<?php

namespace SlmQueueSqs\Factory;

use SlmQueueSqs\Queue\SqsQueue;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * SqsQueueFactory
 */
class SqsQueueFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $name = '', $requestedName = '')
    {
        $parentLocator    = $serviceLocator->getServiceLocator();
        $sqsService       = $parentLocator->get('SlmQueueSqs\Service\SqsService');
        $jobPluginManager = $parentLocator->get('SlmQueue\Job\JobPluginManager');

        return new SqsQueue($sqsService->getClient(), $requestedName, $jobPluginManager);
    }
}
