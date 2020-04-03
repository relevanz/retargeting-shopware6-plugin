import template from './sw-plugin-config.html.twig';

const { Component } = Shopware;

Component.override('sw-plugin-config', {
    template,
    methods: {
        onRelevaSave() {
            this.onSave();
            this.$refs.systemConfig.saveReleva();
        }
    }
});
