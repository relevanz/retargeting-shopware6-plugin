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
        handleResponseErrors (errors) {
            for (var i in errors) {
                var translatedMessage =
                    typeof this.$t("releva-retargeting.messages." + errors[i].code) === "string"
                    ? {// no message defined use fallback
                        title: this.$t("releva-retargeting.messages.fallback.title", {title: errors[i].message}),
                        message: this.$t("releva-retargeting.messages.fallback.message", {code: errors[i].code, data: JSON.stringify(errors[i].data)})
                    }
                    : {
                        title: this.$t("releva-retargeting.messages." + errors[i].code + ".title", errors[i].data),
                        message: this.$t("releva-retargeting.messages." + errors[i].code + ".message", errors[i].data)
                    }
                ;
                this.createNotificationError({
                    title: translatedMessage.title,
                    message: translatedMessage.message
                });
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
                        this.handleResponseErrors(response.errors);
                        this.isLoading = false;
                    }
                );
            }
        }
    }
    
});
