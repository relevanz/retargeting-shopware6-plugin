<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Internal;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class RepositoryHelper {
    
    public static function setAutoload (EntityRepository $repository, $propertyNames = []): void {
        $definitionFields = $repository->getDefinition()->getFields()->getElements();
        foreach ($propertyNames as $propertyName) {
            if (array_key_exists($propertyName, $definitionFields)) {
                $definitionField = $definitionFields[$propertyName];
                $reflectionClass = new \ReflectionClass($definitionField);
                if ($reflectionClass->hasProperty('autoload')) {
                    $reflectionProperty = $reflectionClass->getProperty('autoload');
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($definitionField, true);
                    $reflectionProperty->setAccessible(false);
                }
            }
        }
    }
    
}

