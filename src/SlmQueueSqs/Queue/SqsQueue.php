<?php

namespace SlmQueueSqs\Queue;

use Aws\Sqs\SqsClient;
use SlmQueue\Job\JobInterface;
use SlmQueue\Job\JobPluginManager;
use SlmQueue\Queue\AbstractQueue;

/**
 * SqsQueue
 */
class SqsQueue extends AbstractQueue implements SqsQueueInterface
{
    /**
     * @var SqsClient
     */
    protected $sqsClient;

    /**
     * @var string
     */
    protected $queueUrl;


    /**
     * Constructor
     *
     * @param SqsClient        $sqsClient
     * @param string           $name
     * @param JobPluginManager $jobPluginManager
     */
    public function __construct(SqsClient $sqsClient, $name, JobPluginManager $jobPluginManager)
    {
        $this->sqsClient = $sqsClient;
        parent::__construct($name, $jobPluginManager);

        // As Amazon SQS queues are stored on another server, we need to fetch the queue URL
        $queue          = $this->sqsClient->createQueue(array('QueueName' => $name));
        $this->queueUrl = $queue['QueueUrl'];
    }

    /**
     * Valid option is:
     *      - delay_seconds: the duration (in seconds) the message has to be delayed
     *
     * {@inheritDoc}
     */
    public function push(JobInterface $job, array $options = array())
    {
        $parameters = array(
            'QueueUrl'     => $this->queueUrl,
            'MessageBody'  => $job->jsonSerialize(),
            'DelaySeconds' => isset($options['delay_seconds']) ? $options['delay_seconds'] : null
        );

        $result = $this->sqsClient->sendMessage(array_filter($parameters));

        $job->setMetadata(array(
            'id'  => $result['MessageId'],
            'md5' => $result['MD5OfMessageBody']
        ));
    }

    /**
     * Valid options are:
     *      - max_number_of_messages: maximum number of jobs to return
     *      - visibility_timeout: the duration (in seconds) that the received messages are hidden from subsequent
     *                            retrieve requests after being retrieved by a pop request
     *      - wait_time_seconds: by default, when we ask for a job, it will block until a job is found (possibly
     *                           forever if new jobs never come). If you set a wait time (in seconds), it will return
     *                           after the timeout is expired, even if no jobs were found
     *
     * {@inheritDoc}
     */
    public function pop(array $options = array())
    {
        $result = $this->sqsClient->receiveMessage(array(
            'QueueUrl'            => $this->queueUrl,
            'MaxNumberOfMessages' => isset($options['max_number_of_messages']) ? $options['max_number_of_messages'] : null,
            'VisibilityTimeout'   => isset($options['visibility_timeout']) ? $options['visibility_timeout'] : null,
            'WaitTimeSeconds'     => isset($options['wait_time_seconds']) ? $options['wait_time_seconds'] : null,
        ));

        $messages = $result['Messages']['items'];

        $jobs = array();
        foreach ($messages as $message) {
            $data = json_decode($message['Body'], true);

            $jobs[] = $this->createJob(
                $data['class'],
                $data['content'],
                array(
                    'id'            => $message['MessageId'],
                    'receiptHandle' => $message['ReceiptHandle'],
                    'md5'           => $message['MD5OfBody']
                )
            );
        }

        return $jobs;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(JobInterface $job)
    {
        $parameters = array(
            'QueueUrl'      => $this->queueUrl,
            'ReceiptHandle' => $job->getMetadata('receiptHandle')
        );

        $this->sqsClient->deleteMessage($parameters);
    }

    /**
     * Valid option is:
     *      - delay_seconds: the duration (in seconds) the message has to be delayed
     *
     * Please note that for this to work, the index for the job AND the option must match
     *
     * {@inheritDoc}
     */
    public function batchPush(array $jobs, array $options = array())
    {
        $parameters = array(
            'QueueUrl' => $this->queueUrl,
            'Entries'  => array()
        );

        /** @var $job JobInterface */
        foreach ($jobs as $key => $job) {
            $jobParameters = array(
                'Id'           => $key, // Identifier of the message in the batch
                'MessageBody'  => $job->jsonSerialize(),
                'DelaySeconds' => isset($options[$key]['delay_seconds']) ? $options[$key]['delay_seconds'] : null
            );

            $parameters['Entries']['items'][] = array_filter($jobParameters);
        }

        $result   = $this->sqsClient->sendMessage($parameters);
        $messages = $result['Successful']['items'];

        foreach ($messages as $message) {
            $batchId = $message['Id'];
            $jobs[$batchId]->setMetadata(array(
                'id'  => $message['MessageId'],
                'md5' => $message['MD5OfMessageBody']
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function batchDelete(array $jobs)
    {
        $parameters = array(
            'QueueUrl' => $this->queueUrl,
            'Entries'  => array()
        );

        /** @var $job JobInterface */
        foreach ($jobs as $key => $job) {
            $jobParameters = array(
                'Id'            => $key, // Identifier of the message in the batch
                'ReceiptHandle' => $job->getMetadata('receiptHandle')
            );

            $parameters['Entries']['items'][] = $jobParameters;
        }

        $this->sqsClient->deleteMessageBatch($parameters);
    }
}
