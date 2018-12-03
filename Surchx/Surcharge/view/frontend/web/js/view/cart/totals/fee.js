/**
* SurchX Surcharge admin configuration
*
* @category    Surchx
* @package     Surchx_Surcharge
*/
define(
    [
        'Surchx_Surcharge/js/view/cart/summary/fee'
    ],
    function (Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Surchx_Surcharge/cart/totals/fee'
            },
            /**
             * @override
             *
             * @returns {boolean}
             */
            isDisplayed: function () {
                return this.getPureValue() != 0;
            }
        });
    }
);
