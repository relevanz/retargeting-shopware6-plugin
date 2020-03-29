<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Controller;

use Releva\Retargeting\Base\RelevanzApi;
use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Shopware\Internal\ShopInfo;
use Releva\Retargeting\Shopware\Internal\RepositoryHelper;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Routing\Annotation\RouteScope; // need for annotations
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @RouteScope(scopes={"api"})
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/api/v{version}/releva/retargeting/getInvolvedSalesChannelsToIframeUrls", name="api.action.releva.retargeting.getinvolvedsaleschannelstoiframeurls", methods={"POST"})
     */
    public function getInvolvedSalesChannelsToIframeUrlsAction(Context $context): JsonResponse
    {
        /* @var $systemConfigService SystemConfigService */
        $systemConfigService = $this->get(SystemConfigService::class);
        /* @var $salesChannelsRepository EntityRepository */
        $allSalesChannels = $this->getSalesChannels($context, [
            new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT),
        ]);
        $salesChannels = $errors = [];
        foreach ($allSalesChannels as $salesChannelEntity) {
            /* @var $salesChannelEntity SalesChannelEntity */
            $apiKey = $systemConfigService->get('RelevaRetargeting.config.relevanzApiKey', $salesChannelEntity->getId());
            if (!empty($apiKey)) {
                try {
                    $this->getDomainForSalesChannel($salesChannelEntity);//@throws Exception if sales channel doesnt have domain
                    $userId = $systemConfigService->get('RelevaRetargeting.config.relevanzUserId', $salesChannelEntity->getId());
                    if (empty($userId)) {
                        $this->verifyApiKey($apiKey, $salesChannelEntity);//throws Exception
                    }
                    $salesChannels[] = [
                        'salesChannel' => $salesChannelEntity->getName(),
                        'iframeUrl' => sprintf(RelevanzApi::RELEVANZ_STATS_FRAME.'%s', $apiKey),
                    ];
                } catch (RelevanzException $exception) {
                    $errors[] = [
                        'message' => vsprintf($exception->getMessage(), $exception->getSprintfArgs()),
                        'code' => $exception->getCode(),
                        "data" => [
                            'salesChannelName' => $salesChannelEntity->getName(),
                            'salesChannelId' => $salesChannelEntity->getId(),
                            'data' => $exception->getSprintfArgs(),
                        ],
                    ];
                } catch (\Exception $exception) {
                    $errors[] = [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                        "data" => [],
                    ];
                }
            }
        }
        if (count($salesChannels) === 0 && count($errors) === 0) {
            $errors[] = [
                "message" => "No sales-channels are configured for releva.nz plugin.",
                "code" => 1579084006,
                "data" => [],
            ];
            $salesChannels[] = [
                'salesChannel' => 'releva.nz Homepage',
                'iframeUrl' => 'https://releva.nz/',
            ];
        }
        return new JsonResponse([
            'errors' => $errors,
            'data' => $salesChannels,
        ]);
    }
    
    /**
     * @Route("/api/v{version}/releva/retargeting/getVerifyApiKey", name="api.action.releva.retargeting.getverifyapikey", methods={"POST"})
     */
    public function getVerifyApiKeyAction(Request $request, Context $context): JsonResponse
    {
        $salesChannels = $this->getSalesChannels($context, [
            new EqualsFilter('id', $request->get('config')['salesChannel'])
        ]);
        /* @var $salesChannelEntity SalesChannelEntity */
        $salesChannelEntity = $salesChannels->first();
        $data = ['userId' => null, ];
        $errors = [];
        try {
            $this->verifyApiKey($request->get('config')['apiKey'], $salesChannelEntity);
        } catch (RelevanzException $exception) {
            $errors[] = [
                'message' => vsprintf($exception->getMessage(), $exception->getSprintfArgs()),
                'code' => $exception->getCode(),
                "data" => [
                    'salesChannelName' => $salesChannelEntity->getName(),
                    'salesChannelId' => $salesChannelEntity->getId(),
                    'data' => $exception->getSprintfArgs(),
                ],
            ];
        } catch (\Exception $exception) {
            $errors[] = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                "data" => [],
            ];
        }
        return new JsonResponse([
            'errors' => $errors,
            'data' => $data,
        ]);
    }
    
    private function verifyApiKey (string $apiKey, SalesChannelEntity $salesChannelEntity): int {
        /* @var $systemConfigService SystemConfigService */
        $systemConfigService = $this->get(SystemConfigService::class);
        try {
            $userId = (int) RelevanzApi::verifyApiKey($apiKey, [
                'callback-url' => sprintf(
                    '%s%s',
                    $this->getDomainForSalesChannel($salesChannelEntity)->getUrl(),// throw Exception, no domain configured
                    $this->get(ShopInfo::class)->getUrlCallback()
            ),
            ])->getUserId();
            $systemConfigService->set('RelevaRetargeting.config.relevanzUserId', $userId, $salesChannelEntity->getId());
            return $userId;
        } catch (\Exception $exception) {
            $systemConfigService->set('RelevaRetargeting.config.relevanzUserId', null, $salesChannelEntity->getId());
            throw $exception;
        }
    }
    
    private function getDomainForSalesChannel(SalesChannelEntity $salesChannelEntity): SalesChannelDomainEntity {
        $domain = null;
        foreach ($salesChannelEntity->getDomains() as $domainEntity) {
            if (
                $domain === null // use any domain
                || in_array($domainEntity->getLanguageId(), [
                    $salesChannelEntity->getLanguageId(), // configured domain
                    Defaults::SALES_CHANNEL, // default domain
                ])) {
                $domain = $domainEntity;
            }
             if ($domainEntity->getLanguageId() === $salesChannelEntity->getLanguageId()) {
                 break;
             }
        }
        if ($domain === null) {
            throw new RelevanzException('Storefront doesn\'t have domain.', 1579849966);
        }
        return $domain;
    }
    
    private function getSalesChannels (Context $context, array $filters = []): EntitySearchResult {
        $salesChannelsRepository = $this->container->get('sales_channel.repository');
        RepositoryHelper::setAutoload($salesChannelsRepository, ['domains']);
        $criteria = new Criteria();
        foreach ($filters as $filter) {
            $criteria->addFilter($filter);
        }
        return $salesChannelsRepository->search($criteria, $context);
    }
    
}