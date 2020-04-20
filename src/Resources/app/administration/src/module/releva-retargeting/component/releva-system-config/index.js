import template from './releva-system-config.html.twig';

const { Component, Defaults, Mixin, State } = Shopware;
const { Criteria } = Shopware.Data;

Component.extend('releva-system-config', 'sw-system-config', {
    template,
    
    inject: [
        'retargetingApiService'
    ],
    
    mixins: [
        Mixin.getByName('releva-notification')
    ],
    
    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },
    
    computed: {
        storefrontSalesChannelCriteria: function() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('typeId', Defaults.storefrontSalesChannelTypeId));
            return criteria;
        }
    },
    watch: {
        config() {
            /**
             * bugfix for translations
             * @link https://issues.shopware.com/issues/NEXT-8104
             */
            this.walkConfigElements(function (element) {
                if (typeof element.config.componentName === "string") {
                    for (var configType of ['label', 'helpText']) {
                        if (typeof element.config[configType] === "object") {
                            for (var locale of [State.getters.adminLocaleLanguage + '-' + State.getters.adminLocaleRegion, 'en-GB']) {
                                if (element.config[configType][locale] !== undefined) {
                                    element.config[configType] = element.config[configType][locale];
                                    break;
                                }
                            }
                            if (typeof element.config[configType] === "object") {
                                element.config[configType] = element.config[configType][Object.keys(element.config[configType])[0]];//use first translation
                            }
                        }
                    }
                }
            });
        },
        currentSalesChannelId: {
            immediate: true,
            handler () {
                this.walkConfigElements(null, function (card, self) {
                    card.visible = false;
                    for (var element of card.elements) {
                        element.visible = true;
                        element.message = false;
                        if (element.name === "RelevaRetargeting.config.relevanzApiKey") {
                            // add currentSalesChannelId for verify-api-request
                            element.salesChannelId = self.currentSalesChannelId;
                        }
                        if (//only stores
                            ["RelevaRetargeting.config.relevanzApiKey", "RelevaRetargeting.config.relevanzUserId"].indexOf(element.name) !== -1
                            && self.currentSalesChannelId === null
                        ) {
                            element.visible = false;
                            var message = "releva-retargeting" + element.name.substr(element.name.indexOf('.')) + ".message";
                            var translatedMessage = self.$tc(message);
                            element.message = message === translatedMessage ? false : translatedMessage;
                        }
                        card.visible = card.visible || element.visible || element.message !== undefined;
                    }
                });
            }
        }
    },
    methods: {
        walkConfigElements (elementFunction = null, cardFunction = null, immediate = true) {
            if (typeof this.config[Symbol.iterator] !== 'function') { // config is not loaded, no iterator
                if (immediate) {
                    var self = this;
                    var interval = setInterval(function () {
                        if (self.walkConfigElements(elementFunction, cardFunction, false)) {
                            clearInterval(interval);
                        }
                    }, 300);
                }
                return false;
            }
            for (var card of this.config) {
                if (elementFunction !== null) {
                    for (var element of card.elements) {
                        elementFunction(element, this);
                    }
                }
                if (cardFunction !== null) {
                    cardFunction(card, this);
                }
            }
            return true;
        },
        saveAll() {
            this.isLoading = true;
            if (this.currentSalesChannelId){
                return this.retargetingApiService.getVerifyApiKey({
                    apiKey: this.actualConfigData[this.currentSalesChannelId]["RelevaRetargeting.config.relevanzApiKey"],
                    salesChannel: this.currentSalesChannelId,
                    save: true// could be false
                }).then(
                    (response) => {
                        if (this.actualConfigData.hasOwnProperty(this.currentSalesChannelId)) {
                            this.actualConfigData[this.currentSalesChannelId]["RelevaRetargeting.config.relevanzUserId"] = response.data.userId;
                        }
                        this.handleNotifications(response.notifications);
                        return this.systemConfigApiService.batchSave(this.actualConfigData);
                    }
                ).catch(({ response: { data } }) => {
                    this.handleAjaxErrors(data);
                    return this.systemConfigApiService.batchSave(this.actualConfigData);
                }).finally(() => this.isLoading = false);
            } else {
                return this.systemConfigApiService.batchSave(this.actualConfigData).finally(() => this.isLoading = false);
            }
        }
    }
    
});
