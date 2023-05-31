<?php
namespace Prevaj\Solution\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;

class Add extends Action
{
    protected $resultJsonFactory;
    protected $cart;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Cart $cart
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $params = $this->getRequest()->getParams();

        try {
            if (isset($params['custom_field1'])) {
                $customField1 = $params['custom_field1'];
            } else {
                $customField1 = '';
            }

            if (isset($params['custom_field2'])) {
                $customField2 = $params['custom_field2'];
            } else {
                $customField2 = '';
            }

            $product = $this->_objectManager->get('\Magento\Catalog\Model\Product')
                ->load($params['product']);

            if ($product) {
                $this->cart->addProduct($product, [
                    'custom_field1' => $customField1,
                    'custom_field2' => $customField2
                ]);
                $this->cart->save();

                $result->setData(['success' => true]);
            } else {
                $result->setData(['success' => false, 'error' => 'Product not found.']);
            }
        } catch (\Exception $e) {
            $result->setData(['success' => false, 'error' => $e->getMessage()]);
        }

        return $result;
    }
}
