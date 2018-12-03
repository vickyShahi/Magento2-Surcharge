/**
* SurchX Surcharge admin configuration
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
define(
    [
        'Surchx_Surcharge/js/view/checkout/summary/fee'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return true;
            }
        });
    }
);