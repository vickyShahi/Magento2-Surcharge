<?php
/**
* SurchX Surcharge admin configuration
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
namespace Surchx\Surcharge\Plugin;

class UpdateItemQtyPlugin
{

    /**
     * Variable.
     *
     * @var checkoutSession
     */
    public $_checkoutSession;

    /**
     * Construct.
     *
     * @param Data $helper helper.
     */
    public function __construct(
        \Magento\Checkout\Model\Session $_checkoutSession
    ) {
        $this->_checkoutSession = $_checkoutSession;
    }

    /**
     * Order success action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function beforeExecute()
    {
       $this->_checkoutSession->unsTransactionFee(0);
       return $this;
   }
}