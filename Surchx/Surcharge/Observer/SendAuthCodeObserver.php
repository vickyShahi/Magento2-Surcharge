<?php
/**
* SurchX Surcharge @2018 All rights reserved
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
namespace Surchx\Surcharge\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class SendAuthCodeObserver implements ObserverInterface
{
    protected $_order;
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
         $this->_order = $order;    
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
      $orderids = $observer->getEvent()->getOrderIds();

      foreach($orderids as $orderid){
          $order = $this->_order->load($orderid);
          $quoteId = $order->getQuoteId();
      }

      $rand = rand(1, 1000000);
      $authCode = $quoteId.$rand;

      $authCodeRequestArray = array(
        "mTxId"=>$quoteId,
        "authCode"=>$authCode
      );

      $authCodeRequest = curl_init("https://svc-qa.surchx.com/v1/ch/capture");

      curl_setopt($authCodeRequest, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($authCodeRequest, CURLOPT_POSTFIELDS, json_encode($authCodeRequestArray));
      curl_setopt($authCodeRequest, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($authCodeRequest, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NDAyNDI5NjQsImNsaWVudElkIjoiU3VyY2hYIiwiaWQiOiIyMjU4M2FjMS1mMTk2LTQ1MGQtOGQ5NC1hMGE2ZGEzZjJmMmUiLCJtaWQiOiJzdXJjaHgifQ.WFOhMIznSEoRJOWCRglO3gWauGibJiHd3bmtDNWC6gE","x-requested-with: xhr"));

      $responseData = curl_exec($authCodeRequest);

      return true;
        
    }

}