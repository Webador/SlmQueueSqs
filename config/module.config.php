<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'SlmQueueSqs\Service\SqsService' => 'SlmQueueSqs\Factory\SqsServiceFactory',
            'SlmQueueSqs\Worker\SqsWorker'   => 'SlmQueueSqs\Factory\SqsWorkerFactory'
        )
    ),

    'console'   => array(
        'router' => array(
            'routes' => array(
                'slm-queue-sqs-worker' => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'queue sqs <queue> [--maxJobs=] [--visibilityTimeout=] [--waitTime=]',
                        'defaults' => array(
                            'controller' => 'SlmQueueSqs\Controller\SqsWorkerController',
                            'action'     => 'process'
                        ),
                    ),
                ),
            ),
        ),
    ),

    'controllers' => array(
        'factories' => array(
            'SlmQueueSqs\Controller\SqsWorkerController' => 'SlmQueueSqs\Factory\SqsWorkerControllerFactory'
        )
    ),
);
