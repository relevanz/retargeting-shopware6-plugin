<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Controller;

use Releva\Retargeting\Base\RelevanzApi;
use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Shopware\Internal\MessagesBridge;
use Releva\Retargeting\Shopware\Internal\ShopInfo;
use Releva\Retargeting\Shopware\Internal\RepositoryHelper;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class ApiController extends AbstractController
{

    /**
     * @Route("/api/releva/retargeting/getInvolvedSalesChannelsToIframeUrls", name="api.action.releva.retargeting.getinvolvedsaleschannelstoiframeurls", methods={"POST"})
     */
    public function getInvolvedSalesChannelsToIframeUrlsAction(Context $context): JsonResponse
    {
        /* @var $systemConfigService SystemConfigService */
        $systemConfigService = $this->container->get(SystemConfigService::class);
        /* @var $salesChannelsRepository EntityRepository */
        $allSalesChannels = $this->container->get(RepositoryHelper::class)->getSalesChannels($context, ['domains', ]);
        $salesChannels = $notifications = [];
        foreach ($allSalesChannels as $salesChannelEntity) {
            /* @var $salesChannelEntity SalesChannelEntity */
            $apiKey = $systemConfigService->get('RelevaRetargeting.config.relevanzApiKey', $salesChannelEntity->getId());
            if (!empty($apiKey)) {
                try {
                    $this->getDomainForSalesChannel($salesChannelEntity);//@throws Exception if sales channel doesnt have domain
                    $userId = $systemConfigService->get('RelevaRetargeting.config.relevanzUserId', $salesChannelEntity->getId());
                    if (empty($userId)) {
                        $this->verifyApiKey($apiKey, $salesChannelEntity, true);//throws Exception
                    }
                    $salesChannels[] = [
                        'salesChannel' => $salesChannelEntity->getName(),
                        'iframeUrl' => sprintf(RelevanzApi::RELEVANZ_STATS_FRAME.'%s', $apiKey),
                    ];
                } catch (\Exception $exception) {
                    $this->container->get(MessagesBridge::class)->addException($exception, $salesChannelEntity, $notifications);
                }
            }
        }
        if (count($salesChannels) === 0 && count($notifications) === 0) {
            $this->container->get(MessagesBridge::class)->add('No sales-channels are configured for releva.nz plugin.', 1579084006, [], $notifications);
            $salesChannels[] = [
                'salesChannel' => 'releva.nz Homepage',
                'iframeUrl' => 'https://releva.nz/',
            ];
        }
        return new JsonResponse([
            'notifications' => $notifications,
            'data' => $salesChannels,
        ]);
    }

    /**
     * @Route("/api/releva/retargeting/getVerifyApiKey", name="api.action.releva.retargeting.getverifyapikey", methods={"POST"})
     */
    public function getVerifyApiKeyAction(Request $request, Context $context): JsonResponse
    {
        /* @var $salesChannelEntity SalesChannelEntity */
        $salesChannelEntity = $this->container->get(RepositoryHelper::class)->getSalesChannels($context, ['domains', ], [
            new EqualsFilter('id', $request->get('config')['salesChannel']),
        ])->first();
        $data = ['userId' => null, ];
        $notifications = [];
        try {
            $data['userId'] = $this->verifyApiKey($request->get('config')['apiKey'], $salesChannelEntity, array_key_exists('save', $request->get('config')) && $request->get('config')['save'] === true ? true : false);
        } catch (\Exception $exception) {
            $this->container->get(MessagesBridge::class)->addException($exception, $salesChannelEntity, $notifications);
        }
        return new JsonResponse([
            'notifications' => $notifications,
            'data' => $data,
        ]);
    }

    private function verifyApiKey (string $apiKey, SalesChannelEntity $salesChannelEntity, bool $save): int
    {
        /* @var $systemConfigService SystemConfigService */
        $systemConfigService = $this->container->get(SystemConfigService::class);
        try {
            $parameters =
                $save
                ? [
                    'callback-url' => sprintf(
                        '%s%s',
                        $this->getDomainForSalesChannel($salesChannelEntity)->getUrl(),// throw Exception, no domain configured
                        $this->container->get(ShopInfo::class)->getUrlCallback()
                    ),]
                : [
                ]
            ;
            $this->container->get(MessagesBridge::class)->add('VerifyApiKey-parameters.', 1586412248, $parameters);
            $userId = (int) RelevanzApi::verifyApiKey($apiKey, $parameters)->getUserId();
            if ($save) {
                $systemConfigService->set('RelevaRetargeting.config.relevanzUserId', $userId, $salesChannelEntity->getId());
            }
            return $userId;
        } catch (\Exception $exception) {
            if ($save) {
                $systemConfigService->set('RelevaRetargeting.config.relevanzUserId', null, $salesChannelEntity->getId());
            }
            throw $exception;
        }
    }

    private function getDomainForSalesChannel(SalesChannelEntity $salesChannelEntity): SalesChannelDomainEntity
    {
        $domain = null;
        foreach ($salesChannelEntity->getDomains() as $domainEntity) {
            if (
                $domain === null // use any domain
                || in_array($domainEntity->getLanguageId(), [
                    $salesChannelEntity->getLanguageId(), // configured domain
                    Defaults::SALES_CHANNEL, // system default domain
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

}
