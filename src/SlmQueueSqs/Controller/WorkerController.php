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
        $queueName = $this->params('queue');

        try {
            $count = $worker->processQueue($queueName);
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
