import './page';
import './component';
import './extension';
import './service';

import {deDE, enGB} from './snippet';

const { Module } = Shopware;

Module.register('releva-retargeting', {
    
    type: 'plugin',
    name: 'relevanz-retargeting',
    title: 'releva-retargeting.general.mainMenuItemGeneral',

    favicon: '../../../../../relevaretargeting/administration/static/img/favicon/modules/icon-module-releva-retargeting.png',
    
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        statistic: {
            component: 'releva-retargeting-statistic',
            path: 'statistic'
        }
    },

    navigation: [{
        label: 'releva-retargeting.general.mainMenuItemGeneral',
        path: 'releva.retargeting.statistic',
        parent: 'sw-marketing'
    }]

});
