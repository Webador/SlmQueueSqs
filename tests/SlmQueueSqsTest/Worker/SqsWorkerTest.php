<?php

namespace SlmQueueSqsTest\Worker;

use PHPUnit_Framework_TestCase as TestCase;
use SlmQueue\Options\WorkerOptions;
use SlmQueueSqs\Worker\SqsWorker;

class SqsWorkerTest extends TestCase
{
    public function testAssertJobIsDeletedIfNoExceptionIsThrown()
    {
        $queue = $this->getMock('SlmQueueSqs\Queue\SqsQueueInterface');
        $job   = $this->getMock('SlmQueue\Job\JobInterface');

        $job->expects($this->once())->method('execute');
        $queue->expects($this->once())->method('delete')->with($job);

        $worker = new SqsWorker(
            $this->getMock('SlmQueue\Queue\QueuePluginManager'),
            new WorkerOptions()
        );

        $worker->processJob($job, $queue);
    }
}
