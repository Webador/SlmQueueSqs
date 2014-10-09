<?php

namespace SlmQueueSqs\Queue;

use Aws\Sqs\SqsClient;
use SlmQueue\Job\JobInterface;
use SlmQueue\Job\JobPluginManager;
use SlmQueue\Queue\AbstractQueue;
use SlmQueueSqs\Exception;
use SlmQueueSqs\Options\SqsQueueOptions;

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
     * @var SqsQueueOptions
     */
    protected $queueOptions;

    /**
     * Constructor
     *
     * @param SqsClient        $sqsClient
     * @param SqsQueueOptions  $options
     * @param string           $name
     * @param JobPluginManager $jobPluginManager
     */
    public function __construct(
        SqsClient $sqsClient,
        SqsQueueOptions $options,
        $name,
        JobPluginManager $jobPluginManager
    ) {
        $this->sqsClient    = $sqsClient;
        $this->queueOptions = $options;

        parent::__construct($name, $jobPluginManager);

        // If an URL has explicitly been given in the options, let's use it, otherwise we dynamically fetch it
        if (!$this->queueOptions->getQueueUrl()) {
            $queue = $this->sqsClient->getQueueUrl(array('QueueName' => $name));
            $this->queueOptions->setQueueUrl($queue['QueueUrl']);
        }
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
            'QueueUrl'     => $this->queueOptions->getQueueUrl(),
            'MessageBody'  => $this->serializeJob($job),
            'DelaySeconds' => isset($options['delay_seconds']) ? $options['delay_seconds'] : null
        );

        $result = $this->sqsClient->sendMessage(array_filter($parameters));

        $job->setMetadata(array(
            '__id__' => $result['MessageId'],
            'md5'    => $result['MD5OfMessageBody']
        ));
    }

    /**
     * Valid options are (if you want to pop multiple jobs at once, use batchPop instead):
     *      - visibility_timeout: the duration (in seconds) that the received messages are hidden from subsequent
     *                            retrieve requests after being retrieved by a pop request
     *      - wait_time_seconds: by default, when we ask for a job, it will do a "short polling", it will
     *                           immediately return if no job was found. Amazon SQS also supports "long polling". This
     *                           value can be between 1 and 20 seconds. This allows to maintain the connection active
     *                           during this period of time, hence reducing the number of empty responses.
     *
     * {@inheritDoc}
     */
    public function pop(array $options = array())
    {
        $options['max_number_of_messages'] = 1;

        $jobs = $this->batchPop($options);

        switch(count($jobs)) {
            case 0:
                return null;
            case 1:
                return reset($jobs);
            default:
                throw new Exception\RuntimeException(sprintf(
                    '%s jobs were popped in "%s" method, while only one (or zero) were expected.',
                    count($jobs),
                    __METHOD__
                ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(JobInterface $job)
    {
        $parameters = array(
            'QueueUrl'      => $this->queueOptions->getQueueUrl(),
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
        // SQS can only handle up to 10 jobs, so if we have more jobs, we handle them in slices
        if (count($jobs) > 10) {
            do {
                $splicedJobs = array_splice($jobs, 0, 10);
                $this->batchPush($splicedJobs, $options);
            } while (count($splicedJobs) >= 10);

            return;
        }

        $parameters = array(
            'QueueUrl' => $this->queueOptions->getQueueUrl(),
            'Entries'  => array()
        );

        /** @var $job JobInterface */
        foreach ($jobs as $key => $job) {
            $jobParameters = array(
                'Id'           => $key, // Identifier of the message in the batch
                'MessageBody'  => $this->serializeJob($job),
                'DelaySeconds' => isset($options[$key]['delay_seconds']) ? $options[$key]['delay_seconds'] : null
            );

            $parameters['Entries'][] = array_filter($jobParameters, function ($value) {
                return $value !== null;
            });
        }

        $result   = $this->sqsClient->sendMessageBatch($parameters);
        $messages = $result['Successful'];

        foreach ($messages as $message) {
            $batchId = $message['Id'];
            $jobs[$batchId]->setMetadata(array(
                '__id__' => $message['MessageId'],
                'md5'    => $message['MD5OfMessageBody']
            ));
        }
    }

    /**
     * Valid options are:
     *      - max_number_of_messages: maximum number of jobs to return. As of today, the max value can be 10. Please
     *                                remember that Amazon SQS does not guarantee that you will receive exactly
     *                                this number of messages, rather you can receive UP-TO n messages.
     *      - visibility_timeout: the duration (in seconds) that the received messages are hidden from subsequent
     *                            retrieve requests after being retrieved by a pop request
     *      - wait_time_seconds: by default, when we ask for a job, it will do a "short polling", it will
     *                           immediately return if no job was found. Amazon SQS also supports "long polling". This
     *                           value can be between 1 and 20 seconds. This allows to maintain the connection active
     *                           during this period of time, hence reducing the number of empty responses.
     *
     * {@inheritDoc}
     */
    public function batchPop(array $options = array())
    {
        $result = $this->sqsClient->receiveMessage(array(
            'QueueUrl'            => $this->queueOptions->getQueueUrl(),
            'MaxNumberOfMessages' => isset($options['max_number_of_messages'])
                    ? $options['max_number_of_messages'] : null,
            'VisibilityTimeout'   => isset($options['visibility_timeout']) ? $options['visibility_timeout'] : null,
            'WaitTimeSeconds'     => isset($options['wait_time_seconds']) ? $options['wait_time_seconds'] : null
        ));

        $messages = $result['Messages'];

        if (empty($messages)) {
            return array();
        }

        $jobs = array();
        foreach ($messages as $message) {
            $jobs[] = $this->unserializeJob(
                $message['Body'],
                array(
                    '__id__'        => $message['MessageId'],
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
    public function batchDelete(array $jobs)
    {
        // SQS can only handle up to 10 jobs, so if we have more jobs, we handle them in slices
        if (count($jobs) > 10) {
            do {
                $splicedJobs = array_splice($jobs, 0, 10);
                $this->batchDelete($splicedJobs);
            } while (count($splicedJobs) >= 10);

            return;
        }

        $parameters = array(
            'QueueUrl' => $this->queueOptions->getQueueUrl(),
            'Entries'  => array()
        );

        /** @var $job JobInterface */
        foreach ($jobs as $key => $job) {
            $jobParameters = array(
                'Id'            => $key, // Identifier of the message in the batch
                'ReceiptHandle' => $job->getMetadata('receiptHandle')
            );

            $parameters['Entries'][] = $jobParameters;
        }

        $this->sqsClient->deleteMessageBatch($parameters);
    }
}
