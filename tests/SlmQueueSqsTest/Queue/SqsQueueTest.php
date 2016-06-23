<?php

namespace SlmQueueSqsTest\Queue;

use PHPUnit_Framework_TestCase as TestCase;
use SlmQueueSqs\Options\SqsQueueOptions;
use SlmQueueSqs\Queue\SqsQueue;
use SlmQueueSqsTest\Asset;

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
            array('getQueueUrl', 'sendMessage', 'sendMessageBatch', 'deleteMessageBatch', 'receiveMessage'),
            array(),
            '',
            false
        );

        $this->jobPluginManager = $this->getMockBuilder('SlmQueue\Job\JobPluginManager')
            ->disableOriginalConstructor()->getMock();

        $this->sqsClient->expects($this->once())
                        ->method('getQueueUrl')
                        ->with(array('QueueName' => 'newsletter'))
                        ->will($this->returnValue(array('QueueUrl' => 'https://sqs.endpoint.com')));

        $options = new SqsQueueOptions();

        $this->sqsQueue = new SqsQueue($this->sqsClient, $options, 'newsletter', $this->jobPluginManager);
    }

    public function testReuseSqsUrlFromOptions()
    {
        $sqsClient        = $this->getMock('Aws\Sqs\SqsClient', array('getQueueUrl'), array(), '', false);
        $jobPluginManager = $this->getMockBuilder('SlmQueue\Job\JobPluginManager')
            ->disableOriginalConstructor()->getMock();

        $this->sqsClient->expects($this->never())->method('getQueueUrl');

        $options = new SqsQueueOptions(array('queue_url' => 'https://sqs.endpoint.com'));

        $sqsQueue = new SqsQueue($sqsClient, $options, 'newsletter', $jobPluginManager);
    }

    public function testAssertNullParametersGetStripped()
    {
        $job = new Asset\SimpleJob();

        $this->sqsClient->expects($this->once())
                        ->method('sendMessage')
                        ->with(array(
                            'QueueUrl'    => 'https://sqs.endpoint.com',
                            'MessageBody' => $this->sqsQueue->serializeJob($job)
                        ));

        $this->sqsQueue->push($job, array(
            'delay_seconds' => null
        ));
    }

    public function testSetMetadataWhenJobIsPushed()
    {
        $job = new Asset\SimpleJob(array('foo' => 'bar'));

        $result = array(
            'MessageId'        => 1,
            'MD5OfMessageBody' => md5('baz')
        );

        $this->sqsClient->expects($this->once())
                        ->method('sendMessage')
                        ->with(array(
                            'QueueUrl'    => 'https://sqs.endpoint.com',
                            'MessageBody' => $this->sqsQueue->serializeJob($job)
                        ))
                        ->will($this->returnValue($result));

        $this->sqsQueue->push($job);

        $this->assertEquals(1, $job->getId());
        $this->assertEquals(1, $job->getMetadata('__id__'));
        $this->assertEquals(md5('baz'), $job->getMetadata('md5'));
    }

    public function testSetMetadataWhenMultipleJobsArePushed()
    {
        $jobs = array(
            new Asset\SimpleJob(array('foo' => 'bar')),
            new Asset\SimpleJob(array('bar' => 'baz'))
        );

        $result = array(
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
        );

        $this->sqsClient->expects($this->once())
            ->method('sendMessageBatch')
            ->with(array(
            'QueueUrl' => 'https://sqs.endpoint.com',
            'Entries'  => array(
                array(
                    'Id'          => 0,
                    'MessageBody' => $this->sqsQueue->serializeJob($jobs[0])
                ),
                array(
                    'Id'          => 1,
                    'MessageBody' => $this->sqsQueue->serializeJob($jobs[1])
                )
            )
        ))
            ->will($this->returnValue($result));

        $this->sqsQueue->batchPush($jobs);

        $this->assertCount(2, $jobs);

        $this->assertEquals(1, $jobs[0]->getId());
        $this->assertEquals(1, $jobs[0]->getMetadata('__id__'));
        $this->assertEquals(md5('bar'), $jobs[0]->getMetadata('md5'));

        $this->assertEquals(2, $jobs[1]->getId());
        $this->assertEquals(2, $jobs[1]->getMetadata('__id__'));
        $this->assertEquals(md5('baz'), $jobs[1]->getMetadata('md5'));
    }

    public function testCanPushSpliceJobsIfLimitIsExceeded()
    {
        $jobs = array(
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob()
        );

        $firstSuccessful = array();

        for ($i = 0 ; $i != 10 ; ++$i) {
            $firstSuccessful[] = array(
                'Id'               => $i,
                'MessageId'        => $i + 1,
                'MD5OfMessageBody' => md5('foo')
            );
        }

        $firstResult = array(
            'Successful' => $firstSuccessful
        );

        $secondResult = array(
            'Successful' => array(
                0 => array(
                    'Id'        => 0,
                    'MessageId' => 1,
                    'MD5OfMessageBody' => md5 ('fpp')
                )
            )
        );

        $self = $this;

        $this->sqsClient->expects($this->at(0))
                        ->method('sendMessageBatch')
                        ->with($this->callback(function($parameters) use ($self) {
                $self->assertCount(10, $parameters['Entries']);
                return true;
            }))
                        ->will($this->returnValue($firstResult));

        $this->sqsClient->expects($this->at(1))
                        ->method('sendMessageBatch')
                        ->with($this->callback(function($parameters) use ($self) {
                $self->assertCount(1, $parameters['Entries']);
                return true;
            }))
                        ->will($this->returnValue($secondResult));

        $this->sqsQueue->batchPush($jobs);

        $this->assertCount(11, $jobs);
    }

    public function testNeverBatchPushIfNoJobInArray()
    {
        $this->sqsClient->expects($this->never())
                       ->method('sendMessageBatch');

        $this->sqsQueue->batchPush(array());
    }

    public function testCanDeleteSpliceJobsIfLimitIsExceeded()
    {
        $jobs = array(
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob(),
            new Asset\SimpleJob()
        );

        $firstSuccessful = array();

        for ($i = 0 ; $i != 10 ; ++$i) {
            $firstSuccessful[] = array(
                'Id'               => $i,
                'MessageId'        => $i + 1,
                'MD5OfMessageBody' => md5('foo')
            );
        }

        $firstResult = array(
            'Successful' => $firstSuccessful
        );

        $secondResult = array(
            'Successful' => array(
                0 => array(
                    'Id'        => 0,
                    'MessageId' => 1,
                    'MD5OfMessageBody' => md5 ('fpp')
                )
            )
        );

        $self = $this;

        $this->sqsClient->expects($this->at(0))
            ->method('deleteMessageBatch')
            ->with($this->callback(function($parameters) use ($self) {
                        $self->assertCount(10, $parameters['Entries']);
                        return true;
                    }))
            ->will($this->returnValue($firstResult));

        $this->sqsClient->expects($this->at(1))
            ->method('deleteMessageBatch')
            ->with($this->callback(function($parameters) use ($self) {
                        $self->assertCount(1, $parameters['Entries']);
                        return true;
                    }))
            ->will($this->returnValue($secondResult));

        $this->sqsQueue->batchDelete($jobs);

        $this->assertCount(11, $jobs);
    }

    public function testNeverBatchDeleteIfNoJobInArray()
    {
        $this->sqsClient->expects($this->never())
                        ->method('deleteMessageBatch');

        $this->sqsQueue->batchDelete(array());
    }

    public function testMetadataIsPopped()
    {
        $this->sqsClient->expects($this->once())
            ->method('receiveMessage')
            ->will($this->returnValue(array(
                'Messages' => array(
                    array(
                        'Body' => json_encode(array(
                            'content'  => serialize('aa'),
                            'metadata' => array('__name__' => 'MyClass', 'foo' => 'bar')
                        )),
                        'MessageId'     => 'id_123',
                        'ReceiptHandle' => 'receipt_123',
                        'MD5OfBody'     => 'funny'
                    )
                )
            )));

        $this->jobPluginManager->expects($this->once())
                               ->method('get')
                               ->with('MyClass')
                               ->will($this->returnValue(new Asset\SimpleJob()));

        $job = $this->sqsQueue->pop();

        $this->assertInstanceOf('SlmQueueSqsTest\Asset\SimpleJob', $job);
        $this->assertEquals('aa', $job->getContent());
        $this->assertEquals(array(
            '__id__'        => 'id_123',
            '__name__'      => 'MyClass',
            'receiptHandle' => 'receipt_123',
            'md5'           => 'funny',
            'foo'           => 'bar'
        ), $job->getMetadata());
    }
}
