<?php

namespace SlmQueueSqs\Worker;

use Exception;
use SlmQueue\Job\JobInterface;
use SlmQueueSqs\Queue\SqsQueueInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Worker\AbstractWorker;

/**
 * Worker for Amazon SQS
 */
class Worker extends AbstractWorker
{
    /**
     * {@inheritDoc}
     */
    public function processJob(JobInterface $job, QueueInterface $queue)
    {
        if (!$queue instanceof SqsQueueInterface) {
            return;
        }

        // Contrary to Beanstalkd, Amazon SQS does not have any mechanism to reinsert a job when it
        // has a problem (bury in Beanstalkd). Currently, we just execute and delete if no error occurred
        $job->execute();
        $queue->delete($job);
    }
}
