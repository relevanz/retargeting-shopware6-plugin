import { COOKIE_CONFIGURATION_UPDATE } from 'src/plugin/cookie/cookie-configuration.plugin';

export default class RelevanzRetargetingUtil {
    constructor() {
        this._init();
    }
    _init() {
        this._includeRelevanzPixel({detail: ("; " + document.cookie + ";").indexOf("; relevanzRetargeting=allow;") >= 0 ? {relevanzRetargeting: true} : {}});
        this._registerEvents();
    }
    _registerEvents () {
        document.$emitter.subscribe(typeof COOKIE_CONFIGURATION_UPDATE === "undefined" ? 'CookieConfiguration_Update' : COOKIE_CONFIGURATION_UPDATE, this._includeRelevanzPixel);
    }
    _includeRelevanzPixel(updatedCookies) {
        if (typeof updatedCookies.detail["relevanzRetargeting"] !== "undefined") {
            window.relevanzRetargetingForcePixel = true;
        }
    }

}
