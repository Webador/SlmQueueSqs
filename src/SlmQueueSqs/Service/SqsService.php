<?php

namespace SlmQueueSqs\Service;

use Aws\Sqs\SqsClient;

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
     * @return string $queueNamePrefix Optional queue name to filter queues by the given prefix
     * @return array
     */
    public function getQueueUrls($queueNamePrefix = '')
    {
        $result = $this->sqsClient->listQueues(array(
            'QueueNamePrefix' => $queueNamePrefix)
        );

        return $result['QueueUrls'];
    }
}
