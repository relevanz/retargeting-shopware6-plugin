<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Framework\Cookie;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomCookieProvider implements CookieProviderInterface
{

    private $originalCookieProvider;
    
    private $systemConfigService;
    
    private $requestStack;

    public function __construct(CookieProviderInterface $originalCookieProvider, SystemConfigService $systemConfigService, RequestStack $requestStack)
    {
        $this->originalCookieProvider = $originalCookieProvider;
        $this->systemConfigService = $systemConfigService;
        $this->requestStack = $requestStack;
    }
    
    /**
     * @todo check if plugin is configured
     */
    public function getCookieGroups(): array
    {
        return array_merge(
            $this->originalCookieProvider->getCookieGroups(),
            (
                $this->systemConfigService->get('RelevaRetargeting.config.trackingActive', $this->requestStack->getCurrentRequest()->get('sw-sales-channel-id'))
                && $this->systemConfigService->get('RelevaRetargeting.config.relevanzUserId', $this->requestStack->getCurrentRequest()->get('sw-sales-channel-id'))
                ? [
                    [
                        'snippet_name' => 'cookie.groupMarketing',
                        'snippet_description' => 'cookie.groupMarketingDescription',
                        'entries' => [
                            [
                                'snippet_name' => 'cookie.relevanzRetargeting',
                                'snippet_description' => 'cookie.relevanzRetargetingDescription',
                                'cookie' => 'relevanzRetargeting',
                                'value'=> 'allow',
                                'expiration' => '30'
                            ],
                        ],
                    ]
                ]
                : []
            )
        );
    }
    
}