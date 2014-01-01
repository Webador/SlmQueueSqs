<?php

namespace SlmQueueSqs\Queue;

use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueueSqs\Options\SqsQueueOptions;

/**
 * Contract for a Sqs queue
 */
interface SqsQueueInterface extends QueueInterface
{
    /**
     * Get the SQS queue options
     *
     * @return SqsQueueOptions
     */
    public function getSqsQueueOptions();

    /**
     * Push several jobs at once
     *
     * @param  JobInterface[] $jobs
     * @param  array          $options
     * @return void
     */
    public function batchPush(array $jobs, array $options = array());

    /**
     * Delete several jobs at once
     *
     * @param  JobInterface[] $jobs
     * @return void
     */
    public function batchDelete(array $jobs);
}
