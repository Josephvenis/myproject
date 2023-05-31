<?php
namespace Prevaj\Solution\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

class CustomFields extends Template
{
    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getFormAction()
    {
        $product = $this->getCurrentProduct();

        return $this->getUrl('module/cart/add', ['product' => $product->getId()]);
    }

    protected function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
}