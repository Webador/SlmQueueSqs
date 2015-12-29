<?php

namespace SlmQueueSqsTest\Worker;

use Aws\Sqs\Exception\SqsException;
use PHPUnit_Framework_TestCase as TestCase;
use SlmQueue\Worker\WorkerEvent;
use SlmQueueSqs\Worker\SqsWorker;

class SqsWorkerTest extends TestCase
{
    /**
     * @var SqsWorker
     */
    protected $worker;

    public function setUp()
    {
        $this->worker = new SqsWorker($this->getMock('Zend\EventManager\EventManagerInterface'));
    }

    public function testReturnsUnknownIfNotASqsQueue()
    {
        $queue = $this->getMock('SlmQueue\Queue\QueueInterface');
        $job   = $this->getMock('SlmQueue\Job\JobInterface');

        $this->assertEquals(WorkerEvent::JOB_STATUS_UNKNOWN, $this->worker->processJob($job, $queue));
    }

    public function testDeleteJobOnSuccess()
    {
        $queue = $this->getMock('SlmQueueSqs\Queue\SqsQueueInterface');
        $job   = $this->getMock('SlmQueue\Job\JobInterface');

        $job->expects($this->once())->method('execute');
        $queue->expects($this->once())->method('delete')->with($job);

        $status = $this->worker->processJob($job, $queue);

        $this->assertEquals(WorkerEvent::JOB_STATUS_SUCCESS, $status);
    }

    public function testDoNotDeleteJobOnFailure()
    {
        $queue = $this->getMock('SlmQueueSqs\Queue\SqsQueueInterface');
        $job   = $this->getMock('SlmQueue\Job\JobInterface');

        $job->expects($this->once())
            ->method('execute')
            ->will($this->throwException(new \RuntimeException()));

        $queue->expects($this->never())->method('delete');

        $status = $this->worker->processJob($job, $queue);

        $this->assertEquals(WorkerEvent::JOB_STATUS_FAILURE_RECOVERABLE, $status);
    }

    public function testRethrowSqsException()
    {
        $this->setExpectedException('Aws\Sqs\Exception\SqsException');

        $queue = $this->getMock('SlmQueueSqs\Queue\SqsQueueInterface');
        $job   = $this->getMock('SlmQueue\Job\JobInterface');
        $command = $this->getMock('Aws\CommandInterface');

        $job->expects($this->once())
            ->method('execute')
            ->will($this->throwException(new SqsException('Foo', $command)));

        $queue->expects($this->never())->method('delete');

        $this->worker->processJob($job, $queue);
    }
}
