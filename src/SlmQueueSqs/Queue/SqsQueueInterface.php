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
     * Pop several jobs at once
     *
     * @param  array $options
     * @return JobInterface[]
     */
    public function batchPop(array $options = array());

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
