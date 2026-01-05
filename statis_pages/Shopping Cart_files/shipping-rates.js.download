/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
define(
    [
        'ko',
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/checkout-data'
    ],
    function (
        ko,
        _,
        Component,
        shippingService,
        priceUtils,
        quote,
        selectShippingMethodAction,
        checkoutData
    ) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    template: 'Ced_CsMultiShipping/cart/shipping-rates'
                },
                isVisible: ko.observable(!quote.isVirtual()),
                isLoading: shippingService.isLoading,
                shippingRates: shippingService.getShippingRates(),
                shippingRateGroups: ko.observableArray([]),
                selectedShippingMethod: ko.computed(
                    function () {
                        var seletecdmth = quote.shippingMethod() ? quote.shippingMethod().method_code : null;
                        if(seletecdmth) {
                            var METHOD_SEPARATOR = ':';
                            var methods = seletecdmth.split(METHOD_SEPARATOR);
                            for(var i = 0; i < methods.length; i ++){
                                if(document.getElementById(methods[i]) && document.getElementById('vendor_rates_'+seletecdmth)) {
                                    document.getElementById(methods[i]).checked = true;
                                }
                            }
                        }
                        var inputs = document.getElementsByClassName('radio');
                        for(var i = 0; i < inputs.length; i++) {
                            inputs[i].disabled = false;
                        }
                        return quote.shippingMethod() ? quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] : null;
                    }
                ),

             /**
             * @override
             */
                initObservable: function () {
                    var self = this;
                    this._super();

                    this.shippingRates.subscribe(
                        function (rates) {
                            self.shippingRateGroups([]);
                            _.each(
                                rates, function (rate) {
                                    var carrierTitle = rate['carrier_title'];
                                    var carrier_code = rate['carrier_code'];

                                    if (self.shippingRateGroups.indexOf(carrierTitle) === -1
                                        && carrier_code != 'vendor_rates') {
                                        self.shippingRateGroups.push(carrierTitle);
                                    }
                                }
                            );
                        }
                    );
                    return this;
                },

             /**
             * Get shipping rates for specific group based on title.
             *
             * @returns Array
             */
                getRatesForGroup: function (shippingRateGroupTitle) {
                    return _.filter(
                        this.shippingRates(), function (rate) {
                            return shippingRateGroupTitle === rate['carrier_title'];
                        }
                    );
                },

             /**
             * Format shipping price.
             *
             * @returns {String}
             */
                getFormattedPrice: function (price) {
                    return priceUtils.formatPrice(price, quote.getPriceFormat());
                },

             /**
             * Set shipping method.
             *
             * @param   {String} methodData
             * @returns bool
             */
                selectShippingMethod: function (methodData) {
                    selectShippingMethodAction(methodData);
                    checkoutData.setSelectedShippingRate(methodData['carrier_code'] + '_' + methodData['method_code']);
                    return true;
                },

                selectVirtualMethod: function(methodData){
                    var flagg = true;
                    var METHOD_SEPARATOR = ':';
                    var SEPARATOR = '~';
                    var rates = new Array();
                    var sortedrate = new Array();
                    jQuery('.vendor-rates').each(
                        function(indx,elm){
                            var flag = false;
                            jQuery(elm).find('.radio').each(
                                function(i,inpt){
                                    if(inpt.checked) {
                                        flag = true;
                                        rates.push(inpt.value);
                                    }
                                }
                            );
                            if(!flag) {
                                flagg = false;
                            }
                        }
                    );
                    if(flagg) {
                        for(var i = 0; i < rates.length; i ++){
                            var sortedValue = rates[i].split(SEPARATOR);
                            var pos = isNaN(parseInt(sortedValue[1])) ? 0 : parseInt(sortedValue[1]);
                            sortedrate[pos] = rates[i];
                        }
                        var rate = '';
                        for(var i=0;i< sortedrate.length;i++){
                            if(sortedrate[i]!=undefined) {
                                if(rate) {
                                    rate = rate + METHOD_SEPARATOR + sortedrate[i];
                                }else{
                                    rate =  sortedrate[i];
                                }
                            }
                        }
                        rate = rate.replace('~', '-');
                        if(document.getElementById('vendor_rates_'+rate)) {
                            var event = new Event('click');
                            document.getElementById('vendor_rates_'+rate).dispatchEvent(event);
                        }
                    }
                    return true;
                }
            }
        );
    }
);
