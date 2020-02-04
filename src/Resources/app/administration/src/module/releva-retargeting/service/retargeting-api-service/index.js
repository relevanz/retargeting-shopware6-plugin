const { Application } = Shopware;
import RetargetingApiService from './retargeting-api-service.js';

Application.addServiceProvider('retargetingApiService', (container) => {
    const initContainer = Application.getContainer('init');
    return new RetargetingApiService(initContainer.httpClient, container.loginService);
});