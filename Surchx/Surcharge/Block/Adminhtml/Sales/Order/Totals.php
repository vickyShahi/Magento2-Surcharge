<?php
/**
* SurchX Surcharge @2018 All rights reserved
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
namespace Surchx\Surcharge\Block\Adminhtml\Sales\Order;

class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();

        if(!$this->getSource()->getFeeAmount()) {
            return $this;
        }
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'fee',
                'value' => $this->getSource()->getFeeAmount(),
                'label' => __('Transaction Fee'),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}