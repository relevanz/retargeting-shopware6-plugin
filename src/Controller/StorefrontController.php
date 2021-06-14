<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Controller;

use Releva\Retargeting\Base\Credentials;
use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Shopware\Internal\MessagesBridge;
use Releva\Retargeting\Shopware\Internal\ProductExporter;
use Releva\Retargeting\Shopware\Internal\ShopInfo;

use Shopware\Core\Framework\Routing\Annotation\RouteScope; // need for anotations
use Shopware\Storefront\Controller\StorefrontController as ShopwareStorefrontController;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
        $response = new JsonResponse();
        if (!$this->checkCredentials($request, $salesChannelContext)) {
            $response->setStatusCode(401)->setContent('');
            return $response;
        } else {
            /* @var $shopInfo ShopInfo */
            $shopInfo = $this->get(ShopInfo::class);
            $response->setData([
                'plugin-version' => $shopInfo->getPluginVersion(),
                'shop' => ['system' => $shopInfo->getShopSystem(), 'version' => $shopInfo->getShopVersion(), ],
                'environment' => $shopInfo->getServerEnvironment(),
                'callbacks' => [
                    'callback' => ['url' => $request->getUriForPath($shopInfo->getUrlCallback()), 'parameters' => [], ],
                    'export' => [
                        'url' => $request->getUriForPath($shopInfo->getUrlProductExport()),
                        'parameters' => [
                            'format' => ['values' => ['csv', 'json'], 'default' => 'csv', 'optional' => true, ],
                            'page' => ['type' => 'integer', 'optional' => true, 'info' => ['items-per-page' => self::ITEMS_PER_PAGE, ], ],
                        ],
                    ],
                ]
            ]);
        }
        return $response;
    }
    
    /**
     * @Route("/releva/retargeting/products", name="frontend.releva.retargeting.products", options={"seo"="false"}, methods={"GET"})
     */
    public function productsAction(Request $request, SalesChannelContext $salesChannelContext) : Response
    {
        $response = new Response;
        if (!$this->checkCredentials($request, $salesChannelContext)) {
            $response->setStatusCode(401)->setContent('');
            return $response;
        } else {
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
                   $response->headers->set($headerKey, $headerValue);
                }
                $response->setContent($exporter->getContents());
            } catch (\Exception $exception) {
                $response->setStatusCode($exception instanceof RelevanzException && $exception->getCode() === 1585554289 ? 400 : 500);
            }
        }
        return $response;
    }
    
    private function checkCredentials (Request $request, SalesChannelContext $salesChannelContext): bool {
        $salesChannelEntity = $salesChannelContext->getSalesChannel();
        /* @var $systemConfigService SystemConfigService */
        $systemConfigService = $this->get(SystemConfigService::class);
        if ($systemConfigService->get('RelevaRetargeting.config.trackingActive', $salesChannelEntity->getId())) {
            $credentials = new Credentials(
                $systemConfigService->get('RelevaRetargeting.config.relevanzApiKey', $salesChannelEntity->getId()),
                $systemConfigService->get('RelevaRetargeting.config.relevanzUserId', $salesChannelEntity->getId())
            );
            if ($credentials->isComplete() && $credentials->getAuthHash() === $request->get('auth')) {
                return true;
            } else {
                $this->get(MessagesBridge::class)->add('Auth parameter is invalid.', 1585739840, [
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
            $this->get(MessagesBridge::class)->add('Tracking is not active.', 1585739838, [
                'salesChannelName' => $salesChannelEntity->getName(),
                'salesChannelId' => $salesChannelEntity->getId(),
            ]);
        }
        return false;
    }
    
}