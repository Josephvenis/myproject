<?php

namespace Prevaj\Solution\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class QuoteSubmitBeforeObserver implements ObserverInterface
{
    private $quoteItems = [];
    private $quote = null;
    private $order = null;
    private $serializer;
    
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }
    
    public function execute(EventObserver $observer)
    {
        $this->quote = $observer->getQuote();
        $this->order = $observer->getOrder();
        
        foreach ($this->order->getItems() as $orderItem) {
            if ($quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId())) {
                $additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options');
                $additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options');
                
                if ($additionalOptionsQuote && $additionalOptionsOrder) {
                    $additionalOptions = array_merge(
                        $this->serializer->unserialize($additionalOptionsQuote->getValue()),
                        $additionalOptionsOrder
                    );
                } elseif ($additionalOptionsQuote) {
                    $additionalOptions = $this->serializer->unserialize($additionalOptionsQuote->getValue());
                } else {
                    $additionalOptions = $additionalOptionsOrder;
                }
                
                if ($additionalOptions && count($additionalOptions) > 0) {
                    $options = $orderItem->getProductOptions();
                    $options['additional_options'] = $additionalOptions;
                    $orderItem->setProductOptions($options);
                }
            }
        }
    }
    
    private function getQuoteItemById($id)
    {
        if (empty($this->quoteItems)) {
            if ($this->quote->getItems()) {
                foreach ($this->quote->getItems() as $item) {
                    $this->quoteItems[$item->getId()] = $item;
                }
            }
        }
        
        if (array_key_exists($id, $this->quoteItems)) {
            return $this->quoteItems[$id];
        }

        return null;
    }
}
