<?php

namespace SlmQueueSqs\Factory;

use Interop\Container\ContainerInterface;
use SlmQueue\Queue\QueuePluginManager;
use SlmQueueSqs\Controller\SqsWorkerController;
use SlmQueueSqs\Worker\SqsWorker;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * WorkerFactory
 */
class SqsWorkerControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return SqsWorkerController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $worker  = $container->get(SqsWorker::class);
        $manager = $container->get(QueuePluginManager::class);

        return new SqsWorkerController($worker, $manager);
    }

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();

        return $this($parentLocator, SqsWorkerController::class);
    }
}
