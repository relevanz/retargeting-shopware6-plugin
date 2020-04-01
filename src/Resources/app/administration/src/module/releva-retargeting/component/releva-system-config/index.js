import template from './releva-system-config.html.twig';

const { Component, Defaults } = Shopware;
const { Criteria } = Shopware.Data;

Component.extend('releva-system-config', 'sw-system-config', {
    template,
    inject: [
        'retargetingApiService'
    ],

    computed: {
        storefrontSalesChannelCriteria: function() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('typeId', Defaults.storefrontSalesChannelTypeId));
            return criteria;
        }
    },
    methods: {
        handleResponseNotifications (notifications) {
            for (var i in notifications) {
                if (typeof this.$t("releva-retargeting.messages." + notifications[i].code) === "string") {
                    notifications[i].title = this.$t("releva-retargeting.messages.fallback.title", {title: notifications[i].message});
                    notifications[i].message = this.$t("releva-retargeting.messages.fallback.message", {code: notifications[i].code, data: JSON.stringify(notifications[i].data)});
                } else {
                    notifications[i].title = this.$t("releva-retargeting.messages." + notifications[i].code + ".title", notifications[i].data);
                    notifications[i].message = this.$t("releva-retargeting.messages." + notifications[i].code + ".message", notifications[i].data);
                }
                this.createNotification(notifications[i]);
            }
        },
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
                        this.handleResponseNotifications(response.notifications);
                        this.isLoading = false;
                    }
                );
            }
        }
    }
    
});
