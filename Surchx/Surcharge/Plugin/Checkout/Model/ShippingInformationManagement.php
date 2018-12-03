<?php
/**
* SurchX Surcharge admin configuration
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
namespace Surchx\Surcharge\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

   /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magecomp\Extrafee\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    )
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $quotePostCode = $addressInformation->getShippingAddress()->getPostCode();
        $ccin = $this->_checkoutSession->getCcin();
        $subTotal = $quote->getSubtotal();
        $uniqueId = $quote->getEntityId();
        if(isset($ccin) && $ccin!=0) {
            $requestData = array(
                "campaign"=>["abandonTest"]
                ,"country"=>"840"
                ,"region"=>$quotePostCode
                ,"processor"=>"SurchX"
                ,"nicn"=>$ccin
                ,"amount"=>$subTotal
                ,"mTxId"=>$uniqueId
            );
            
            $transFeeRequest = curl_init("https://svc-qa.surchx.com/v1/ch");
            curl_setopt($transFeeRequest, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($transFeeRequest, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($transFeeRequest, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($transFeeRequest, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NDAyNDI5NjQsImNsaWVudElkIjoiU3VyY2hYIiwiaWQiOiIyMjU4M2FjMS1mMTk2LTQ1MGQtOGQ5NC1hMGE2ZGEzZjJmMmUiLCJtaWQiOiJzdXJjaHgifQ.WFOhMIznSEoRJOWCRglO3gWauGibJiHd3bmtDNWC6gE","x-requested-with: xhr"));
            $responseData = curl_exec($transFeeRequest);
            $fee = 0;
            $respArray = json_decode($responseData);
            foreach($respArray as $k=>$v) {
                if($k=='transactionFee')
                    $fee=$v;
            }
            $this->_checkoutSession->setTransactionFee($fee);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
        }
    }
}

