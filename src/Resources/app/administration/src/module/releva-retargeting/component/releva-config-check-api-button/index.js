import template from './releva-config-check-api-button.html.twig';
import './releva-config-check-api-button.scss';

const { Component, Mixin } = Shopware;

Component.extend('releva-config-check-api-button', 'sw-text-field', {
    template,
    
    inheritAttrs: false,
    
    mixins: [
        Mixin.getByName('releva-notification'),
    ],
    
    inject: [
        'retargetingApiService'
    ],
    
    data() {
        return {
            checkApiState: "unchecked",
            buttonText: function (self) {
                var title = "releva-retargeting" + self.$attrs.name.substr(self.$attrs.name.indexOf('.')) + ".button";
                var translated = self.$tc(title);
                return title === translated ? false : translated;
            }(this),
            salesChannel: function(self) {
                var current = self;
                while (typeof current.$parent !== "undefined") {
                    current = current.$parent;
                    if (typeof current.currentSalesChannelId !=="undefined") {
                        return current.currentSalesChannelId;
                    }
                }
                return '';
            }(this),
            scopeMessage: this.$attrs.scopeMessage,
        };
    },
    computed: {
        buttonIcon() {
            if (typeof this.compatValue === "undefined") {
                return;
            }
            // icons are in /vendor/shopware/administration/Resources/app/administration/node_modules/@shopware-ag/meteor-icon-kit/icons/regular/
            switch (this.checkApiState) {
                case "success": {
                    return {name: "regular-check-circle", color: "green"};
                }
                case "error": {
                    return {name: "regular-exclamation-circle", color: "red"};
                }
                case "checking": {
                    return {name: "regular-sync", color: "gray"};
                }
                case "unchecked": {
                    return {name: "regular-cloud-upload", color: "silver"};
                }
                default: {
                    return {name: "regular-ellipsis-h-s", color: "silver"};
                }
            }
        },
        checkApiState() {
            this.disabled() ? "" : "unchecked";
        },
        disabled() {
            return typeof this.compatValue !== "undefined" && this.compatValue.trim() !== ""
        }
    },
    methods: {
        changeMode(disabled) {
            if (disabled || (typeof this.compatValue !== "undefined" ? this.compatValue.trim() : "") === "") {
                return;
            }
            this.checkApiState = "checking";
            var self = this;
            this.retargetingApiService.getVerifyApiKey({
                apiKey: typeof this.compatValue !== "undefined" ? this.compatValue.trim() : "",
                salesChannel: function(current) {
                    while (typeof current.$parent !== "undefined") {
                        current = current.$parent;
                        if (typeof current.currentSalesChannelId !=="undefined") {
                            return current.currentSalesChannelId;
                        }
                    }
                    return '';
                }(this)
            }).then(
                (response) => {
                    self.checkApiState = response.data.userId === null ? "error" : "success";
                    this.handleNotifications(response.notifications);
                }
            ).catch(({ response: { data } }) => {
                self.checkApiState = "unchecked";
                self.handleAjaxErrors(data);
            });
        }
    }
});
