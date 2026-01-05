/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GoogleMapPinAddress
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Webkul_GoogleMapPinAddress/js/model/map-config-provider'
], function ($, wrapper,quote, mapData) {
    'use strict';
    var mapDataValue = mapData.getMapData();
    return function (setBillingAddressAction) {
        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {
            if (mapDataValue['status']== '0') {
                return originalAction(messageContainer)
            }
            var billingAddress = quote.billingAddress();

            if(billingAddress != undefined) {

                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }

                if (billingAddress.customAttributes != undefined) {
                    var attrKey = '';
                    $.each(billingAddress.customAttributes, function (key, value) {

                        if($.isPlainObject(value)){
                            if (value['attribute_code'] != undefined){
                                attrKey =  value['attribute_code'];
                            }
                            if (value['value']['attribute_code'] != undefined )
                        {
                            value = value['value']['value'];    
                        } else {
                            value = value['value'];
                        }
                    }
                        if (attrKey == ""){
                            billingAddress['extension_attributes'][key] = value;    
                        } else {
                        billingAddress['extension_attributes'][attrKey] = value;
                        }
                    });
                }

            }

            return originalAction(messageContainer);
        });
    };
});