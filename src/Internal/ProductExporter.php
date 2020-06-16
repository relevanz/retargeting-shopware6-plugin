<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Internal;

use Releva\Retargeting\Base\Export\ProductCsvExporter;
use Releva\Retargeting\Base\Export\ExporterInterface;
use Releva\Retargeting\Base\Export\ProductJsonExporter;
use Releva\Retargeting\Base\Export\Item\ProductExportItem;
use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Shopware\Internal\RepositoryHelper;

use Psr\Container\ContainerInterface;

use Shopware\Core\Defaults;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Cart\ProductLineItemFactory;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductExporter
{
    private $container;
    
    const FORMAT_CSV = 'csv';
    
    const FORMAT_JSON = 'json';
    
    private $salesChannelCategories = [];
    
    private $productTranslations = [];

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    private function getProductCategoryIds (ProductEntity $product, SalesChannelEntity $salesChannel):array
    {
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
        $productCategoryIds = [];
        foreach ($product->getCategories() as $productCategory) {
            $categoryIds = array_filter(explode('|', $productCategory->getPath().'|'.$productCategory->getId()));
            if (array_intersect($categoryIds, $this->salesChannelCategories[$salesChannel->getId()])){
                $productCategoryIds[] = $productCategory->getId();
            }
        }
        return $productCategoryIds;
    }
    
    private function translate(ProductEntity $product, string $method):string
    {
        if (!($out = $product->{$method}())) {
            if (!array_key_exists($product->getId(), $this->productTranslations)) {
                $this->productTranslations[$product->getId()] = $product->getTranslations()->get($product->getId().'-'.Defaults::LANGUAGE_SYSTEM);
            }
            $out = $this->productTranslations[$product->getId()]->{$method}();
        }
        return (string) $out;
    }
    
    public function export(SalesChannelContext $salesChannelContext, string $format, int $limit = null, int $offset = null): ExporterInterface
    {
        $context = $salesChannelContext->getContext();
        $productsSearchResult = $this->container->get(RepositoryHelper::class)->getProducts($context, ['categories', 'translations', 'seoUrls'], [RepositoryHelper::FILTER_PRODUCT_AVAILABLE, ], $limit, $offset);
        if ($productsSearchResult->getTotal() === 0) {
            throw new RelevanzException("No products found.", 1585554289);
        }
        $exporter = $format === 'json' ? new ProductJsonExporter() : new ProductCsvExporter();
        foreach ($productsSearchResult as $product) {
            /* @var $product ProductEntity */
            $exporter->addItem($this->getProductExportItem($product, $salesChannelContext));
        }
        return $exporter;
    }
    
    private function getProductExportItem (ProductEntity $product, SalesChannelContext $salesChannelContext) {
        /* @var $cartService CartService */
        $domain = $salesChannelContext->getSalesChannel()->getDomains()->filterByProperty('languageId', $salesChannelContext->getContext()->getLanguageId())->first();
        // create cart and fill with one product to get calculated price
        $cartService = $this->container->get(CartService::class);
        $lineItem = (new ProductLineItemFactory())->create($product->getId());
        $cartService->add($cartService->createNew('releva'), $lineItem, $salesChannelContext);
        $price = $priceOffer = $lineItem->getPrice()->getTotalPrice();
        foreach ($cartService->getCart('releva', $salesChannelContext)->getLineItems()->fmap(function (LineItem $lineItem) {
            return $lineItem->getType() === 'promotion' ? $lineItem : false;
        }) as $promotionLineItem) {
            $priceOffer += $promotionLineItem->getPrice()->getTotalPrice();//promotion has negative price
        }
        return new ProductExportItem(
            (string) $product->getId(),
            (array) $this->getProductCategoryIds($product, $salesChannelContext->getSalesChannel()),
            (string) $this->translate($product, 'getName'),
            (string) $this->translate($product, 'getMetaDescription'),
            (string) $this->translate($product, 'getDescription'),
            (float) $price,
            (float) $priceOffer,
            (string) $this->getProductUrl($product, $domain->getUrl()),
            (string) $lineItem->getCover()->getUrl()
         );
    }
    
    private function getProductUrl(ProductEntity $product, $shopUrl): string
    {
        $url = null;
        foreach ($product->getSeoUrls() as $seoUrl) {
            $seoPathInfo = $seoUrl->getSeoPathInfo();
            if ($seoPathInfo !== '') {
                $url = $seoPathInfo;
                if ($seoUrl->getIsCanonical()) {
                    break;
                }
            }
        }
        $url = $url === null ? 'detail/'.$product->getId() : $url;
        return $shopUrl.'/'.$url;
    }
    
}