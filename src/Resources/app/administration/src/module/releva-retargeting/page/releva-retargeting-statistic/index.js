import template from './releva-retargeting-statistic.html.twig';

const { Component, Mixin } = Shopware;

Component.register('releva-retargeting-statistic', {
    template,

    inject: [
        'retargetingApiService'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],
    
    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            currentIframeUrl: null,
            salesChannelsToIframeUrl: null
        };
    },
    
    created() {
        this.retargetingApiService.getInvolvedSalesChannelsToIframeUrls().then(
            (response) => {
                this.handleResponseErrors(response.errors);
                this.salesChannelsToIframeUrl = response.data;
                this.onSalesChannelsToIframeUrlSelectionChange(this.salesChannelsToIframeUrl[0].iframeUrl);
            }
        );
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
        onSalesChannelsToIframeUrlSelectionChange(iframeUrl) {
            this.currentIframeUrl = iframeUrl;
        }
    }
    
});
