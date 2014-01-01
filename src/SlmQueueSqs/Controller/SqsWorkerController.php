<?php

namespace SlmQueueSqs\Controller;

use SlmQueue\Controller\AbstractWorkerController;
use SlmQueue\Controller\Exception\WorkerProcessException;
use SlmQueue\Exception\ExceptionInterface;

/**
 * This controller allow to execute jobs using the command line
 */
class SqsWorkerController extends AbstractWorkerController
{
    /**
     * Process a queue
     *
     * @return string
     * @throws WorkerProcessException
     */
    public function processAction()
    {
        $params = $this->params()->fromRoute();

        $options = array(
            'queue'                  => $params['queue'],
            'max_number_of_messages' => $params['maxJobs'],
            'visibility_timeout'     => isset($params['visibilityTimeout']) ? $params['visibilityTimeout'] : null,
            'wait_time_seconds'      => isset($params['waitTime']) ? $params['waitTime'] : null
        );

        $queue = $options['queue'];

        try {
            if ($options['max_number_of_messages'] > 1) {
                $result = $this->worker->processBatchableQueue($queue, array_filter($options));
            } else {
                $result = $this->worker->processQueue($queue, array_filter($options));
            }
        } catch (ExceptionInterface $e) {
            throw new WorkerProcessException(
                'Caught exception while processing queue',
                $e->getCode(), $e
            );
        }

        return sprintf(
            "Finished worker for queue '%s' with %s jobs\n",
            $queue,
            $result
        );
    }
}
