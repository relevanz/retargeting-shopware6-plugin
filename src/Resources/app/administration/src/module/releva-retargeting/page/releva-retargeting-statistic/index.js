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
                this.handleResponseNotifications(response.notifications);
                this.salesChannelsToIframeUrl = response.data;
                this.onSalesChannelsToIframeUrlSelectionChange(this.salesChannelsToIframeUrl[0].iframeUrl);
            }
        );
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
        onSalesChannelsToIframeUrlSelectionChange(iframeUrl) {
            this.currentIframeUrl = iframeUrl;
        }
    }
    
});
