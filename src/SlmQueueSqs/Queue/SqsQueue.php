<?php

namespace SlmQueueSqs\Queue;

use SlmQueue\Job\JobInterface;
use SlmQueue\Job\JobPluginManager;
use SlmQueue\Queue\AbstractQueue;

/**
 * SqsQueue
 */
class SqsQueue extends AbstractQueue implements SqsQueueInterface
{
    /**
     * {@inheritDoc}
     */
    public function push(JobInterface $job, array $options = array())
    {
        // TODO: Implement push() method.
    }

    /**
     * {@inheritDoc}
     */
    public function pop(array $options = array())
    {
        // TODO: Implement pop() method.
    }

    /**
     * {@inheritDoc}
     */
    public function delete(JobInterface $job)
    {
        // TODO: Implement delete() method.
    }

    /**
     * {@inheritDoc}
     */
    public function batchPush(array $jobs, array $options = array())
    {
        // TODO: Implement batchPush() method.
    }

    /**
     * {@inheritDoc}
     */
    public function batchDelete(array $jobs, array $options = array())
    {
        // TODO: Implement batchDelete() method.
    }
}
