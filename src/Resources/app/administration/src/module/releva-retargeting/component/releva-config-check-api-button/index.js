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
            }(this)
        };
    },
    
    computed: {
        buttonIcon() {
            switch (this.checkApiState) {
                case "success": {
                    return {name: "default-basic-checkmark-circle", color: "green"};
                }
                case "error": {
                    return {name: "default-badge-error", color: "red"};
                }
                case "checking": {
                    return {name: "default-web-loading-circle", color: "gray"};
                }
                default: {
                    return {name: "default-action-cloud-upload", color: "silver"};
                }
            }
        }
    },
    
    methods: {
        changeMode(disabled) {
            if (disabled) {
                return;
            }
            this.checkApiState = "checking";
            var self = this;
            this.retargetingApiService.getVerifyApiKey({
                apiKey: this.currentValue.trim(),
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
