# 0.3.0 (dev)

- Use AWS ZF 2 module
- pop method now only returns 1 job. You must use batchPop to return more than one job.
- SqsQueue now accepts `name` parameter to be a URL. If it is a URL, it will not fetch the URL (which needs one
HTTP calls to SQS API), and instead directly uses it.
- [BC] SQS service has been removed, please now use the official AWS SDK to list or create queues
- [BC] SqsQueue now uses `getQueueUrl` instead of `createQueue`. This means that you must first create
queues using the official SQS client or the AWS console. This change has been made to offer a more predictable
behaviour for SQS queues.

# 0.2.4

- Fix a bug when SQS returns no messages

# 0.2.3

- Fix a bug with batch push
- Fix a bug with batch delete
- More unit tests

# 0.2.2

- Set the minimal version of AWS SDK to 2.1.1 (which is the first version to support Amazon SQS)

# 0.2.1

- Fix compatibilities problems with PHP 5.3

# 0.2.0

This version is a complete rewrite of SlmQueue. It is now splitted in several modules and support both
Beanstalkd and Amazon SQS queue systems through SlmQueueBeanstalkd and SlmQueueSqs modules.
