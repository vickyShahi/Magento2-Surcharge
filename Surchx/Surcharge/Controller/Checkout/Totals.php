<?php
/**
* SurchX Surcharge @2018 All rights reserved
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
namespace Surchx\Surcharge\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;

class Totals extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJson;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_helper;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $loggerInterface
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_resultJson = $resultJson;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $loggerInterface;
    }

    /**
     * Trigger to re-calculate the collect Totals
     *
     * @return bool
     */
    public function execute()
    {
        $response = [
            'errors' => false,
            'message' => 'Re-calculate successful.'
        ];
        try {
            $this->quoteRepository->get($this->_checkoutSession->getQuoteId());
            $quote = $this->_checkoutSession->getQuote();

            //Trigger to re-calculate totals
            $data = $this->_helper->jsonDecode($this->getRequest()->getContent());
            
            $this->_checkoutSession->getQuote()->getPayment()->setMethod('surchx_stripe');
            
            $billingAddress = $this->_checkoutSession->getQuote()->getBillingAddress()->getData();
            $addressRegion = $billingAddress['postcode'];
            $quoteData = $this->_checkoutSession->getQuote()->getData();
            $uniqueId = $quoteData['entity_id'];
            $subTotal = $quoteData['subtotal'];
            $ccin = mb_substr($data['ccin'], 0, 6);
            $this->_checkoutSession->setCcin($ccin);
            $requestData = array(
                "campaign"=>["abandonTest"]
                ,"country"=>"840"
                ,"region"=>$addressRegion
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


        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultJson = $this->_resultJson->create();
        return $resultJson->setData($response);
    }
}
