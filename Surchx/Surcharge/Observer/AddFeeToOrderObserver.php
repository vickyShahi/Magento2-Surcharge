<?php
/**
* SurchX Surcharge @2018 All rights reserved
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
namespace Surchx\Surcharge\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * AddFeeToOrderObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $feeAmount = 0;
        $transFee = $this->_checkoutSession->getTransactionFee();
        if(isset($transFee)) {
            $feeAmount = $transFee;
        }
        $this->_checkoutSession->unsTransactionFee();
        $this->_checkoutSession->unsCcin();
        //Set fee data to order
        $order = $observer->getOrder();
        $order->setData('fee_amount', $feeAmount);
        $order->setData('base_fee_amount', $feeAmount);
        return $this;
    }
}