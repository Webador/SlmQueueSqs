<?php

namespace SlmQueueSqs\Queue;

use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;

/**
 * Contract for a Sqs queue
 */
interface SqsQueueInterface extends QueueInterface
{
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
     * @param  array          $options
     * @return void
     */
    public function batchDelete(array $jobs, array $options = array());
}
