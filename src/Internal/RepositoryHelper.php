<?php declare(strict_types=1);

namespace Releva\Retargeting\Shopware\Internal;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class RepositoryHelper {
    
    /**
     * manipulates given EntityRepository to allow autoload of related repositories during query
     * 
     * @param EntityRepository $repository
     * @param array $propertyNames
     * @return void
     */
    public static function setAutoload (EntityRepository $repository, array $propertyNames = []): void {
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

