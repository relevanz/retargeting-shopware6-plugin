import ApiService from 'src/core/service/api.service';

class RetargetingApiService extends ApiService 
{
    constructor(httpClient, loginService, apiEndpoint = '/releva/retargeting') {
        super(httpClient, loginService, apiEndpoint);
    }
    
    getInvolvedSalesChannelsToIframeUrls(config) {
        const apiRoute = `${this.getApiBasePath()}/getInvolvedSalesChannelsToIframeUrls`
        return this.httpClient.post(
            apiRoute,
            {
                config: config
            },
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
    
    getVerifyApiKey(config) {
        const apiRoute = `${this.getApiBasePath()}/getVerifyApiKey`
        return this.httpClient.post(
            apiRoute,
            {
                config: config
            },
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
    
}
export default RetargetingApiService;
