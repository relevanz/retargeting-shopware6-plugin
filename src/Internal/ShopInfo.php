<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Internal;
use Releva\Retargeting\Base\AbstractShopInfo;
use Psr\Container\ContainerInterface;
use PackageVersions\Versions;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

class ShopInfo extends AbstractShopInfo {
    
    private static $container;
    
    public function __construct(ContainerInterface $container) {
        self::$container = $container;
    }

    public static function getShopSystem(): string {
        return 'Shopware';
    }
    
    public static function getShopVersion(): string {
        $versions = Versions::VERSIONS;
        if (isset($versions['shopware/core'])) {
            $shopwareVersion = Versions::getVersion('shopware/core');
        } else {
            $shopwareVersion = Versions::getVersion('shopware/platform');
        }
        return $shopwareVersion;
    }
    public static function getPluginVersion(): string {
        return Versions::getVersion('releva/relevanz-shopware-plugin');
    }
    
    public static function getDbVersion(): array {;
        $versionData = self::$container === null ? [] : self::$container->get(Connection::class)->query('SELECT @@version AS `version`, @@version_comment AS `server`;')->fetch();
        return empty($versionData) ? parent::getDbVersion() : $versionData;
    }
    
    public static function getServerEnvironment(): array {
        return [
            'server-software' => Request::createFromGlobals()->server->get('SERVER_SOFTWARE'),
            'php' => static::getPhpVersion(),
            'db' => static::getDbVersion(),
        ];
    }
    
    public static function getUrlCallback() {
        return self::$container === null ? null : self::$container->get('router')->getRouteCollection()->get('frontend.releva.retargeting.callback')->getPath().'?auth=:auth';
    }

    /**
     * @return string
     */
    public static function getUrlProductExport() {
        return self::$container === null ? null : self::$container->get('router')->getRouteCollection()->get('frontend.releva.retargeting.products')->getPath().'?auth=:auth';
    }
}