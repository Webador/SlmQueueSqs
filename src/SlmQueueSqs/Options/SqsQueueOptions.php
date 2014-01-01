<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace SlmQueueSqs\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Options for SQS queues
 */
class SqsQueueOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $queueUrl;

    /**
     * @var int
     */
    protected $delaySeconds = 0;

    /**
     * @var int
     */
    protected $retentionPeriod = 345600;

    /**
     * @var int
     */
    protected $waitTimeSeconds = 0;

    /**
     * @var int
     */
    protected $visibilityTimeout = 30;

    /**
     * @param string $queueUrl
     */
    public function setQueueUrl($queueUrl)
    {
        $this->queueUrl = (string) $queueUrl;
    }

    /**
     * @return string
     */
    public function getQueueUrl()
    {
        return $this->queueUrl;
    }

    /**
     * @param int $delaySeconds
     */
    public function setDelaySeconds($delaySeconds)
    {
        $this->delaySeconds = (int) $delaySeconds;
    }

    /**
     * @return int
     */
    public function getDelaySeconds()
    {
        return $this->delaySeconds;
    }

    /**
     * @param int $retentionPeriod
     */
    public function setRetentionPeriod($retentionPeriod)
    {
        $this->retentionPeriod = (int) $retentionPeriod;
    }

    /**
     * @return int
     */
    public function getRetentionPeriod()
    {
        return $this->retentionPeriod;
    }

    /**
     * @param int $waitTimeSeconds
     */
    public function setWaitTimeSeconds($waitTimeSeconds)
    {
        $this->waitTimeSeconds = (int) $waitTimeSeconds;
    }

    /**
     * @return int
     */
    public function getWaitTimeSeconds()
    {
        return $this->waitTimeSeconds;
    }

    /**
     * @param int $visibilityTimeout
     */
    public function setVisibilityTimeout($visibilityTimeout)
    {
        $this->visibilityTimeout = (int) $visibilityTimeout;
    }

    /**
     * @return int
     */
    public function getVisibilityTimeout()
    {
        return $this->visibilityTimeout;
    }
}
