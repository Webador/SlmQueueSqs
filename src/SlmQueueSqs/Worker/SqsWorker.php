<?php

namespace SlmQueueSqs\Worker;

use Exception;
use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Worker\AbstractWorker;
use SlmQueue\Worker\WorkerEvent;
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
            return WorkerEvent::JOB_STATUS_UNKNOWN;
        }

        // In SQS, if an error occurs (exception for instance), the job is automatically reinserted
        // into the queue after a configured delay (the "visibility_timeout" option). If the job executed
        // correctly, it must explicitly be removed
        try {
            $job->execute();
            $queue->delete($job);

            return WorkerEvent::JOB_STATUS_SUCCESS;
        } catch (Exception $exception) {
            // Do nothing, the job will be reinserted automatically for another try
            return WorkerEvent::JOB_STATUS_FAILURE_RECOVERABLE;
        }
    }
}
