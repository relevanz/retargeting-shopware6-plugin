import template from './releva-system-config.html.twig';

const { Component, Defaults, Mixin } = Shopware;
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
    methods: {
        saveReleva () {
            if (this.currentSalesChannelId){
                this.isLoading = true;
                this.retargetingApiService.getVerifyApiKey({
                    apiKey: this.actualConfigData[this.currentSalesChannelId]["RelevaRetargeting.config.relevanzApiKey"],
                    salesChannel: this.currentSalesChannelId
                }).then(
                    (response) => {
                        if (
                            typeof this.actualConfigData[this.currentSalesChannelId]["RelevaRetargeting.config.relevanzUserId"] === 'undefined'
                            || this.actualConfigData[this.currentSalesChannelId]["RelevaRetargeting.config.relevanzUserId"] !== response.data.userId
                        ) {
                            if (this.actualConfigData.hasOwnProperty(this.currentSalesChannelId)) {
                                delete this.actualConfigData[this.currentSalesChannelId];
                            }
                            this.readAll();
                        }
                        this.handleNotifications(response.notifications);
                        this.isLoading = false;
                    }
                );
            }
        }
    }
    
});
