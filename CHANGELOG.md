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
