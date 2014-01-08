<?php

namespace SlmQueueSqs\Factory;

use SlmQueueSqs\Queue\SqsQueue;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory that create a SQS queue for an Elastic Beanstalk worker environment
 */
class EBWorkerQueueFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator    = $serviceLocator->getServiceLocator();
        $sqsClient        = $parentLocator->get('Aws')->get('Sqs');
        $jobPluginManager = $parentLocator->get('SlmQueue\Job\JobPluginManager');

        $queueUrl = trim(file_get_contents('/var/app/sqs_worker'));

        return new SqsQueue($sqsClient, $queueUrl, $jobPluginManager);
    }
}
