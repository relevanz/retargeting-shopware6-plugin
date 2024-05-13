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
    
    public function getCookieGroups(): array
    {
        $originalCookieGroups = $this->originalCookieProvider->getCookieGroups();
        if (
            $this->systemConfigService->get('RelevaRetargeting.config.trackingActive', $this->requestStack->getCurrentRequest()->get('sw-sales-channel-id'))
            && $this->systemConfigService->get('RelevaRetargeting.config.relevanzUserId', $this->requestStack->getCurrentRequest()->get('sw-sales-channel-id'))
        ) {
            $foundIndex = null;
            foreach ($originalCookieGroups as $originalCookieGroupIndex => $originalCookieGroup) {
                if ($originalCookieGroup['snippet_name'] === 'cookie.groupMarketing') {
                    $foundIndex = $originalCookieGroupIndex;
                    break;
                }
            }
            if ($foundIndex === null) {
                $foundIndex = count($originalCookieGroups);
                $originalCookieGroups[] = [
                    'snippet_name' => 'cookie.groupMarketing',
                    'snippet_description' => 'cookie.groupMarketingDescription',
                    'entries' => [],
                ];
            }
            $originalCookieGroups[$foundIndex]['entries'][] = [
                'snippet_name' => 'cookie.relevanzRetargeting',
                'snippet_description' => 'cookie.relevanzRetargetingDescription',
                'cookie' => 'relevanzRetargeting',
                'value'=> 'allow',
                'expiration' => '30',
            ];
        }
        return $originalCookieGroups;
    }
    
}