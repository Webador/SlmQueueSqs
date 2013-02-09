<?php

namespace SlmQueueSqs\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * SqsOptions
 */
class SqsOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $configFile;


    /**
     * Set the Amazon AWS config file path
     *
     * @param  string $configFile
     * @throws Exception\RuntimeException
     * @return void
     */
    public function setConfigFile($configFile)
    {
        if (!is_file($configFile)) {
            throw new Exception\RuntimeException(sprintf(
                'Path to Amazon AWS config file is not valid, %s given',
                $configFile
            ));
        }

        $this->configFile = $configFile;
    }

    /**
     * Get the Amazon AWS config file path
     *
     * @return string
     */
    public function getConfigFile()
    {
        return $this->configFile;
    }
}
