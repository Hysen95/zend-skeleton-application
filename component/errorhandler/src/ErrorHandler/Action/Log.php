<?php

namespace ErrorHandler\Action;

use ErrorHandler\Model\Error;

use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

class Log extends AbstractAction implements ActionInterface
{
	
	protected $logger = false;

	public function getLogger()
	{
    	if (!$this->logger)
    	{
	    	$defaultStream = __DIR__ . '/../../../../../data/logs/default.log';

	    	$config = $this->getConfig();
	    	$stream = isset($config['stream']) ? $config['stream'] : $defaultStream;

	    	$fp = @fopen($stream, 'a', false);
			if (!$fp)
			{
				$dir = dirname($stream);
                if (!file_exists($dir))
                {
					if (!mkdir($dir, 0777, true))
						throw new \Exception('ErrorHandler: failed to create '.$dir.' directory');
                }

                if (!file_exists($stream))
                {
                    touch($stream);
                    chmod($stream, 0777);
                }
			}

	    	$writer = new Stream($stream);
	    	$this->logger = new Logger();
	    	$this->logger->addWriter($writer);
    	}
    	return $this->logger;
	}

	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
		return $this;
	}

	public function getPriority()
	{
    	$defaultPriority = \Zend\Log\Logger::INFO;

	    $config = $this->getConfig();
	    $priority = isset($config['priority']) ? $config['priority'] : $defaultPriority;

	    return $priority;
	}

    public function run(Error $error)
    {
    	$logger = $this->getLogger();
    	$priority = $this->getPriority();

    	$logger->log($priority, $error->getLogAsString());

    	return true;
    }
	/**
	 * {@inheritDoc}
	 * @see \ErrorHandler\Action\ActionInterface::setConfig()
	 */
	public function setConfig($config) {
		// TODO: Auto-generated method stub

	}

}