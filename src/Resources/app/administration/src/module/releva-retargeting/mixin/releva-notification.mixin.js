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
                var notificationKey = "releva-retargeting.messages." + notifications[i].code;
                if (applicationRoot.$t(notificationKey + ".title") === notificationKey + ".title") {
                    title = applicationRoot.$t("releva-retargeting.messages.fallback.title", {title: notifications[i].message});
                    message = applicationRoot.$t("releva-retargeting.messages.fallback.message", {code: notifications[i].code, data: JSON.stringify(notifications[i].data)});
                } else {
                    title = applicationRoot.$t(notificationKey + ".title", notifications[i].data);
                    message = applicationRoot.$t(notificationKey + ".message", notifications[i].data);
                }
                this.createNotification({title: title, message: message, variant: notifications[i].variant});
            }
        },
        handleAjaxErrors (data) {
            if (data && data.errors) {
                var applicationRoot = Application.getApplicationRoot();
                data.errors.forEach((error) => {
                    this.createNotification({title: applicationRoot.$t("releva-retargeting.messages.ajax.title"), message: applicationRoot.$t("releva-retargeting.messages.ajax.message", error), variant: "error"});
                });
            }
        }
    }
});
