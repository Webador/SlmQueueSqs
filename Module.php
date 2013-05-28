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
    Feature\ConsoleUsageProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
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
        return 'SlmQueueSqs ' . Version::VERSION;
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'queue sqs <queue> [--maxJobs=] [--visibilityTimeout=] [--waitTime=]' => 'Process the jobs',

            array('<queue>', 'Queue\'s name to process'),
            array('--maxJobs=', 'Maximum number of jobs that can be returned from a pop call'),
            array('--visibilityTimeout=', 'Duration (in seconds) that the received messages are hidden from subsequent retrieve requests after being retrieved by a pop request'),
            array('--waitTime=', 'Wait time (in seconds) for which the call will wait for a job to arrive in the queue before returning')
        );
    }

    /**
     * This ModuleManager feature was introduced in ZF 2.1 to check if all the dependencies needed by a module
     * were correctly loaded. However, as we want to keep backward-compatibility with ZF 2.0, please DO NOT
     * explicitely implement Zend\ModuleManager\Feature\DependencyIndicatorInterface. Just write this method and
     * the module manager will automatically call it
     *
     * @return array
     */
    public function getModuleDependencies()
    {
        return array('SlmQueue');
    }
}
