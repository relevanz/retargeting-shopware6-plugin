(this.webpackJsonp=this.webpackJsonp||[]).push([["releva-retargeting"],{CkOj:function(e,t,n){"use strict";n.d(t,"a",(function(){return c}));var a=n("lSNA"),r=n.n(a),i=n("lO2t"),s=n("lYO9");function l(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function o(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?l(n,!0).forEach((function(t){r()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):l(n).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function c(e){var t=function(e){var t;if(i.a.isString(e))try{t=JSON.parse(e)}catch(e){return!1}else{if(!i.a.isObject(e)||i.a.isArray(e))return!1;t=e}return t}(e);if(!t)return null;if(!0===t.parsed||!function(e){return void 0!==e.data||void 0!==e.errors||void 0!==e.links||void 0!==e.meta}(t))return t;var n=function(e){var t={links:null,errors:null,data:null,associations:null,aggregations:null};if(e.errors)return t.errors=e.errors,t;var n=function(e){var t=new Map;if(!e||!e.length)return t;return e.forEach((function(e){var n="".concat(e.type,"-").concat(e.id);t.set(n,e)})),t}(e.included);if(i.a.isArray(e.data))t.data=e.data.map((function(e){var a=g(e,n);return Object(s.f)(a,"associationLinks")&&(t.associations=o({},t.associations,{},a.associationLinks),delete a.associationLinks),a}));else if(i.a.isObject(e.data)){var a=g(e.data,n);Object.prototype.hasOwnProperty.call(a,"associationLinks")&&(t.associations=o({},t.associations,{},a.associationLinks),delete a.associationLinks),t.data=a}else t.data=null;e.meta&&Object.keys(e.meta).length&&(t.meta=u(e.meta));e.links&&Object.keys(e.links).length&&(t.links=e.links);e.aggregations&&Object.keys(e.aggregations).length&&(t.aggregations=e.aggregations);return t}(t);return n.parsed=!0,n}function g(e,t){var n={id:e.id,type:e.type,links:e.links||{},meta:e.meta||{}};e.attributes&&Object.keys(e.attributes).length>0&&(n=o({},n,{},u(e.attributes)));if(e.relationships){var a=function(e,t){var n={},a={};return Object.keys(e).forEach((function(r){var s=e[r];if(s.links&&Object.keys(s.links).length&&(a[r]=s.links.related),s.data){var l=s.data;i.a.isArray(l)?n[r]=l.map((function(e){return h(e,t)})):i.a.isObject(l)?n[r]=h(l,t):n[r]=null}})),{mappedRelations:n,associationLinks:a}}(e.relationships,t);n=o({},n,{},a.mappedRelations,{},{associationLinks:a.associationLinks})}return n}function u(e){var t={};return Object.keys(e).forEach((function(n){var a=e[n],r=n.replace(/-([a-z])/g,(function(e,t){return t.toUpperCase()}));t[r]=a})),t}function h(e,t){var n="".concat(e.type,"-").concat(e.id);return t.has(n)?g(t.get(n),t):e}},O1Uz:function(e){e.exports=JSON.parse('{"releva-retargeting":{"general":{"mainMenuItemGeneral":"releva.nz retargeting","storefront":"Storefront-Verkaufskanal","fallback_config_relevanzapikey":"Api Key ist nur mit ausgewählten @:(releva-retargeting.general.storefront) konfigurierbar."},"messages":{"1553935480":{"title":"Kann keine Verbindung zum releva.nz API-Server herstellen.","message":"salesChannel: {salesChannelName}, data: {data}"},"1553935569":{"title":"@:(releva-retargeting.messages.1553935786.title)","message":"@:(releva-retargeting.messages.1553935786.message)"},"1553935786":{"title":"Ihr API Key für releva.nz und Verkaufskanal \\"{salesChannelName}\\" ist fehlerhaft.","message":"<a href=\\"#/sw/plugin/settings/RelevaRetargeting\\">Konfiguriere Plugin hier.</a>"},"1579084006":{"title":"Es wurde kein Verkaufskanal für das releva.nz Plugin konfiguriert.","message":"Noch nicht registriert? <a href=\\"https://releva.nz\\" target=\\"blank\\">Jetzt nachholen.</a> Bereits registriert? <a href=\\"#/sw/plugin/settings/RelevaRetargeting\\">Konfiguriere Plugin hier.</a>"},"1579849966":{"title":"Verkaufskanal hat keine Domain.","message":"Verkaufskanal <a href=\\"#/sw/sales/channel/detail/{salesChannelId}\\">{salesChannelName}</a> benötigt eine Domain für den releva.nz Produktexport."},"fallback":{"title":"{title}","message":"code: {code}, data: {data}"}}}}')},OzCK:function(e,t){e.exports='{% block sw_system_config %}\n    <div class="sw-system-config">\n        <div class="sw-system-config__global-sales-channel-switch">\n            <sw-entity-single-select\n                labelProperty="name"\n                valueProperty="id"\n                entity="sales_channel"\n                @input="onSalesChannelChanged"\n                :label="$tc(\'sw-settings.system-config.labelSalesChannelSelect\')"\n                :placeholder="$tc(\'releva-retargeting.general.storefront\')"\n                :criteria="storefrontSalesChannelCriteria"\n                :value="currentSalesChannelId"\n            />\n        </div>\n        {% block sw_system_config_content_card_releva %}\n            <sw-card\n                v-if="typeof actualConfigData[currentSalesChannelId] !== \'undefined\'" \n                v-for="card, index in config"\n                :key="index"\n                :class="`sw-system-config__card--${index}`"\n                :isLoading="isLoading"\n                :title="getInlineSnippet(card.title)"\n            >\n                <template v-if="currentSalesChannelId !== null || index !== 0" >\n                    <slot name="beforeElements" v-bind="{ card, config: actualConfigData[currentSalesChannelId] }"></slot>\n                    <template v-if="!isLoading">\n                        <template v-for="element in card.elements">\n                            <slot name="card-element" v-bind="{ element: getElementBind(element), config: actualConfigData[currentSalesChannelId], card }">\n                                {% block sw_system_config_content_card_field %}\n                                    <sw-form-field-renderer \n                                        v-bind="getElementBind(element)"\n                                        v-model="actualConfigData[currentSalesChannelId][element.name]"\n                                    >\n                                    </sw-form-field-renderer>\n                                {% endblock %}\n                            </slot>\n                        </template>\n                        <slot name="card-element-last" />\n                    </template>\n                    <slot name="afterElements" v-bind="{ card, config: actualConfigData[currentSalesChannelId] }"></slot>\n                </template>\n                <template v-else>\n                    {{ $tc(\'releva-retargeting.general.fallback_config_relevanzapikey\') }}\n                </template>\n            </sw-card>\n       {% endblock %}\n   </div>\n{% endblock %}\n'},SwDr:function(e,t,n){"use strict";n.r(t);var a=n("nKaH"),r=n.n(a);const{Component:i,Mixin:s}=Shopware;i.register("releva-retargeting-statistic",{template:r.a,inject:["retargetingApiService"],mixins:[s.getByName("notification")],metaInfo(){return{title:this.$createTitle()}},data:()=>({currentIframeUrl:null,salesChannelsToIframeUrl:null}),created(){this.retargetingApiService.getInvolvedSalesChannelsToIframeUrls().then(e=>{this.handleResponseErrors(e.errors),this.salesChannelsToIframeUrl=e.data,this.onSalesChannelsToIframeUrlSelectionChange(this.salesChannelsToIframeUrl[0].iframeUrl)})},methods:{handleResponseErrors(e){for(var t in e){var n="string"==typeof this.$t("releva-retargeting.messages."+e[t].code)?{title:this.$t("releva-retargeting.messages.fallback.title",{title:e[t].message}),message:this.$t("releva-retargeting.messages.fallback.message",{code:e[t].code,data:JSON.stringify(e[t].data)})}:{title:this.$t("releva-retargeting.messages."+e[t].code+".title",e[t].data),message:this.$t("releva-retargeting.messages."+e[t].code+".message",e[t].data)};this.createNotificationError({title:n.title,message:n.message})}},onSalesChannelsToIframeUrlSelectionChange(e){this.currentIframeUrl=e}}});var l=n("OzCK"),o=n.n(l);const{Component:c,Defaults:g}=Shopware,{Criteria:u}=Shopware.Data;c.extend("releva-system-config","sw-system-config",{template:o.a,inject:["retargetingApiService"],computed:{storefrontSalesChannelCriteria:function(){const e=new u;return e.addFilter(u.equals("typeId",g.storefrontSalesChannelTypeId)),e}},methods:{handleResponseErrors(e){for(var t in e){var n="string"==typeof this.$t("releva-retargeting.messages."+e[t].code)?{title:this.$t("releva-retargeting.messages.fallback.title",{title:e[t].message}),message:this.$t("releva-retargeting.messages.fallback.message",{code:e[t].code,data:JSON.stringify(e[t].data)})}:{title:this.$t("releva-retargeting.messages."+e[t].code+".title",e[t].data),message:this.$t("releva-retargeting.messages."+e[t].code+".message",e[t].data)};this.createNotificationError({title:n.title,message:n.message})}},saveReleva(){this.currentSalesChannelId&&(this.isLoading=!0,this.retargetingApiService.getVerifyApiKey({apiKey:this.actualConfigData[this.currentSalesChannelId]["RelevaRetargeting.config.relevanzApiKey"],salesChannel:this.currentSalesChannelId}).then(e=>{void 0!==this.actualConfigData[this.currentSalesChannelId]["RelevaRetargeting.config.relevanzUserId"]&&this.actualConfigData[this.currentSalesChannelId]["RelevaRetargeting.config.relevanzUserId"]===e.data.userId||(this.actualConfigData.hasOwnProperty(this.currentSalesChannelId)&&delete this.actualConfigData[this.currentSalesChannelId],this.readAll()),this.handleResponseErrors(e.errors),this.isLoading=!1}))}}});var h=n("dmtk"),d=n.n(h);const{Component:f,Defaults:p}=Shopware,{Criteria:v}=Shopware.Data;f.override("sw-plugin-config",{template:d.a,methods:{onRelevaSave(){this.onSave(),this.$refs.systemConfig.saveReleva()}}});var m=n("SwLI");class y extends m.default{constructor(e,t,n="/releva/retargeting"){super(e,t,n)}getInvolvedSalesChannelsToIframeUrls(e){const t=`${this.getApiBasePath()}/getInvolvedSalesChannelsToIframeUrls`;return this.httpClient.post(t,{config:e},{headers:this.getBasicHeaders()}).then(e=>m.default.handleResponse(e))}getVerifyApiKey(e){const t=`${this.getApiBasePath()}/getVerifyApiKey`;return this.httpClient.post(t,{config:e},{headers:this.getBasicHeaders()}).then(e=>m.default.handleResponse(e))}}var b=y;const{Application:k}=Shopware;k.addServiceProvider("retargetingApiService",e=>{const t=k.getContainer("init");return new b(t.httpClient,e.loginService)});var C=n("O1Uz"),O=n("Xwpa");const{Module:w}=Shopware;w.register("releva-retargeting",{type:"plugin",name:"relevanz-retargeting",title:"releva-retargeting.general.mainMenuItemGeneral",favicon:"../../../../../relevaretargeting/administration/static/img/favicon/modules/icon-module-releva-retargeting.png",snippets:{"de-DE":C,"en-GB":O},routes:{statistic:{component:"releva-retargeting-statistic",path:"statistic"}},navigation:[{label:"releva-retargeting.general.mainMenuItemGeneral",path:"releva.retargeting.statistic",parent:"sw-marketing"}]})},SwLI:function(e,t,n){"use strict";n.r(t);var a=n("lwsE"),r=n.n(a),i=n("W8MJ"),s=n.n(i),l=n("CkOj"),o=function(){function e(t,n,a){var i=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"application/vnd.api+json";r()(this,e),this.httpClient=t,this.loginService=n,this.apiEndpoint=a,this.contentType=i}return s()(e,[{key:"getList",value:function(t){var n=t.page,a=void 0===n?1:n,r=t.limit,i=void 0===r?25:r,s=t.sortBy,l=t.sortDirection,o=void 0===l?"asc":l,c=t.sortings,g=t.queries,u=t.term,h=t.criteria,d=t.aggregations,f=t.associations,p=t.headers,v=t.versionId,m=t.ids,y=this.getBasicHeaders(p),b={page:a,limit:i};return c?b.sort=c:s&&s.length&&(b.sort=("asc"===o.toLowerCase()?"":"-")+s),m&&(b.ids=m.join("|")),u&&(b.term=u),h&&(b.filter=[h.getQuery()]),d&&(b.aggregations=d),f&&(b.associations=f),v&&(y=Object.assign(y,e.getVersionHeader(v))),g&&(b.query=g),b.term&&b.term.length||b.filter&&b.filter.length||b.aggregations||b.sort||b.queries||b.associations?this.httpClient.post("".concat(this.getApiBasePath(null,"search")),b,{headers:y}).then((function(t){return e.handleResponse(t)})):this.httpClient.get(this.getApiBasePath(),{params:b,headers:y}).then((function(t){return e.handleResponse(t)}))}},{key:"getById",value:function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{};if(!t)return Promise.reject(new Error("Missing required argument: id"));var r=n,i=this.getBasicHeaders(a);return this.httpClient.get(this.getApiBasePath(t),{params:r,headers:i}).then((function(t){return e.handleResponse(t)}))}},{key:"updateById",value:function(t,n){var a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},r=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};if(!t)return Promise.reject(new Error("Missing required argument: id"));var i=a,s=this.getBasicHeaders(r);return this.httpClient.patch(this.getApiBasePath(t),n,{params:i,headers:s}).then((function(t){return e.handleResponse(t)}))}},{key:"deleteAssociation",value:function(e,t,n,a){if(!e||!n||!n)return Promise.reject(new Error("Missing required arguments."));var r=this.getBasicHeaders(a);return this.httpClient.delete("".concat(this.getApiBasePath(e),"/").concat(t,"/").concat(n),{headers:r}).then((function(e){return e.status>=200&&e.status<300?Promise.resolve(e):Promise.reject(e)}))}},{key:"create",value:function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},r=n,i=this.getBasicHeaders(a);return this.httpClient.post(this.getApiBasePath(),t,{params:r,headers:i}).then((function(t){return e.handleResponse(t)}))}},{key:"delete",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{};if(!e)return Promise.reject(new Error("Missing required argument: id"));var a=Object.assign({},t),r=this.getBasicHeaders(n);return this.httpClient.delete(this.getApiBasePath(e),{params:a,headers:r})}},{key:"clone",value:function(t){return t?this.httpClient.post("/_action/clone/".concat(this.apiEndpoint,"/").concat(t),null,{headers:this.getBasicHeaders()}).then((function(t){return e.handleResponse(t)})):Promise.reject(new Error("Missing required argument: id"))}},{key:"versionize",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},a="/_action/version/".concat(this.apiEndpoint,"/").concat(e),r=Object.assign({},t),i=this.getBasicHeaders(n);return this.httpClient.post(a,{},{params:r,headers:i})}},{key:"mergeVersion",value:function(t,n,a,r){if(!t)return Promise.reject(new Error("Missing required argument: id"));if(!n)return Promise.reject(new Error("Missing required argument: versionId"));var i=Object.assign({},a),s=Object.assign(e.getVersionHeader(n),this.getBasicHeaders(r)),l="_action/version/merge/".concat(this.apiEndpoint,"/").concat(n);return this.httpClient.post(l,{},{params:i,headers:s})}},{key:"getApiBasePath",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",n="";return t&&t.length&&(n+="".concat(t,"/")),e&&e.length>0?"".concat(n).concat(this.apiEndpoint,"/").concat(e):"".concat(n).concat(this.apiEndpoint)}},{key:"getBasicHeaders",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t={Accept:this.contentType,Authorization:"Bearer ".concat(this.loginService.getToken()),"Content-Type":"application/json"};return Object.assign({},t,e)}},{key:"apiEndpoint",get:function(){return this.endpoint},set:function(e){this.endpoint=e}},{key:"httpClient",get:function(){return this.client},set:function(e){this.client=e}},{key:"contentType",get:function(){return this.type},set:function(e){this.type=e}}],[{key:"handleResponse",value:function(t){if(null===t.data||void 0===t.data)return t;var n=t.data,a=t.headers;return a&&a["content-type"]&&"application/vnd.api+json"===a["content-type"]&&(n=e.parseJsonApiData(n)),n}},{key:"parseJsonApiData",value:function(e){return Object(l.a)(e)}},{key:"getVersionHeader",value:function(e){return{"sw-version-id":e}}}]),e}();t.default=o},Xwpa:function(e){e.exports=JSON.parse('{"releva-retargeting":{"general":{"mainMenuItemGeneral":"releva.nz retargeting","storefront":"Storefront-sales-channel","fallback_config_relevanzapikey":"Api Key is only configurable with selected @:(releva-retargeting.general.storefront)."},"messages":{"1553935480":{"title":"Unable to connect to releva.nz API-Server.","message":"salesChannel: {salesChannelName}, data: {data}"},"1553935569":{"title":"@:(releva-retargeting.messages.1553935786.title)","message":"@:(releva-retargeting.messages.1553935786.message)"},"1553935786":{"title":"The API key for sales-channel \\"{salesChannelName}\\" cannot be verified. Please make sure that the API key is correct.","message":"<a href=\\"#/sw/plugin/settings/RelevaRetargeting\\">Configure plugin here.</a>"},"1579084006":{"title":"No sales-channels are configured for releva.nz plugin.","message":"Not yet registered? <a href=\\"https://releva.nz\\" target=\\"blank\\">Now catch up.</a> Already registered? <a href=\\"#/sw/plugin/settings/RelevaRetargeting\\">Configure plugin here.</a>"},"1579849966":{"title":"Sales-channel doesn\'t have domain.","message":"Sales-channel <a href=\\"#/sw/sales/channel/detail/{salesChannelId}\\">{salesChannelName}</a> need a domain for the releva.nz article export."},"fallback":{"title":"{title}","message":"code: {code}, data: {data}"}}}}')},dmtk:function(e,t){e.exports='{% block sw_plugin_config_actions_save %}\n    <template v-if="domain == \'RelevaRetargeting.config\'">\n        <sw-button variant="primary" class="sw-plugin-config__save-action" @click.prevent="onRelevaSave">\n            {{ $tc(\'sw-plugin-config.buttonSave\') }}\n        </sw-button>\n    </template>\n    <template v-else>\n        {% parent() %}\n    </template>\n{% endblock %}\n{% block sw_system_config %}\n    <template v-if="domain == \'RelevaRetargeting.config\'">\n        <releva-system-config \n            :domain="domain"\n            :salesChannelId="salesChannelId"\n            ref="systemConfig">\n        </releva-system-config>\n    </template>\n    <template v-else>\n        {% parent() %}\n    </template>\n{% endblock %}'},lO2t:function(e,t,n){"use strict";n.d(t,"b",(function(){return j}));var a=n("GoyQ"),r=n.n(a),i=n("YO3V"),s=n.n(i),l=n("E+oP"),o=n.n(l),c=n("wAXd"),g=n.n(c),u=n("Z0cm"),h=n.n(u),d=n("lSCD"),f=n.n(d),p=n("YiAA"),v=n.n(p),m=n("4qC0"),y=n.n(m),b=n("Znm+"),k=n.n(b),C=n("Y+p1"),O=n.n(C),w=n("UB5X"),S=n.n(w);function j(e){return void 0===e}t.a={isObject:r.a,isPlainObject:s.a,isEmpty:o.a,isRegExp:g.a,isArray:h.a,isFunction:f.a,isDate:v.a,isString:y.a,isBoolean:k.a,isEqual:O.a,isNumber:S.a,isUndefined:j}},lYO9:function(e,t,n){"use strict";n.d(t,"g",(function(){return v})),n.d(t,"a",(function(){return m})),n.d(t,"c",(function(){return y})),n.d(t,"h",(function(){return b})),n.d(t,"f",(function(){return k})),n.d(t,"b",(function(){return C})),n.d(t,"e",(function(){return O})),n.d(t,"d",(function(){return w}));var a=n("lSNA"),r=n.n(a),i=n("QkVN"),s=n.n(i),l=n("BkRI"),o=n.n(l),c=n("mwIZ"),g=n.n(c),u=n("D1y2"),h=n.n(u),d=n("lO2t");function f(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function p(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?f(n,!0).forEach((function(t){r()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):f(n).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}s.a,o.a,g.a,h.a;var v=s.a,m=o.a,y=g.a,b=h.a;function k(e,t){return Object.prototype.hasOwnProperty.call(e,t)}function C(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};return JSON.parse(JSON.stringify(e))}function O(e,t){return e===t?{}:d.a.isObject(e)&&d.a.isObject(t)?d.a.isDate(e)||d.a.isDate(t)?e.valueOf()===t.valueOf()?{}:t:Object.keys(t).reduce((function(n,a){if(!k(e,a))return p({},n,r()({},a,t[a]));if(d.a.isArray(t[a])){var i=w(e[a],t[a]);return Object.keys(i).length>0?p({},n,r()({},a,t[a])):n}if(d.a.isObject(t[a])){var s=O(e[a],t[a]);return!d.a.isObject(s)||Object.keys(s).length>0?p({},n,r()({},a,s)):n}return e[a]!==t[a]?p({},n,r()({},a,t[a])):n}),{}):t}function w(e,t){if(e===t)return[];if(!d.a.isArray(e)||!d.a.isArray(t))return t;if(e.length<=0&&t.length<=0)return[];if(e.length!==t.length)return t;if(!d.a.isObject(t[0]))return t.filter((function(t){return!e.includes(t)}));var n=[];return t.forEach((function(a,r){var i=O(e[r],t[r]);Object.keys(i).length>0&&n.push(t[r])})),n}},nKaH:function(e,t){e.exports='{% block releva_retargeting_statistic %}\n    <sw-page class="releva-retargeting-statistic">\n        <template slot="smart-bar-actions">\n            {% block releva_retargeting_statistic_actions %}\n                <sw-single-select\n                    v-if="salesChannelsToIframeUrl && salesChannelsToIframeUrl.length > 1"\n                    style="min-width: 250px;text-align: left; margin: 0;"\n                    size="big" \n                    labelProperty="salesChannel"\n                    valueProperty="iframeUrl"\n                    @input="onSalesChannelsToIframeUrlSelectionChange"\n                    :value="currentIframeUrl"\n                    :options="salesChannelsToIframeUrl"\n                    :placeholder="$tc(\'releva-retargeting.general.storefront\')"\n                ></sw-single-select>\n            {% endblock %}\n        </template>\n        <template slot="content">\n            {% block releva_retargeting_statistic_content %}\n                <div v-if=\'currentIframeUrl\' style="position: relative; height: 100%;">\n                    <template>\n                        <iframe v-bind:src="currentIframeUrl" style="position:absolute; left:0; right:0; top: 0 ; bottom: 0; width: 100%; height: 100%; border: none;"></iframe>\n                    </template>\n                </div>\n            {% endblock %}\n        </template>\n    </sw-page>\n{% endblock %}\n'}},[["SwDr","runtime","vendors-node"]]]);