<?php

namespace SlmQueueSqs\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Simple queue options
 */
class SqsQueueOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $queueUrl;

    /**
     * Set the queue URL
     *
     * @param string $queueUrl
     */
    public function setQueueUrl($queueUrl)
    {
        $this->queueUrl = (string) $queueUrl;
    }

    /**
     * Get the queue URL
     *
     * @return string
     */
    public function getQueueUrl()
    {
        return $this->queueUrl;
    }
}
