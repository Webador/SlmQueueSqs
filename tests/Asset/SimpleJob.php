<?php

namespace SlmQueueSqsTest\Asset;

use SlmQueue\Job\AbstractJob;

class SimpleJob extends AbstractJob
{
    /**
     * {@inheritDoc}
     */
    public function execute(): ?int
    {
    }
}
