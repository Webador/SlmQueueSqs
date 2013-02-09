<?php

namespace SlmQueueSqs\Controller;

use Exception;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * This controller allow to execute jobs using the command line
 */
class WorkerController extends AbstractActionController
{
    /**
     * Process the queue given in parameter
     */
    public function processAction()
    {
        /** @var $worker \SlmQueueSqs\Worker\Worker */
        $worker    = $this->serviceLocator->get('SlmQueueSqs\Worker\Worker');
        $queueName = $this->params('queueName');
        $options   = array(
            'max_number_of_messages' => $this->params('maxJobs', null),
            'visibility_timeout'     => $this->params('visibilityTimeout', null),
            'wait_time_seconds'      => $this->params('waitTime', null)
        );

        try {
            $count = $worker->processQueue($queueName, array_filter($options));
        } catch(Exception $exception) {
            return "\nAn error occurred " . $exception->getMessage() . "\n\n";
        }

        return sprintf(
            "\nWork for queue %s is done, %s jobs were processed\n\n",
            $queueName,
            $count
        );
    }
}
