<?php

namespace SlmQueueSqsTest\Worker;

use Exception;
use PHPUnit_Framework_TestCase as TestCase;
use SlmQueueSqs\Worker\Worker as SqsWorker;
use SlmQueueSqsTest\Asset;
use SlmQueueSqsTest\Util\ServiceManagerFactory;
use Zend\ServiceManager\ServiceManager;

class WorkerTest extends TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var \SlmQueueSqs\Queue\SqsQueue
     */
    protected $queueMock;

    /**
     * @var SqsWorker
     */
    protected $worker;


    public function setUp()
    {
        parent::setUp();
        $this->serviceManager = ServiceManagerFactory::getServiceManager();

        $this->queueMock  = $this->getMock('SlmQueueSqs\Queue\SqsQueueInterface');
        $queueManagerMock = $this->getMock('SlmQueue\Queue\QueuePluginManager');
        $workerOptions    = $this->serviceManager->get('SlmQueue\Options\WorkerOptions');

        $this->worker = new SqsWorker($queueManagerMock, $workerOptions);
    }

    public function testAssertJobIsDeletedIfNoExceptionIsThrown()
    {
        $job = new Asset\SimpleJob();

        $this->queueMock->expects($this->once())
            ->method('delete')
            ->with($job)
            ->will($this->returnCallback(function() use ($job) {
                $job->setContent('deleted');
            })
        );

        $this->worker->processJob($job, $this->queueMock);

        $this->assertEquals('deleted', $job->getContent());
    }
}
