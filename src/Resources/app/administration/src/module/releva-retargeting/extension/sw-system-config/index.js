import template from './sw-system-config.html.twig';

const { Component, Defaults, Mixin, State } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-system-config', {
    template,
    inject: [
        'retargetingApiService'
    ],
    mixins: [
        Mixin.getByName('releva-notification')
    ],
    methods: {
        getScopeMessage(element) {
            var snippet = {};
            var hasScopeMessage = false;
            if (element.config.hasOwnProperty("scopeMessage")) {
                hasScopeMessage = true;
                snippet['en-EN'] = element.config.scopeMessage;
            }
            if (element.config.hasOwnProperty("scopeMessageDE")) {
                hasScopeMessage = true;
                snippet['de-DE'] = element.config.scopeMessageDE;
            }
            return hasScopeMessage ? this.getInlineSnippet(snippet) : null;
        },
        saveAll() {
            this.isLoading = true;
            if (this.domain === "RelevaRetargeting.config" && this.currentSalesChannelId) {
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
                        return this.$super("saveAll");
                    }
                ).catch(({ response: { data } }) => {
                    this.handleAjaxErrors(data);
                    return this.$super("saveAll");
                }).finally(() => this.isLoading = false);
            } else {
                return this.$super("saveAll");
            }
        }
    }
});
