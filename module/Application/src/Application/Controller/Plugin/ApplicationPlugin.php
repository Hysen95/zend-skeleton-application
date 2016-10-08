<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class ApplicationPlugin extends AbstractPlugin {
	
	public function getCurrentConfig() {
		$config = $this->controller->getServiceLocator()->get("Config");
		return $config;
	}
	
}