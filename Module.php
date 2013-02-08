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
        return "\n----------------------------------------------------------------------\n" .
               "SlmQueueSqs | Amazon SQS Zend Framework 2 module\n" .
               "----------------------------------------------------------------------\n";
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'queue sqs <queueName> --start' => 'Process the jobs',
            array('<queueName>', 'Queue\'s name to process')
        );
    }
}
