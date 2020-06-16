<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Internal;

use Releva\Retargeting\Base\Exception\RelevanzException;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class MessagesBridge
{
    
    private $container;
    
    private $logger;
    
    const NOTIFICATION_SUCCESS = 'success';
    const NOTIFICATION_INFO = 'info';
    const NOTIFICATION_WARNING = 'warning';
    const NOTIFICATION_ERROR = 'error';
    
    const LOGGER_EMERGENCY = 'emergency';
    const LOGGER_ALERT = 'alert';
    const LOGGER_CRITICAL = 'critical';
    const LOGGER_ERROR = 'error';
    const LOGGER_WARNING = 'warning';
    const LOGGER_NOTICE = 'notice';
    const LOGGER_INFO = 'info';
    const LOGGER_DEBUG = 'debug';
    
    private static $logLevelMatching = [
        '*' =>        ['logger-type' => self::LOGGER_NOTICE,  'notification-type' => self::NOTIFICATION_WARNING, ],//Miscellaneous messages, not defined in/from releva.nz plugin
        //administration messages
        1579084006 => ['logger-type' => self::LOGGER_NOTICE,  'notification-type' => self::NOTIFICATION_WARNING, ],//No sales-channels are configured for releva.nz plugin.
        1579849966 => ['logger-type' => self::LOGGER_NOTICE,  'notification-type' => self::NOTIFICATION_WARNING, ],//Sales-channel doesn't have domain.
        1553935786 => ['logger-type' => self::LOGGER_WARNING, 'notification-type' => self::NOTIFICATION_WARNING, ],//The API key cannot be verified.
        1553935569 => ['logger-type' => self::LOGGER_WARNING, 'notification-type' => self::NOTIFICATION_WARNING, ],//The API key cannot be verified.
        1553935480 => ['logger-type' => self::LOGGER_ERROR,   'notification-type' => self::NOTIFICATION_ERROR,   ],//Unable to connect to releva.nz API-Server.
        1586412248 => ['logger-type' => self::LOGGER_DEBUG,   'notification-type' => null,                       ],//Debug for checking credentials-url
        //frontend messages
        1585739838 => ['logger-type' => self::LOGGER_DEBUG,   'notification-type' => null,                       ],//Tracking is not active.
        1585739840 => ['logger-type' => self::LOGGER_DEBUG,   'notification-type' => null,                       ],//Auth Parameter invalid.
    ];

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    public function addException(\Exception $exception, SalesChannelEntity $salesChannelEntity, array &$notifications = []):self
    {
        $data = [
            'salesChannelName' => $salesChannelEntity->getName(),
            'salesChannelId' => $salesChannelEntity->getId(),
        ];
        if ($exception instanceof RelevanzException) {
            $message = vsprintf($exception->getMessage(), $exception->getSprintfArgs());
            $data['data'] = $exception->getSprintfArgs();
        } else {// \Exception
            $message = $exception->getMessage();
        }
        $this->add($message, $exception->getCode(), $data, $notifications);
        return $this;
    }
    
    public function add (string $message, int $code, array $data, array &$notifications = []):self
    {
        $logLevelConfig = (array_key_exists($code, static::$logLevelMatching) ? static::$logLevelMatching[$code] : static::$logLevelMatching['*']);
        $enviroment = $this->container->getParameter('kernel.environment');
        if ($logLevelConfig['logger-type'] !== 'debug' || $enviroment === 'dev') {
            if ($logLevelConfig['notification-type'] !== null) {
                $notifications[] = [
                    'variant' => $logLevelConfig['notification-type'],
                    'message' => $message,
                    'code' => $code,
                    "data" => $data,
                ];
            }
            if ($this->logger !== null && $logLevelConfig['logger-type'] !== null) {
                $this->logger->{$logLevelConfig['logger-type']}($message, ['environment' => $enviroment, 'code' => $code, 'data' => $data]);
            }
        }
        return $this;
    }
    
}