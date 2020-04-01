<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Internal;

use Releva\Retargeting\Base\Exception\RelevanzException;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class LoggerBridge {
    
    private $container;
    
    private $logger;
    
    public function __construct(ContainerInterface $container, LoggerInterface $logger) {
        $this->container = $container;
        $this->logger = $logger;
    }
    
    private static $logLevelMatching = [
        '*' => ['logger-type' => 'notice', 'notification-type' => 'warning', ],
        1579084006 => ['logger-type' => 'notice', 'notification-type' => 'warning', ],
        1579849966 => ['logger-type' => 'notice', 'notification-type' => 'warning', ],
        1553935786 => ['logger-type' => 'warning', 'notification-type' => 'warning', ],
        1553935786 => ['logger-type' => 'warning', 'notification-type' => 'warning', ],
        1553935480 => ['logger-type' => 'error', 'notification-type' => 'error', ],
        1585739838 => ['logger-type' => 'debug', 'notification-type' => 'info', ],
        1585739840 => ['logger-type' => 'debug', 'notification-type' => 'info', ],
    ];


    public function addException(\Exception $exception, SalesChannelEntity $salesChannelEntity, array &$notifications = []):self {
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
    
    public function add (string $message, int $code, array $data, array &$notifications = []):self {
        $logLevelConfig = (array_key_exists($code, self::$logLevelMatching) ? self::$logLevelMatching[$code] : self::$logLevelMatching['*']);
        $enviroment = $this->container->getParameter('kernel.environment');
        if ($logLevelConfig['logger-type'] !== 'debug' || $enviroment === 'dev') {
            $notifications[] = [
                'variant' => $logLevelConfig['notification-type'],
                'message' => $message,
                'code' => $code,
                "data" => $data,
            ];
            if ($this->logger !== null) {
                $this->logger->{'add'.$logLevelConfig['logger-type']}($message, ['environment' => $enviroment, 'code' => $code, 'data' => $data]);
            }
        }
        return $this;
    }
    
}