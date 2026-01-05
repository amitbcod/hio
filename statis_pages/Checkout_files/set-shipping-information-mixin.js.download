/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Webkul_GoogleMapPinAddress/js/model/map-config-provider'
], function ($, wrapper, quote, mapData) {
    'use strict';
    var mapDataValue = mapData.getMapData();
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {
            if (mapDataValue['status']== '0') {
                return originalAction(messageContainer)
            }
            var shippingAddress = quote.shippingAddress();

            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            if (shippingAddress.customAttributes != undefined) {
                var attrKey = '';

                $.each(shippingAddress.customAttributes , function( key, value ) {

                    if($.isPlainObject(value)){
                        attrKey = value['attribute_code'];
                        if (value['value']['attribute_code'] != undefined )
                        {
                            value = value['value']['value'];    
                        } else {
                            value = value['value'];
                        }
                    }

                    if (attrKey == '') {
                        shippingAddress['customAttributes'][key] = value;
                        shippingAddress['extension_attributes'][key] = value;
                    } else {
                        shippingAddress['customAttributes'][attrKey] = value;
                        shippingAddress['extension_attributes'][attrKey] = value;
                    }
                });
            }

            return originalAction(messageContainer);
        });
    };
});