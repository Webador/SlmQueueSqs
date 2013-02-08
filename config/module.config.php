<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'SlmQueueSqs\Worker\Worker' => 'SlmQueueSqs\Factory\WorkerFactory'
        )
    ),

    'console'   => array(
        'router' => array(
            'routes' => array(
                'slm-queue-sqs-worker' => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'queue sqs <queueName> --start',
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
        'invokables' => array(
            'SlmQueueSqs\Controller\Worker' => 'SlmQueueSqs\Controller\WorkerController'
        )
    ),
);
