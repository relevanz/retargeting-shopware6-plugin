const { Mixin, Application } = Shopware;

Mixin.register('releva-notification', {
    mixins: [
        Mixin.getByName('notification'),
    ],
    methods: {
        handleNotifications(notifications) {
            var applicationRoot = Application.getApplicationRoot();
            var title, message;
            for (var i in notifications) {
                if (typeof applicationRoot.$t("releva-retargeting.messages." + notifications[i].code) === "string") {
                    title = applicationRoot.$t("releva-retargeting.messages.fallback.title", {title: notifications[i].message});
                    message = applicationRoot.$t("releva-retargeting.messages.fallback.message", {code: notifications[i].code, data: JSON.stringify(notifications[i].data)});
                } else {
                    title = applicationRoot.$t("releva-retargeting.messages." + notifications[i].code + ".title", notifications[i].data);
                    message = applicationRoot.$t("releva-retargeting.messages." + notifications[i].code + ".message", notifications[i].data);
                }
                this.createNotification({title: title, message: message, variant: notifications[i].variant});
            }
        }
    }
});
