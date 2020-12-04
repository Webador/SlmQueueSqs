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
    public function processAction(): string
    {
        $params = $this->params()->fromRoute();

        $options = array(
            'queue'              => $params['queue'],
            'visibility_timeout' => isset($params['visibilityTimeout']) ? $params['visibilityTimeout'] : null,
            'wait_time_seconds'  => isset($params['waitTime']) ? $params['waitTime'] : null
        );

        $queue = $this->queuePluginManager->get($options['queue']);

        try {
            $messages = $this->worker->processQueue($queue, array_filter($options));
        } catch (ExceptionInterface $e) {
            throw new WorkerProcessException('Caught exception while processing queue', $e->getCode(), $e);
        }

        return $this->formatOutput($options['queue'], $messages);
    }
}
