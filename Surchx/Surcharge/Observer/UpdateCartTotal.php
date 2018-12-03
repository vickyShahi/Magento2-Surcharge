<?php
/**
* SurchX Surcharge @2018 All rights reserved
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
namespace Surchx\Surcharge\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateCartTotal implements ObserverInterface{

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
        $event = $observer->getEvent();
        $quote_item = $this->_checkoutSession->getQuote();
        $this->_checkoutSession->unsTransactionFee();
        $this->_checkoutSession->unsCcin();
    }
}