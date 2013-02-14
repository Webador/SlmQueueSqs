<?php

namespace SlmQueueSqs\Factory;

use SlmQueueSqs\Options\SqsOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * SqsOptionsFactory
 */
class SqsOptionsFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        return new SqsOptions($config['slm_queue']['sqs']);
    }
}
