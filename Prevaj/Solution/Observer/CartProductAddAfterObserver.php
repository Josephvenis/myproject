<?php

namespace Prevaj\Solution\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CartProductAddAfterObserver implements ObserverInterface
{
    protected $request;
    protected $serializer;

    public function __construct(
        RequestInterface $request,
        SerializerInterface $serializer
    ) {
        $this->request = $request;
        $this->serializer = $serializer;
    }

    public function execute(EventObserver $observer)
    {
        $postValue = $this->request->getParams();

        if (isset($postValue['custom_field1']) && $postValue['custom_field1']) {
            $item = $observer->getQuoteItem();
            $customField1Value = $postValue['custom_field1'];
            
            $additionalOptions = $item->getOptionByCode('additional_options');
            if ($additionalOptions) {
                $options = $this->serializer->unserialize($additionalOptions->getValue());
                $options[] = [
                    'label' => 'Name',
                    'value' => $customField1Value,
                ];
                $additionalOptions->setValue($this->serializer->serialize($options));
            } else {
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize([
                        [
                            'label' => 'Name',
                            'value' => $customField1Value,
                        ],
                    ]),
                ]);
            }
        }

        if (isset($postValue['custom_field2']) && $postValue['custom_field2']) {
            $item = $observer->getQuoteItem();
            $customField2Value = $postValue['custom_field2'];
            
            $additionalOptions = $item->getOptionByCode('additional_options');
            if ($additionalOptions) {
                $options = $this->serializer->unserialize($additionalOptions->getValue());
                $options[] = [
                    'label' => 'Company Name',
                    'value' => $customField2Value,
                ];
                $additionalOptions->setValue($this->serializer->serialize($options));
            } else {
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize([
                        [
                            'label' => 'Company Name',
                            'value' => $customField2Value,
                        ],
                    ]),
                ]);
            }
        }
    }
}
