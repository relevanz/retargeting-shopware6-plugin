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
        },
    },
    watch: {
        config() {
            /**
             * bugfix for translations
             * @link https://issues.shopware.com/issues/NEXT-8104
             */
            for (var card of this.config) {
                for (var element of card.elements) {
                    if (typeof element.config.componentName === "string") {
                        for (var configType of ['label', 'helpText', 'errorMessage']) {
                            if (typeof element.config[configType] === "object") {
                                for (var locale of [State.getters.adminLocaleLanguage + '-' + State.getters.adminLocaleRegion, 'en-GB']) {
                                    if (typeof element.config[configType][locale] !== "undefined") {
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
                }
            }
        }
    },
    methods: {
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
