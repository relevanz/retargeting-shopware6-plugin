import { COOKIE_CONFIGURATION_UPDATE } from 'src/plugin/cookie/cookie-configuration.plugin';

export default class RelevanzRetargetingUtil {
    constructor() {
        this._init();
    }
    _init() {
        this._includeRelevanzPixel();
        this._registerEvents();
    }
    _registerEvents () {
        document.$emitter.subscribe(COOKIE_CONFIGURATION_UPDATE, this._includeRelevanzPixel);
    }
    _includeRelevanzPixel() {
        var value = '; ' + document.cookie;
        var parts = value.split('; relevanzRetargeting=');
        if (parts.length == 2 && parts.pop().split(';').shift() === 'allow') {// cookie setted
            window.relevanzRetargetingForcePixel = true;
        }
    }

}