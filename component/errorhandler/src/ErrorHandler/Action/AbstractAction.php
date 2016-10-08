<?php

namespace ErrorHandler\Action;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractAction implements
	ActionInterface,
	ServiceLocatorAwareInterface
{
	
    protected $config = null;
    protected $serviceLocator = null;

    public function __construct($serviceLocator, $config)
    {
    	$this->setServiceLocator($serviceLocator);
    	$this->setConfig($config);
    }

    /**
     * Set the configuration array for the action object
     * @param array|null $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }


    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
