<?php

namespace SlmQueueSqs\Service;

use Aws\Sqs\Enum\QueueAttribute;
use Aws\Sqs\SqsClient;
use SlmQueueSqs\Options\SqsQueueOptions;

/**
 * This is a thin wrapper around Amazon SQS client
 */
class SqsService
{
    /**
     * @var SqsClient
     */
    protected $sqsClient;

    /**
     * Constructor
     *
     * @param SqsClient $sqsClient
     */
    public function __construct(SqsClient $sqsClient)
    {
        $this->sqsClient = $sqsClient;
    }

    /**
     * Get the Amazon SQS client
     *
     * @return SqsClient
     */
    public function getClient()
    {
        return $this->sqsClient;
    }

    /**
     * Get the list of all the queue URLs
     *
     * @param  string $queueNamePrefix Optional queue name to filter queues by the given prefix
     * @return array
     */
    public function getQueueUrls($queueNamePrefix = '')
    {
        $result = $this->sqsClient->listQueues(array(
            'QueueNamePrefix' => $queueNamePrefix
        ));

        return $result['QueueUrls'];
    }

    /**
     * Create a new queue using the given options, and return the queue URL
     *
     * @param  string          $queueName
     * @param  SqsQueueOptions $options
     * @return string
     */
    public function createQueue($queueName, SqsQueueOptions $options)
    {
        $attributes = array(
            QueueAttribute::DELAY_SECONDS                     => $options->getDelaySeconds(),
            QueueAttribute::MESSAGE_RETENTION_PERIOD          => $options->getRetentionPeriod(),
            QueueAttribute::RECEIVE_MESSAGE_WAIT_TIME_SECONDS => $options->getWaitTimeSeconds(),
            QueueAttribute::VISIBILITY_TIMEOUT                => $options->getVisibilityTimeout()
        );

        $queue = $this->sqsClient->getQueueUrl(array(
            'QueueName'  => $queueName,
            'Attributes' => array_filter($attributes)
        ));

        return $queue['QueueUrl'];
    }
}
