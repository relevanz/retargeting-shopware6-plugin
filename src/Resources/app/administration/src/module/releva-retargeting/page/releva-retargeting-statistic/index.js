import template from './releva-retargeting-statistic.html.twig';
import './releva-retargeting-statistic.scss';

const { Component, Mixin } = Shopware;

Component.register('releva-retargeting-statistic', {
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

    data() {
        return {
            currentIframeUrl: null,
            salesChannelsToIframeUrl: null
        };
    },
    
    created() {
        this.retargetingApiService.getInvolvedSalesChannelsToIframeUrls().then(
            (response) => {
                this.handleNotifications(response.notifications);
                this.salesChannelsToIframeUrl = response.data;
                this.onSalesChannelsToIframeUrlSelectionChange(this.salesChannelsToIframeUrl[0].iframeUrl);
            }
        ).catch(({ response: { data } }) => {
            this.handleAjaxErrors(data);
        });
    },
    
    methods: {
        onSalesChannelsToIframeUrlSelectionChange(iframeUrl) {
            this.currentIframeUrl = iframeUrl;
        }
    }
    
});
