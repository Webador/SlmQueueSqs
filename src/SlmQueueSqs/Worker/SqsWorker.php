<?php

namespace SlmQueueSqs\Worker;

use Exception;
use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueAwareInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Worker\AbstractWorker;
use SlmQueueSqs\Queue\SqsQueue;
use SlmQueueSqs\Queue\SqsQueueInterface;

/**
 * Worker for Amazon SQS
 */
class SqsWorker extends AbstractWorker
{
    /**
     * {@inheritDoc}
     */
    public function processJob(JobInterface $job, QueueInterface $queue)
    {
        if (!$queue instanceof SqsQueueInterface) {
            return;
        }

        // In SQS, if an error occurs (exception for instance), the job is automatically reinserted
        // into the queue after a configured delay (the "visibility_timeout" option). If the job executed
        // correctly, it must explicitely be removed
        $job->execute();
        $queue->delete($job);
    }
}
