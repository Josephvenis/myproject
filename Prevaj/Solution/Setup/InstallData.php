<?php

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Api\OptionRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\View\Element\BlockFactory;

require __DIR__ . '/../../../../../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

// Set the area code
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode(Area::AREA_ADMINHTML);

// Retrieve necessary repositories and factories
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
$productAttributeFactory = $objectManager->get(ProductAttributeInterfaceFactory::class);
$optionFactory = $objectManager->get(OptionFactory::class);
$linkManagement = $objectManager->get(LinkManagementInterface::class);
$optionRepository = $objectManager->get(OptionRepositoryInterface::class);
$pageRepository = $objectManager->get(PageRepositoryInterface::class);
$pageFactory = $objectManager->get(PageInterfaceFactory::class);
$storeManager = $objectManager->get(StoreManagerInterface::class);
$eavSetupFactory = $objectManager->get(EavSetupFactory::class);
$blockFactory = $objectManager->get(BlockFactory::class);

// Create an instance of EavSetup
$eavSetup = $eavSetupFactory->create(['setup' => $objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface')]);

// Create configurable product attribute "size"
$eavSetup->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'size',
    [
        'type' => 'int',
        'label' => 'Size',
        'input' => 'select',
        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
        'frontend' => '',
        'backend' => '',
        'required' => true,
        'sort_order' => 50,
        'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
        'option' => [
            'values' => [
                'Small',
                'Large',
            ],
        ],
        'visible' => true,
        'user_defined' => true,
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => true,
        'used_in_product_listing' => true,
        'unique' => false,
        'apply_to' => ''
    ]
);

$productData1 = [
    'sku' => 'prevaj-product-1',
    'name' => 'Prevaj Product 1',
    'price' => 9.99,
    'attribute_set_id' => 4,
    'status' => 1,
    'visibility' => 4,
    'type_id' => 'simple',
    'weight' => 1,
    'category_ids' => [2],
    'website_ids' => [1],
    'extension_attributes' => [
        'stock_item' => [
            'qty' => 100,
            'is_in_stock' => true
        ]
    ],
    'quantity_and_stock_status' => [
        'qty' => 100,
        'is_in_stock' => true
    ]
];

$product1 = $productFactory->create(['data' => $productData1]);
$product1->save();

// Create simple product 2
$productData2 = [
    'sku' => 'prevaj-product-2',
    'name' => 'Prevaj Product 2',
    'price' => 14.99,
    'attribute_set_id' => 4,
    'status' => 1,
    'visibility' => 4,
    'type_id' => 'simple',
    'weight' => 1,
    'category_ids' => [2],
    'website_ids' => [1],
    'extension_attributes' => [
        'stock_item' => [
            'qty' => 200,
            'is_in_stock' => true
        ]
    ],
    'quantity_and_stock_status' => [
        'qty' => 200,
        'is_in_stock' => true
    ]
];

$product2 = $productFactory->create(['data' => $productData2]);
$product2->save();
// Create configurable product
$configurableProductData = [
    'sku' => 'prevaj-config-product',
    'name' => 'Prevaj Config Product',
    'attribute_set_id' => 4,
    'status' => 1,
    'visibility' => 4,
    'type_id' => 'configurable',
    'weight' => 1,
    'website_ids' => [1],
    'category_ids' => [2],
    'extension_attributes' => [
        'stock_item' => [
            'qty' => 100,
            'is_in_stock' => true
        ]
    ],
    'quantity_and_stock_status' => [
        'is_in_stock' => true
    ],
    'custom_attributes' => [
        [
            'attribute_code' => 'size',
            'value' => 'large'
        ]
    ]
];

$configurableProduct = $productFactory->create(['data' => $configurableProductData]);
$configurableProduct->save();

$configurableProduct->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
    ->setAffectConfigurableProductAttributes(4)
    ->setUsedProductAttributeIds([$configurableProduct->getResource()->getAttribute('size')->getId()])
    ->setNewVariationsAttributeSetId(4)
    ->setAssociatedProductIds([$product1->getId(), $product2->getId()])
    ->setCanSaveConfigurableAttributes(true)
    ->save();

// Create CMS page
$cmsPageData = [
    'title' => 'Prevaj Custom Page',
    'page_layout' => '1column',
    'meta_keywords' => 'prevaj, custom, page',
    'meta_description' => 'This is my New page.',
    'identifier' => 'prevaj-custom-page',
    'content_heading' => 'Prevaj Solution',
    'content' => '{{block class="Prevaj\Solution\Block\Products" template="Prevaj_Solution::product_list.phtml"}}',
    'is_active' => 1,
    'stores' => [0],
];

$pageIdentifier = $cmsPageData['identifier'];
$cmsPage = $pageFactory->create()->load($pageIdentifier, 'identifier');
if (!$cmsPage->getId()) {
    $cmsPage = $pageFactory->create();
    $cmsPage->setData($cmsPageData);
    $pageRepository->save($cmsPage);
}

// Display the CMS page
$storeId = $storeManager->getStore()->getId();
$pageUrl = $storeManager->getStore()->getBaseUrl() . $pageIdentifier . '.html';
$cmsPage->setStoreId($storeId);
$cmsPage->setIdentifier($pageIdentifier);
$cmsPage->setIsActive(true);
$pageRepository->save($cmsPage);

echo "CMS page created and products added successfully. You can view the page <a href='$pageUrl'>$pageIdentifier</a>.";