/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

define([
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'mage/translate'
], function (Component, ko, quote, customer, storage, urlBuilder, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Geissweb_ElectronicInvoicingAttributes/checkout/einvoicing-fields',
            isBuyerReferenceEnabled: true,
            buyerReferenceTooltip: '',
            isProjectReferenceEnabled: true,
            projectReferenceTooltip: ''
        },

        buyerReference: ko.observable(''),
        projectReference: ko.observable(''),
        isVisible: ko.observable(true),
        isBuyerReferenceVisible: ko.observable(true),
        isProjectReferenceVisible: ko.observable(true),

        /**
         * @returns {Object}
         */
        initialize: function () {
            this._super();

            this.isBuyerReferenceVisible(this.isBuyerReferenceEnabled);
            this.isProjectReferenceVisible(this.isProjectReferenceEnabled);
            this.isVisible(this.isBuyerReferenceEnabled || this.isProjectReferenceEnabled);

            if (!this.isBuyerReferenceEnabled && !this.isProjectReferenceEnabled) {
                return this;
            }

            this.loadInitialData();
            this.subscribeToChanges();

            return this;
        },

        /**
         * Load initial data from quote or customer
         */
        loadInitialData: function () {
            var self = this;
            var quoteId = quote.getQuoteId();

            if (!quoteId) {
                return;
            }

            var serviceUrl = urlBuilder.createUrl('/carts/mine/einvoicing', {});

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/einvoicing', {
                    cartId: quoteId
                });
            }

            storage.get(serviceUrl).done(function (response) {
                if (response) {
                    if (response.buyer_reference) {
                        self.buyerReference(response.buyer_reference);
                    }
                    if (response.project_reference) {
                        self.projectReference(response.project_reference);
                    }
                }
            }).fail(function () {
                // Silently fail - data will be empty
            });
        },

        /**
         * Subscribe to field changes and save automatically
         */
        subscribeToChanges: function () {
            var self = this;
            var saveTimeout;

            var saveData = function () {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(function () {
                    self.saveData();
                }, 500);
            };

            this.buyerReference.subscribe(saveData);
            this.projectReference.subscribe(saveData);
        },

        /**
         * Save data to quote
         */
        saveData: function () {
            var self = this;
            var quoteId = quote.getQuoteId();

            if (!quoteId) {
                return;
            }

            var serviceUrl = urlBuilder.createUrl('/carts/mine/einvoicing', {});

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/einvoicing', {
                    cartId: quoteId
                });
            }

            var payload = {
                buyerReference: self.buyerReference(),
                projectReference: self.projectReference()
            };

            storage.post(serviceUrl, JSON.stringify(payload)).fail(function () {
                // Silently fail - data will be saved on order placement
            });
        },

        /**
         * @returns {String}
         */
        getTitle: function () {
            return $t('E-Invoicing Information');
        }
    });
});
