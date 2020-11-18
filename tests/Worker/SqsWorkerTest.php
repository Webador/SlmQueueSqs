<?php

namespace SlmQueueSqsTest\Worker;

use Aws\Sqs\Exception\SqsException;
use PHPUnit\Framework\TestCase;
use SlmQueue\Worker\Event\ProcessJobEvent;
use SlmQueueSqs\Worker\SqsWorker;
use Laminas\EventManager\EventManagerInterface;

class SqsWorkerTest extends TestCase
{
    /**
     * @var SqsWorker
     */
    protected $worker;

    public function setUp(): void
    {
        $this->worker = new SqsWorker($this->getMockBuilder(EventManagerInterface::class)->getMock());
    }

    public function testReturnsUnknownIfNotASqsQueue()
    {
        $queue = $this->getMockBuilder('SlmQueue\Queue\QueueInterface')->getMock();
        $job   = $this->getMockBuilder('SlmQueue\Job\JobInterface')->getMock();

        $this->assertEquals(ProcessJobEvent::JOB_STATUS_UNKNOWN, $this->worker->processJob($job, $queue));
    }

    public function testDeleteJobOnSuccess()
    {
        $queue = $this->getMockBuilder('SlmQueueSqs\Queue\SqsQueueInterface')->getMock();
        $job   = $this->getMockBuilder('SlmQueue\Job\JobInterface')->getMock();

        $job->expects($this->once())->method('execute');
        $queue->expects($this->once())->method('delete')->with($job);

        $status = $this->worker->processJob($job, $queue);

        $this->assertEquals(ProcessJobEvent::JOB_STATUS_SUCCESS, $status);
    }

    public function testDoNotDeleteJobOnFailure()
    {
        $queue = $this->getMockBuilder('SlmQueueSqs\Queue\SqsQueueInterface')->getMock();
        $job   = $this->getMockBuilder('SlmQueue\Job\JobInterface')->getMock();

        $job->expects($this->once())
            ->method('execute')
            ->will($this->throwException(new \RuntimeException()));

        $queue->expects($this->never())->method('delete');

        $status = $this->worker->processJob($job, $queue);

        $this->assertEquals(ProcessJobEvent::JOB_STATUS_FAILURE_RECOVERABLE, $status);
    }

    public function testRethrowSqsException()
    {
        $this->expectException('Aws\Sqs\Exception\SqsException');

        $queue = $this->getMockBuilder('SlmQueueSqs\Queue\SqsQueueInterface')->getMock();
        $job   = $this->getMockBuilder('SlmQueue\Job\JobInterface')->getMock();
        $command = $this->getMockBuilder('Aws\CommandInterface')->getMock();

        $job->expects($this->once())
            ->method('execute')
            ->will($this->throwException(new SqsException('Foo', $command)));

        $queue->expects($this->never())->method('delete');

        $this->worker->processJob($job, $queue);
    }
}
