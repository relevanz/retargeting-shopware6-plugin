<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Subscriber;

use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Releva\Retargeting\Base\RelevanzApi;

class StoreFrontSubscriber implements EventSubscriberInterface
{
    
    private $systemConfigService;
    
    public function __construct(SystemConfigService $systemConfigService) {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FooterPageletLoadedEvent::class => 'addRelevaUrls',
        ];
    }

    public function addRelevaUrls(FooterPageletLoadedEvent $event): void
    {
        $event->getPagelet()->addExtension('releva', new ArrayEntity([
            'tracking_active' => $this->systemConfigService->get('RelevaRetargeting.config.trackingActive', $event->getSalesChannelContext()->getSalesChannel()->getId()),
            'tracker_url' => RelevanzApi::RELEVANZ_TRACKER_URL,
            'conv_url' => RelevanzApi::RELEVANZ_CONV_URL,
            'user_id' => $this->systemConfigService->get('RelevaRetargeting.config.relevanzUserId', $event->getSalesChannelContext()->getSalesChannel()->getId()),
            'additional_html' => $this->systemConfigService->get('RelevaRetargeting.config.additionalHtml', $event->getSalesChannelContext()->getSalesChannel()->getId()),
        ]));
    }
    
}