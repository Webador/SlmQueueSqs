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
    public function processQueue($queueName, array $options = array())
    {
        // If user want to pop only one job, delegate this to the parent method
        if (!isset($options['max_jobs']) || $options['max_jobs'] == 1) {
            return parent::processQueue($queueName, $options);
        }

        /** @var $queue QueueInterface */
        $queue = $this->queuePluginManager->get($queueName);
        $count = 0;

        // If we're here, this is batch pop, so we must have a SqsQueue
        if (!$queue instanceof SqsQueueInterface) {
            return $count;
        }

        while (true) {
            // Check for external stop condition
            if ($this->isStopped()) {
                break;
            }

            $jobs = $queue->batchPop($options);

            foreach ($jobs as $job) {
                // The queue may return null, for instance if a timeout was set
                if (!$job instanceof JobInterface) {
                    continue;
                }

                // The job might want to get the queue injected
                if ($job instanceof QueueAwareInterface) {
                    $job->setQueue($queue);
                }

                $this->processJob($job, $queue);
                $count++;

                // Check for internal stop condition
                if (
                    $count === $this->options->getMaxRuns()
                    || memory_get_usage() > $this->options->getMaxMemory()
                ) {
                    break;
                }
            }
        }

        return $count;
    }

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
