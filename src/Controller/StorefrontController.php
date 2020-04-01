<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Controller;

use Releva\Retargeting\Base\Credentials;
use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Shopware\Internal\ProductExporter;
use Releva\Retargeting\Shopware\Internal\ShopInfo;

use Shopware\Core\Framework\Routing\Annotation\RouteScope; // need for anotations
use Shopware\Storefront\Controller\StorefrontController as ShopwareStorefrontController;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class StorefrontController extends ShopwareStorefrontController
{
    
    private const ITEMS_PER_PAGE = 50;
    
    /**
     * @Route("/releva/retargeting/callback", name="frontend.releva.retargeting.callback", options={"seo"="false"}, methods={"GET"})
     */
    public function callbackAction(Request $request, SalesChannelContext $salesChannelContext): JsonResponse
    {
        $this->checkCredentials($request, $salesChannelContext);
        /* @var $shopInfo ShopInfo */
        $shopInfo = $this->get(ShopInfo::class);
        return new JsonResponse([
            'plugin-version' => $shopInfo->getPluginVersion(),
            'shop' => ['system' => $shopInfo->getShopSystem(), 'version' => $shopInfo->getShopVersion(), ],
            'environment' => $shopInfo->getServerEnvironment(),
            'callbacks' => [
                'callback' => ['url' => $request->getSchemeAndHttpHost().$shopInfo->getUrlCallback(), 'parameters' => [], ],
                'export' => [
                    'url' => $request->getSchemeAndHttpHost().$shopInfo->getUrlProductExport(),
                    'parameters' => [
                        'format' => ['values' => ['csv', 'json'], 'default' => 'csv', 'optional' => true, ],
                        'page' => ['type' => 'integer', 'optional' => true, 'info' => ['items-per-page' => self::ITEMS_PER_PAGE, ], ],
                    ],
                ],
            ]
        ]);
    }
    
    /**
     * @Route("/releva/retargeting/products", name="frontend.releva.retargeting.products", options={"seo"="false"}, methods={"GET"})
     */
    public function productsAction(Request $request, SalesChannelContext $salesChannelContext) : void
    {
        $this->checkCredentials($request, $salesChannelContext);
        try {
            $page = (int) $request->get('page') < 1 ? null : (int) $request->get('page') - 1;
            $productExporter = $this->get(ProductExporter::class);
            $exporter = $productExporter->export(
                $salesChannelContext,
                $request->get('format') === 'json' ? ProductExporter::FORMAT_JSON : ProductExporter::FORMAT_CSV,
                $page === null ? null : self::ITEMS_PER_PAGE,
                $page === null ? null : $page * self::ITEMS_PER_PAGE
            );
            foreach ($exporter->getHttpHeaders() as $headerKey => $headerValue) {
                header(sprintf('%s:%s', $headerKey, $headerValue));
            }
            echo $exporter->getContents();
        } catch (\Exception $exception) {
            http_response_code($exception instanceof RelevanzException && $exception->getCode() === 1585554289 ? 400 : 500);
        }
        die;
    }
    
    private function checkCredentials (Request $request, SalesChannelContext $salesChannelContext): self {
        $salesChannelEntity = $salesChannelContext->getSalesChannel();
        /* @var $systemConfigService SystemConfigService */
        $systemConfigService = $this->get(SystemConfigService::class);
        if ($systemConfigService->get('RelevaRetargeting.config.trackingActive', $salesChannelEntity->getId())) {
            $credentials = new Credentials(
                $systemConfigService->get('RelevaRetargeting.config.relevanzApiKey', $salesChannelEntity->getId()),
                $systemConfigService->get('RelevaRetargeting.config.relevanzUserId', $salesChannelEntity->getId())
            );
            if ($credentials->isComplete() && $credentials->getAuthHash() === $request->get('auth')) {
                return $this;
            } else {
                $this->get('Releva\Retargeting\Shopware\Internal\LoggerBridge')->add('Auth is not correct', 1585739840, [
                    'salesChannelName' => $salesChannelEntity->getName(),
                    'salesChannelId' => $salesChannelEntity->getId(),
                    'credentialsComplete' => $credentials->isComplete(),
                    'auth' => [
                        'requested' => $request->get('auth'),
                        'expected' => $credentials->getAuthHash(),
                    ],
                ]);
            }
        } else {
            $this->get('Releva\Retargeting\Shopware\Internal\LoggerBridge')->add('Tracking is not active', 1585739838, [
                'salesChannelName' => $salesChannelEntity->getName(),
                'salesChannelId' => $salesChannelEntity->getId(),
            ]);
        }
        http_response_code(401);
        die;
    }
    
}