<?php

namespace SlmQueueSqsTest\Options;

use PHPUnit_Framework_TestCase as TestCase;
use SlmQueueSqsTest\Util\ServiceManagerFactory;
use Zend\ServiceManager\ServiceManager;

class SqsOptionsTest extends TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function setUp()
    {
        parent::setUp();
        $this->serviceManager = ServiceManagerFactory::getServiceManager();
    }

    public function testCreateBeanstalkdOptions()
    {
        /** @var $sqsOptions \SlmQueueSqs\Options\SqsOptions */
        $sqsOptions = $this->serviceManager->get('SlmQueueSqs\Options\SqsOptions');

        $this->assertInstanceOf('SlmQueueSqs\Options\SqsOptions', $sqsOptions);
    }
}
