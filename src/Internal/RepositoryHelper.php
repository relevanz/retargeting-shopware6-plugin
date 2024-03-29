<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Internal;

use Psr\Container\ContainerInterface;

use Shopware\Core\Defaults;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;

class RepositoryHelper
{
    
    private $container;
    
    /**
     * filter: only available-products
     */
    const FILTER_PRODUCT_AVAILABLE = 'filter-product-available';
    
    /**
     * filter: only available-products
     */
    const FILTER_PRODUCT_MAIN = 'filter-product-main';
    
    /**
     * filter: only storefront salseschannels
     */
    const FILTER_SALESCHANNEL_STOREFRONT = 'filter-saleschannel-storefront';
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function getSalesChannels (Context $context, array $autoloads = [], array $filters = []): EntitySearchResult
    {
        if (($key = array_search(self::FILTER_SALESCHANNEL_STOREFRONT, $filters)) !== false) {
            $filters[$key] = new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT);
        }
        return $this->handleRepository($this->container->get('sales_channel.repository'), $context, null, null, $autoloads, $filters);
    }
    
    public function getProducts (Context $context, array $autoloads = [], array $filters = [], int $limit = null, int $offset = null): EntitySearchResult
    {
        if (($key = array_search(self::FILTER_PRODUCT_AVAILABLE, $filters)) !== false) {
            $filters[$key] = new ProductAvailableFilter($context->getSource()->getSalesChannelId(), ProductVisibilityDefinition::VISIBILITY_SEARCH);
        }
        if (($key = array_search(self::FILTER_PRODUCT_MAIN, $filters)) !== false) {
            $filters[$key] = new EqualsFilter('product.parentId', null);
        }
        return $this->handleRepository($this->container->get('product.repository'), $context, $limit, $offset, $autoloads, $filters);
    }
    
    private function handleRepository (EntityRepository $repository, Context $context, int $limit = null, int $offset = null, $autoloads = [], array $filters = []): EntitySearchResult
    {
        $criteria = (new Criteria())->setLimit($limit)->setOffset($offset);
        foreach ($autoloads as $autoload) {
            $criteria->addAssociation($autoload);
        }
        foreach ($filters as $filter) {
            $criteria->addFilter($filter);
        }
        return $repository->search($criteria, $context);
    }
    
}

