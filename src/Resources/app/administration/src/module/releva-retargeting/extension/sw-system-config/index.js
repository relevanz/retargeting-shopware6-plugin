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

    watch: {
        currentSalesChannelId() {
            this.readConfig();
        },
        config() {
            if (this.domain === "RelevaRetargeting.config") {
                for (var card of this.config) {
                    for (var element of card.elements) {
                        if (!this.currentSalesChannelId && element.config.hasOwnProperty("scope") && element.config.scope !== "global") {
                             card.elements = card.elements.filter(function(deleteElement) {
                                 return element !== deleteElement;
                             })
                        } else {
                            element.config.disabled =
                                element.config.hasOwnProperty("disabled")
                                ? element.config.disabled
                                : false
                            ;
                        }
                        /**
                         * bugfix for translations
                         * @link https://issues.shopware.com/issues/NEXT-8104
                         */
//                        if (typeof element.config.componentName === "string") {
//                            for (var configType of ['label', 'helpText', 'errorMessage']) {
//                                if (typeof element.config[configType] === "object") {
//                                    for (var locale of [State.getters.adminLocaleLanguage + '-' + State.getters.adminLocaleRegion, 'en-GB']) {
//                                        if (typeof element.config[configType][locale] !== "undefined") {
//                                            element.config[configType] = element.config[configType][locale];
//                                            break;
//                                        }
//                                    }
//                                    if (typeof element.config[configType] === "object") {
//                                        element.config[configType] = element.config[configType][Object.keys(element.config[configType])[0]];//use first translation
//                                    }
//                                }
//                            }
//                        }
                    }
                    if (card.elements.length === 0) {
                        this.config = this.config.filter(function(deleteCard) {
                            return card !== deleteCard;
                        })
                    }
                }
            }
        }
    },
    methods: {
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
        },
        isDefaultSalesChannelOrGlobalScope(element) {
            if (
                this.domain === "RelevaRetargeting.config" 
                && (
                    element.config.hasOwnProperty("scopeMessage")
                    || (element.config.hasOwnProperty("scope") && element.config.scope !== "global")
                )
             ) {
                return false;
            }
            return true;
        }
    }
});