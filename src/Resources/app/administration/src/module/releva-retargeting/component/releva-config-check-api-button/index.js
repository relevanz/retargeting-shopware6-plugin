import template from './releva-config-check-api-button.html.twig';
import './releva-config-check-api-button.scss';

const { Component, Mixin } = Shopware;

Component.register('releva-config-check-api-button', {
    template,

    inheritAttrs: false,

    mixins: [
        Mixin.getByName('releva-notification'),
    ],

    inject: [
        'retargetingApiService'
    ],

    props: {
        modelValue: {
            type: String,
            required: false,
            default: null,
        },
        value: {
            type: String,
            required: false,
            default: null,
        },
        deprecated: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            salesChannel: function(self) {
                var current = self;
                while (typeof current.$parent !== "undefined") {
                    current = current.$parent;
                    if (typeof current.currentSalesChannelId !== "undefined") {
                        return current.currentSalesChannelId;
                    }
                }
                return '';
            }(this),
            scopeMessage: this.$attrs.scopeMessage,
        };
    },

    computed: {
        compatValue: {
            get() {
                if (this.value === null || this.value === undefined) {
                    return this.modelValue;
                }
                return this.value;
            },
            set(value) {
                this.$emit('update:value', value);
                this.$emit('update:modelValue', value);
            },
        },
    },

    methods: {
        handleUpdateModelValue(value) {
            this.$emit('update:modelValue', value);
            this.$emit('update:value', value);
        },
    },
});
