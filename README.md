SlmQueueSqs
===========

[![Build Status](https://travis-ci.org/juriansluiman/SlmQueueSqs.png?branch=master)](https://travis-ci.org/juriansluiman/SlmQueueSqs)

Version 0.2.2 Created by Jurian Sluiman and Michaël Gallego

> NOTE : this is an early release of SlmQueueSqs, although it is tested, it may not work as expected. Please use
> with caution, and don't hesitate to open issues or PRs !


Requirements
------------
* [Zend Framework 2](https://github.com/zendframework/zf2)
* [SlmQueue](https://github.com/juriansluiman/SlmQueue)
* [Amazon AWS SDK 2](https://github.com/aws/aws-sdk-php)


Installation
------------

First, install SlmQueue ([instructions here](https://github.com/juriansluiman/SlmQueue/blob/master/README.md)). Then,
add the following line into your `composer.json` file:

```json
"require": {
	"juriansluiman/slm-queue-sqs": ">=0.2"
}
```

Then, enable the module by adding `SlmQueueSqs` in your application.config.php file. You may also want to
configure the module: just copy the `slm_queue_sqs.local.php.dist` (you can find this file in the config
folder of SlmQueueSqs) into your config/autoload folder, and override what you want.


Documentation
-------------

Before reading SlmQueueSqs documentation, please read [SlmQueue documentation](https://github.com/juriansluiman/SlmQueue).

Currently, SlmQueueSqs does not offer any real features to create queues. You'd better use the administrator console
of Amazon AWS services to create queues.


### Setting the connection parameters

Copy the `slm_queue_sqs.local.php.dist` file to your `config/autoload` folder, and follow the instructions.


### Adding queues

SlmQueueSqs provides an interface for SQS queues that extends `SlmQueue\Queue\QueueInterface`, and provides in
addition the following methods:

* batchPush(array $jobs, array $options = array()): insert many jobs at once into the queue. Please note that if
you need to specify options, the index key for both jobs and options must matched.
* batchDelete(array $jobs): delete multiple jobs at once from the queue.

A concrete class that implements this interface is included: `SlmQueueSqs\Queue\SqsQueue` and a factory is available to
create Sqs queues. Therefore, if you want to have a queue called "email", just add the following line in your
`module.config.php` file:

```php
return array(
    'slm_queue' => array(
        'queues' => array(
            'factories' => array(
                'newsletter' => 'SlmQueueSqs\Factory\SqsQueueFactory'
            )
        )
    )
);
```

This queue can therefore be pulled from the QueuePluginManager class.


### Operations on queues

> The name of the options match the names of the Amazon AWS SDK.

#### push / batchPush

Valid option is:

* delay_seconds: the duration (in seconds) the message has to be delayed

Example:

```php
$queue->push($job, array(
    'delay_seconds' => 20
));
```

#### pop

Valid options are:

* max_number_of_messages: maximum number of jobs to return
* visibility_timeout: the duration (in seconds) that the received messages are hidden from subsequent
retrieve requests after being retrieved by a pop request
* wait_time_seconds: by default, when we ask for a job, it will block until a job is found (possibly forever if new
jobs never come). If you set a wait time (in seconds), it will return after the timeout is expired, even if no jobs were found


### Executing jobs

SlmQueueSqs provides a command-line tool that can be used to pop and execute jobs. You can type the following
command within the public folder of your Zend Framework 2 application:

`php index.php queue sqs <queueName> [--maxJobs=] [--visibilityTimeout=] [--waitTime=] --start`

The queueName is a mandatory parameter, while the other parameters are all optional:

* maxJobs: maximum number of jobs that can be returned from a single pop call
* visibilityTimeout: duration (in seconds) that the received messages are hidden from subsequent retrieve requests after being retrieved by a pop request
* waitTime: wait time (in seconds) for which the call will wait for a job to arrive in the queue before returning


### Getting the list of all existing queues

You may want to retrieve a list of all existing Amazon SQS queues. You can do so by creating the `SqsService` object
and calling the method `getQueueUrls`:

```php
$sqsService = $serviceLocator->get('SlmQueueSqs\Service\SqsService');
$queues     = $sqsService->getQueueUrls();
```
