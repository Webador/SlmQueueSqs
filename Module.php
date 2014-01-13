<?php

namespace SlmQueueSqs;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Loader;
use Zend\ModuleManager\Feature;

/**
 * SlmQueueSqs
 */
class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\ConsoleBannerProviderInterface,
    Feature\ConsoleUsageProviderInterface,
    Feature\DependencyIndicatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            Loader\AutoloaderFactory::STANDARD_AUTOLOADER => array(
                Loader\StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleBanner(AdapterInterface $console)
    {
        return 'SlmQueueSqs';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'queue sqs <queue> [--visibilityTimeout=] [--waitTime=]' => 'Process the jobs',

            array('<queue>', 'Queue\'s name to process'),
            array('--visibilityTimeout=', 'Duration (in seconds) that the received messages are hidden from subsequent retrieve requests after being retrieved by a pop request'),
            array('--waitTime=', 'Wait time (in seconds) for which the call will wait for a job to arrive in the queue before returning')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleDependencies()
    {
        return array('SlmQueue');
    }
}
