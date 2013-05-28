<?php

namespace SlmQueueSqs\Factory;

use Aws\Common\Aws;
use SlmQueueSqs\Service\SqsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * SqsServiceFactory
 */
class SqsServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Aws\Sqs\SqsClient $sqsClient */
        $sqsClient = $serviceLocator->get('Aws')->get('Sqs');
        return new SqsService($sqsClient);
    }
}
