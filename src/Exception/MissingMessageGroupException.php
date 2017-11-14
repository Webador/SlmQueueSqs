<?php

namespace SlmQueueSqs\Exception;

class MissingMessageGroupException extends RuntimeException
{
    protected $message = 'A message group must be associated with FIFO queues. message_group_id parameter expected.';
}
