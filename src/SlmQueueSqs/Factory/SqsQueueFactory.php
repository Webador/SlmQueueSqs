<?php

namespace SlmQueueSqs\Factory;

use Aws\Sdk as Aws;
use Interop\Container\ContainerInterface;
use SlmQueue\Job\JobPluginManager;
use SlmQueueSqs\Options\SqsQueueOptions;
use SlmQueueSqs\Queue\SqsQueue;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * SqsQueueFactory
 */
class SqsQueueFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return SqsQueue
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $sqsClient        = $container->get(Aws::class)->createSqs();
        $jobPluginManager = $container->get(JobPluginManager::class);

        // Let's see if we have options for this specific queue
        $config = $container->get('Config');
        $config = $config['slm_queue']['queues'];

        $options = new SqsQueueOptions(isset($config[$requestedName]) ? $config[$requestedName] : []);


        return new SqsQueue($sqsClient, $options, $requestedName, $jobPluginManager);
    }

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $name = '', $requestedName = '')
    {
        $parentLocator = $serviceLocator->getServiceLocator();

        return $this($parentLocator, SqsQueue::class);
    }
}
