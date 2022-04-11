<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Internal;

use Releva\Retargeting\Base\AbstractShopInfo;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

class ShopInfo extends AbstractShopInfo
{

    private static $container;

    public function __construct(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function getShopSystem(): string
    {
        return 'Shopware';
    }

    public static function getShopVersion() :? string
    {
        return self::$container->hasParameter('kernel.shopware_version') ? self::$container->getParameter('kernel.shopware_version') : null;
    }

    public static function getPluginVersion() :? string
    {
        foreach(self::$container->hasParameter('kernel.plugin_infos') ? self::$container->getParameter('kernel.plugin_infos') : [] as $pluginInfo) {
            if ($pluginInfo['name'] === 'RelevaRetargeting') {
                return $pluginInfo['version'];
            }
        }
        return null;
    }

    public static function getDbVersion(): array
    {
        $versionData =
            self::$container === null
            ? []
            : self::$container->get(Connection::class)->query('SELECT @@version AS `version`, @@version_comment AS `server`;')->fetch()
        ;
        return empty($versionData) ? parent::getDbVersion() : $versionData;
    }

    public static function getServerEnvironment(): array
    {
        return [
            'server-software' => Request::createFromGlobals()->server->get('SERVER_SOFTWARE'),
            'php' => static::getPhpVersion(),
            'db' => static::getDbVersion(),
        ];
    }

    public static function getUrlCallback():? string
    {
        return
            self::$container === null
            ? null
            : self::$container->get('router')->getRouteCollection()->get('frontend.releva.retargeting.callback')->getPath()
        ;
    }

    public static function getUrlProductExport():? string
    {
        return
            self::$container === null
            ? null
            : self::$container->get('router')->getRouteCollection()->get('frontend.releva.retargeting.products')->getPath()
        ;
    }

}