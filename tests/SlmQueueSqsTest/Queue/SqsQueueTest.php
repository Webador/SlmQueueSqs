<?php

namespace SlmQueueSqsTest\Queue;

use PHPUnit_Framework_TestCase as TestCase;
use Guzzle\Service\Resource\Model as ResourceModel;
use SlmQueueSqs\Queue\SqsQueue;
use SlmQueueSqsTest\Asset;
use Zend\ServiceManager\ServiceManager;

class SqsQueueTest extends TestCase
{
    /**
     * @var \Aws\Sqs\SqsClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sqsClient;

    /**
     * @var \SlmQueue\Job\JobPluginManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jobPluginManager;

    /**
     * @var \SlmQueueSqs\Queue\SqsQueueInterface
     */
    protected $sqsQueue;

    public function setUp()
    {
        $this->sqsClient = $this->getMock(
            'Aws\Sqs\SqsClient',
            array('getQueueUrl', 'sendMessage', 'sendMessageBatch'),
            array(),
            '',
            false
        );

        $this->jobPluginManager = $this->getMock('SlmQueue\Job\JobPluginManager');

        $this->sqsClient->expects($this->once())
                        ->method('getQueueUrl')
                        ->with(array('QueueName' => 'newsletter'))
                        ->will($this->returnValue(array('QueueUrl' => 'https://sqs.endpoint.com')));

        $this->sqsQueue = new SqsQueue($this->sqsClient, 'newsletter', $this->jobPluginManager);
    }

    public function testReuseSqsUrl()
    {
        $sqsClient        = $this->getMock('Aws\Sqs\SqsClient', array('getQueueUrl'), array(), '', false);
        $jobPluginManager = $this->getMock('SlmQueue\Job\JobPluginManager');

        $this->sqsClient->expects($this->never())->method('getQueueUrl');

        $sqsQueue = new SqsQueue($sqsClient, 'https://sqs.foo.com', $jobPluginManager);
    }

    public function testAssertNullParametersGetStripped()
    {
        $messageBody = serialize(array('foo' => 'bar'));

        $job = $this->getMock('SlmQueue\Job\JobInterface');
        $job->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue($messageBody));

        $this->sqsClient->expects($this->once())
                        ->method('sendMessage')
                        ->with(array(
                            'QueueUrl'    => 'https://sqs.endpoint.com',
                            'MessageBody' => $messageBody
                        ));

        $this->sqsQueue->push($job, array(
            'delay_seconds' => null
        ));
    }

    public function testSetMetadataWhenJobIsPushed()
    {
        $job = new Asset\SimpleJob(array('foo' => 'bar'));

        $result = new ResourceModel(array(
            'MessageId'        => 1,
            'MD5OfMessageBody' => md5('baz')
        ));

        $this->sqsClient->expects($this->once())
                        ->method('sendMessage')
                        ->with(array(
                            'QueueUrl' => 'https://sqs.endpoint.com',
                            'MessageBody' => $job->jsonSerialize()
                        ))
                        ->will($this->returnValue($result));

        $this->sqsQueue->push($job);

        $this->assertEquals(1, $job->getId());
        $this->assertEquals(1, $job->getMetadata('id'));
        $this->assertEquals(md5('baz'), $job->getMetadata('md5'));
    }

    public function testSetMetadataWhenMultipleJobsArePushed()
    {
        $jobs = array(
            new Asset\SimpleJob(array('foo' => 'bar')),
            new Asset\SimpleJob(array('bar' => 'baz'))
        );

        $result = new ResourceModel(array(
            'Successful' => array(
                0 => array(
                    'Id'        => 0,
                    'MessageId' => 1,
                    'MD5OfMessageBody' => md5 ('bar')
                ),

                1 => array(
                    'Id'        => 1,
                    'MessageId' => 2,
                    'MD5OfMessageBody' => md5('baz')
                )
            )
        ));

        $this->sqsClient->expects($this->once())
            ->method('sendMessageBatch')
            ->with(array(
            'QueueUrl' => 'https://sqs.endpoint.com',
            'Entries'  => array(
                array(
                    'Id'          => 0,
                    'MessageBody' => $jobs[0]->jsonSerialize()
                ),
                array(
                    'Id'          => 1,
                    'MessageBody' => $jobs[1]->jsonSerialize()
                )
            )
        ))
            ->will($this->returnValue($result));

        $this->sqsQueue->batchPush($jobs);

        $this->assertCount(2, $jobs);

        $this->assertEquals(1, $jobs[0]->getId());
        $this->assertEquals(1, $jobs[0]->getMetadata('id'));
        $this->assertEquals(md5('bar'), $jobs[0]->getMetadata('md5'));

        $this->assertEquals(2, $jobs[1]->getId());
        $this->assertEquals(2, $jobs[1]->getMetadata('id'));
        $this->assertEquals(md5('baz'), $jobs[1]->getMetadata('md5'));
    }
}
