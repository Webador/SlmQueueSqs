<?php

namespace SlmQueueSqsTest\Service;

use PHPUnit_Framework_TestCase as TestCase;
use Guzzle\Service\Resource\Model as ResourceModel;
use SlmQueueSqsTest\Asset;
use Zend\ServiceManager\ServiceManager;

class SqsServiceTest extends TestCase
{
    /**
     * @var \Aws\Sqs\SqsClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sqsClient;


    public function setUp()
    {
        parent::setUp();
        $this->sqsClient = $this->getMock('Aws\Sqs\SqsClient', array('listQueues'), array(), '', false);
    }

    public function testServiceReturnValidClient()
    {
        $sqsService = new \SlmQueueSqs\Service\SqsService($this->sqsClient);
        $this->assertInstanceOf('Aws\Sqs\SqsClient', $sqsService->getClient());
    }

    public function testCanListQueues()
    {
        $model = new ResourceModel(array(
            'QueueUrls' => array('http://endpoint.com/queue')
        ));

        $this->sqsClient->expects($this->once())
            ->method('listQueues')
            ->will($this->returnValue($model));

        $sqsService = new \SlmQueueSqs\Service\SqsService($this->sqsClient);
        $queues     = $sqsService->getQueueUrls();

        $this->assertEquals(array('http://endpoint.com/queue'), $queues);
    }
}
