<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Controller;

use Releva\Retargeting\Base\Export\Item\ProductExportItem;
use Releva\Retargeting\Base\Export\ProductJsonExporter;
use Releva\Retargeting\Base\Export\ProductCsvExporter;
use Releva\Retargeting\Shopware\Internal\ShopInfo;
use Releva\Retargeting\Base\Credentials;
use Releva\Retargeting\Shopware\Internal\RepositoryHelper;

use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\Cart\ProductLineItemFactory;
use Shopware\Core\Content\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\Annotation\RouteScope; // need for anotations
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Defaults;
use Shopware\Storefront\Controller\StorefrontController as ShopwareStorefrontController;
use Shopware\Core\System\SystemConfig\SystemConfigService;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @RouteScope(scopes={"storefront"})
 */
class StorefrontController extends ShopwareStorefrontController {
    
    private const ITEMS_PER_PAGE = 50;
    
    /**
     * @var [(string) sales-channel-id[(string) category-id, ], ]
     */
    private $salesChannelCategories = [];
    
    /**
     * @var [(string) sales-channel-id => EntitySearchResult, ]
     */
    private $salesChannelProducts = [];

    /**
     * @Route("/releva/retargeting/callback", name="frontend.releva.retargeting.callback", options={"seo"="false"}, methods={"GET"})
     */
    public function callbackAction(Request $request, SalesChannelContext $salesChannelContext): JsonResponse
    {
        if (!$this->checkCredentials($request, $salesChannelContext)) {
            http_response_code(401);
            die;
        }
        /* @var $shopInfo ShopInfo */
        $shopInfo = $this->get(ShopInfo::class);
        $callbackData = [
            'plugin-version' => $shopInfo->getPluginVersion(),
            'shop' => [
                'system' => $shopInfo->getShopSystem(),
                'version' => $shopInfo->getShopVersion(),
            ],
            'environment' => $shopInfo->getServerEnvironment(),
            'callbacks' => [
                'callback' => [
                    'url' => $request->getSchemeAndHttpHost().$shopInfo->getUrlCallback(),
                    'parameters' => [],
                ],
                'export' => [
                    'url' => $request->getSchemeAndHttpHost().$shopInfo->getUrlProductExport(),
                    'parameters' => [
                        'format' => [
                            'values' => ['csv', 'json'],
                            'default' => 'csv',
                            'optional' => true,
                        ],
                        'page' => [
                            'type' => 'integer',
                            'optional' => true,
                            'info' => ['items-per-page' => self::ITEMS_PER_PAGE, ],
                        ],
                    ],
                ],
            ]
        ];
        return new JsonResponse($callbackData);
    }
    
    /**
     * @Route("/releva/retargeting/products", name="frontend.releva.retargeting.products", options={"seo"="false"}, methods={"GET"})
     */
    public function productsAction(Request $request, SalesChannelContext $salesChannelContext) : void
    {
        if (!$this->checkCredentials($request, $salesChannelContext)) {
            http_response_code(401);
            die;
        }
        $productsSearchResult = $this->getSalesChannelProducts($salesChannelContext, (int) $request->get('page') < 1 ? null : (int) $request->get('page') - 1);
        if ($productsSearchResult->getTotal() === 0) {
            http_response_code(404);
            die;
        }
        $exporter = $request->get('format') === 'json' ? new ProductJsonExporter() : new ProductCsvExporter();
        /* @var $cartService CartService */
        $cartService = $this->get(CartService::class);
        $detailUrl = $request->getSchemeAndHttpHost().'/detail/%s';//default routing from symfony to product-detail-page
        $salesChannelCategoryIds = $this->getSalesChannelCategoryIds($salesChannelContext);
        foreach ($productsSearchResult as $product) {
            /* @var $product ProductEntity */
            $productCategoryIds = [];
            foreach ($product->getCategories() as $productCategory) {
                $categoryIds = array_filter(explode('|', $productCategory->getPath().'|'.$productCategory->getId()));
                if (array_intersect($categoryIds, $salesChannelCategoryIds)){
                    $productCategoryIds[] = $productCategory->getId();
                }
            }
            // create cart and fill with one product to get calculated price
            $lineItem = (new ProductLineItemFactory)->create($product->getId());
            $cartService->add($cartService->createNew('releva'), $lineItem, $salesChannelContext);
            $price = $priceOffer = $lineItem->getPrice()->getTotalPrice();
            foreach ($cartService->getCart('releva', $salesChannelContext)->getLineItems()->fmap(function (LineItem $lineItem) {
                return $lineItem->getType() === 'promotion' ? $lineItem : false;
            }) as $promotionLineItem) {
                $priceOffer += $promotionLineItem->getPrice()->getTotalPrice();//promotion has negative price
            }
            $defaultTranslation = $product->getTranslations()->get($product->getId().'-'.Defaults::LANGUAGE_SYSTEM);
            $exporter->addItem(
                new ProductExportItem(
                    (string) $product->getId(),
                    (array) $productCategoryIds,
                    (string) ($product->getName() ?: $defaultTranslation->getName()),
                    (string) ($product->getMetaDescription() ?: $defaultTranslation->getMetaDescription()),
                    (string) ($product->getDescription() ?: $defaultTranslation->getDescription()),
                    (float) $price,
                    (float) $priceOffer,
                    (string) sprintf($detailUrl, $product->getId()),
                    (string) $lineItem->getCover()->getUrl()
                )
            );
        }
        foreach ($exporter->getHttpHeaders() as $headerKey => $headerValue) {
            header(sprintf('%s:%s', $headerKey, $headerValue));
        }
        echo $exporter->getContents();
        die;
    }
    
    private function checkCredentials (Request $request, SalesChannelContext $salesChannelContext): bool {
        $salesChannelEntity = $salesChannelContext->getSalesChannel();
        /* @var $systemConfigService SystemConfigService */
        $systemConfigService = $this->get(SystemConfigService::class);
        $credentials = new Credentials(
            $systemConfigService->get('RelevaRetargeting.config.relevanzApiKey', $salesChannelEntity->getId()),
            $systemConfigService->get('RelevaRetargeting.config.relevanzUserId', $salesChannelEntity->getId())
        );
        return $credentials->isComplete() && $credentials->getAuthHash() === $request->get('auth');
    }
    
    private function getSalesChannelCategoryIds (SalesChannelContext $salesChannelContext): array {
        $salesChannel = $salesChannelContext->getSalesChannel();
        if (!array_key_exists($salesChannel->getId(), $this->salesChannelCategories)) {
            $this->salesChannelCategories[$salesChannel->getId()] = [];
            foreach ([
                $salesChannel->getNavigationCategoryId(),
                $salesChannel->getFooterCategoryId(),
                $salesChannel->getServiceCategoryId(),
            ] as $categoryId) {
                if ($categoryId !== null && !in_array($categoryId, $this->salesChannelCategories[$salesChannel->getId()])) {
                    $this->salesChannelCategories[$salesChannel->getId()][] = $categoryId;
                }
            }
        }
        return $this->salesChannelCategories[$salesChannel->getId()];
    }
    
    private function getSalesChannelProducts (SalesChannelContext $salesChannelContext, int $page = null): EntitySearchResult {
        $salesChannel = $salesChannelContext->getSalesChannel();
        if (!array_key_exists($salesChannel->getId(), $this->salesChannelProducts)) {
            /* @var $productRepository EntityRepository */
            $productRepository = $this->get('product.repository');;
            RepositoryHelper::setAutoload($productRepository, ['categories', 'translations', ]);
            $criteria = (new Criteria())
                ->setLimit($page === null ? null : self::ITEMS_PER_PAGE)
                ->setOffset($page === null ? null : $page * self::ITEMS_PER_PAGE)
                ->addFilter(new EqualsAnyFilter('categoryTree', $this->getSalesChannelCategoryIds($salesChannelContext)))
            ;
            $this->salesChannelProducts[$salesChannel->getId()] = $productRepository->search($criteria, $salesChannelContext->getContext());
        }
        return $this->salesChannelProducts[$salesChannel->getId()];
    }
}