SlmQueueSqs
===========

[![Build Status](https://travis-ci.org/juriansluiman/SlmQueueSqs.png?branch=master)](https://travis-ci.org/juriansluiman/SlmQueueSqs)
[![Latest Stable Version](https://poser.pugx.org/slm/queue-sqs/v/stable.png)](https://packagist.org/packages/slm/queue-sqs)
[![Latest Unstable Version](https://poser.pugx.org/slm/queue-sqs/v/unstable.png)](https://packagist.org/packages/slm/queue-sqs)
[![Total Downloads](https://poser.pugx.org/slm/queue-sqs/downloads.png)](https://packagist.org/packages/slm/queue-sqs)

Version 0.5.0 Created by Jurian Sluiman and Michaël Gallego

Requirements
------------
* [Zend Framework 2](https://github.com/zendframework/zf2)
* [SlmQueue](https://github.com/juriansluiman/SlmQueue)
* [Amazon AWS SDK > 2.1.1](https://github.com/aws/aws-sdk-php)
* [Amazon AWS ZF2 module](https://github.com/aws/aws-sdk-php-zf2)

To-do
-----

Feel free to help in those areas if you like this module !

* Write more tests to assert the queue work as expected
* Better error handling (currently, errors that may be returned by SQS client are completely ignored, we'd
 like to throw exceptions so that people can handle them in their code)
* More support for programmatic queue handling: currently SlmQueueSqs offers very few options to create new
 queues (we assume people to create them from the Amazon Console or directly through the SDK). It may be useful
 to offer better integration so that SlmQueueSqs also offers a nice interface to create new queues.

Installation
------------

First, install SlmQueue ([instructions here](https://github.com/juriansluiman/SlmQueue/blob/master/README.md)). Then,
add the following line into your `composer.json` file:

```json
"require": {
	"slm/queue-sqs": "^1.0"
}
```

Then, enable the module by adding `SlmQueueSqs` in your application.config.php file (you must also add the `AwsModule` key
for enabling the AWS ZF2 module.

> Starting from 0.3.0, SlmQueueSqs now internally uses the official AWS Zend Framework 2 module, so you can write
your credentials only once for all AWS services.


Configuring AWS
---------------

Version must be specified for AWS SQS!

```
<?php
return [
    'aws' => [
        'credentials' => [
            'key' => 'ACCESS_KEY_GOES_HERE',
            'secret' => 'SECRET_KEY_GOES_HERE'
        ],
        'region' => 'us-east-1', ## or your region ##
        'Sqs' => [
            'version' => '2012-11-05' ## suggested to code this to a specific version of the SQS API.
        ]
    ]
];
```

Documentation
-------------

Before reading SlmQueueSqs documentation, please read [SlmQueue documentation](https://github.com/juriansluiman/SlmQueue).

SlmQueueSqs does not offer any features to create queues. You should use the official SQS SDK or use the AWS console.

### Setting your AWS credentials

Please refer to [the documentation of the official AWS ZF2 module](https://github.com/aws/aws-sdk-php-zf2#configuration).

### Setting metadata

Like other SlmQueue providers, you can set specific metadata to a job. Please note that this is different from the
[built-in message attributes](http://aws.amazon.com/fr/blogs/aws/simple-queue-service-message-attributes/) that was
introduced in SQS in May 2014. SlmQueueSqs does not support this currently, but this is very similar to the metadata
in SlmQueue.

Please note that, when jobs are retrieved using the worker, some metadata pulled from SQS are automatically injected
into your job. Therefore, you are encouraged to NOT use those keys, as your metadata will override SQS metadata:

* `id`: this is the SQS message id.
* `receiptHandle`: this is the SQS receipt handle, that is used to delete a job, among other thing.
* `md5`: this is the MD5 signature of the message, calculated by AWS. You could use that to validate your messages.

### Adding queues

SlmQueueSqs provides an interface for SQS queues that extends `SlmQueue\Queue\QueueInterface`, and provides in
addition the following methods:

* batchPush(array $jobs, array $options = array()): insert many jobs at once into the queue. Please note that if
you need to specify options, the index key for both jobs and options must matched.
* batchDelete(array $jobs): delete multiple jobs at once from the queue.

A concrete class that implements this interface is included: `SlmQueueSqs\Queue\SqsQueue` and a factory is available to
create Sqs queues. Therefore, if you want to have a queue called "newsletter", just add the following line in your
`module.config.php` file:

```
<?php
return array(
    'slm_queue' => array(
        'queue_manager' => array(
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

* visibility_timeout: the duration (in seconds) that the received messages are hidden from subsequent
  retrieve requests after being retrieved by a pop request
* wait_time_seconds: by default, when we ask for a job, it will do a "short polling", it will
  immediately return if no job was found. Amazon SQS also supports "long polling". This
  value can be between 1 and 20 seconds. This allows to maintain the connection active
  during this period of time, hence reducing the number of empty responses.

#### batchPop

Valid options are:

* max_number_of_messages: maximum number of jobs to return. As of today, the max value can be 10. Please
 remember that Amazon SQS does not guarantee that you will receive exactly
 this number of messages, rather you can receive UP-TO n messages.
* visibility_timeout: the duration (in seconds) that the received messages are hidden from subsequent
  retrieve requests after being retrieved by a pop request
* wait_time_seconds: by default, when we ask for a job, it will do a "short polling", it will
  immediately return if no job was found. Amazon SQS also supports "long polling". This
  value can be between 1 and 20 seconds. This allows to maintain the connection active
  during this period of time, hence reducing the number of empty responses.

### Configuring queues

You may want to explicitly set queue URL instead of having it automatically fetched by its name (this can be useful
if you want to use different queues in prod and test environments, while still referencing it using the same
queue name in your code). To do so, add the following config:

```
<?php
return array(
    'slm_queue' => array(
        'queues' => array(
            'worker-queue' => array(
                'queue_url' => 'http://sqs.amazonaws.com/my-queue'
            )
        )
    )
);
```

Now, this URL will be reused instead of fetching it to AWS servers.

### Executing jobs

SlmQueueSqs provides a command-line tool that can be used to pop and execute jobs. You can type the following
command within the public folder of your Zend Framework 2 application:

`php index.php queue sqs <queue> [--visibilityTimeout=] [--waitTime=]`

The queue name is a mandatory parameter, while the other parameters are all optional:

* visibilityTimeout: duration (in seconds) that the received messages are hidden from subsequent retrieve requests after being retrieved by a pop request
* waitTime: wait time (in seconds) for which the call will wait for a job to arrive in the queue before returning



Troubleshooting
---------------

Issue:

```
no instance returnedMissing required client configuration options: 

version: (string)

  A "version" configuration value is required. Specifying a version constraint
  ensures that your code will not be affected by a breaking change made to the
  service. For example, when using Amazon S3, you can lock your API version to
  "2006-03-01".
  
  Your build of the SDK has the following version(s) of "sqs": * "2012-11-05"
  
  You may provide "latest" to the "version" configuration value to utilize the
  most recent available API version that your client's API provider can find.
  Note: Using 'latest' in a production application is not recommended.
  
  A list of available API versions can be found on each client's API documentation
  page: http://docs.aws.amazon.com/aws-sdk-php/v3/api/index.html. If you are
  unable to load a specific API version, then you may need to update your copy of
  the SDK.======================================================================
   The application has thrown an exception!
======================================================================
 Zend\ServiceManager\Exception\ServiceNotCreatedException
 The factory was called but did not return an instance.

 ```

Solution: See above Aws configuration example for version definition.
