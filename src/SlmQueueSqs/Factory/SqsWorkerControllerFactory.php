<?php

namespace SlmQueueSqs\Factory;

use SlmQueue\Queue\QueuePluginManager;
use SlmQueueSqs\Controller\SqsWorkerController;
use SlmQueueSqs\Worker\SqsWorker;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * WorkerFactory
 */
class SqsWorkerControllerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();

        $worker  = $parentLocator->get(SqsWorker::class);
        $manager = $parentLocator->get(QueuePluginManager::class);

        return new SqsWorkerController($worker, $manager);
    }
}
