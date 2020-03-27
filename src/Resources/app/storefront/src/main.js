import { COOKIE_CONFIGURATION_UPDATE } from 'src/plugin/cookie/cookie-configuration.plugin';

function eventCallback(updatedCookies) {
    
    if (typeof updatedCookies.detail.relevanzRetargeting !== 'undefined') {
        includeRelevanz();
    }
}

function includeRelevanz() {
    var value = '; ' + document.cookie;
    var parts = value.split('; relevanzRetargeting=');
    if (parts.length == 2 && parts.pop().split(';').shift() === 'allow') {// cookie setted
        var element = document.querySelector('#relevanzRetargetingConfig');
        if (typeof element.getAttribute('data-src') === 'string') {
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = element.getAttribute('data-src');
            if (element.getAttribute('data-async') === 'async') {
                script.async = true;
            }
            document.body.appendChild(script);
        }
    }
}
includeRelevanz();
document.$emitter.subscribe(COOKIE_CONFIGURATION_UPDATE, eventCallback);