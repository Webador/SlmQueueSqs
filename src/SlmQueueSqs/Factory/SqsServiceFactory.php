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
        /** @var $sqsOptions \SlmQueueSqs\Options\SqsOptions */
        $sqsOptions = $serviceLocator->get('SlmQueueSqs\Options\SqsOptions');
        $sqsClient  = Aws::factory($sqsOptions->getConfigFile())->get('sqs');

        return new SqsService($sqsClient);
    }
}
