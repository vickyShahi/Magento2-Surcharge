/**
* SurchX Surcharge admin configuration
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'surchx_stripe',
                component: 'Surchx_Surcharge/js/view/payment/method-renderer/stripe-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);