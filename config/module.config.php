<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'SlmQueueSqs\Service\SqsService' => 'SlmQueueSqs\Factory\SqsServiceFactory',
            'SlmQueueSqs\Worker\Worker'      => 'SlmQueueSqs\Factory\WorkerFactory'
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
                            'controller' => 'SlmQueueSqs\Controller\Worker',
                            'action'     => 'process'
                        ),
                    ),
                ),
            ),
        ),
    ),

    'controllers' => array(
        'factories' => array(
            'SlmQueueSqs\Controller\Worker' => 'SlmQueueSqs\Factory\WorkerControllerFactory'
        )
    ),
);
