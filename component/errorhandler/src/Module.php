<?php

namespace ErrorHandler;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

class Module implements
    ConfigProviderInterface,
    DependencyIndicatorInterface
{
	
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' .  __NAMESPACE__,
                ),
            ),
        );
    }
   
    public function getModuleDependencies()
    {
        return array();
    }

    public function onBootstrap(MvcEvent $e)
    {
    	
        $application = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();

        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onError'), -999999999);

        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER_ERROR, array($this, 'onError'), -999999999);

        $config = $serviceManager->get('Configuration');

        $this->_setPhpSettings($config);

        $phpErrorsLoggerConfig = (isset($config['zf_error_handler']) &&
            isset($config['zf_error_handler']['php_errors_handler'])) ?
                $config['zf_error_handler']['php_errors_handler'] :
                array();

        if (!isset($phpErrorsLoggerConfig['handle_errors']))
            $phpErrorsLoggerConfig['handle_errors'] = false;

        if (!isset($phpErrorsLoggerConfig['actions']))
            $phpErrorsLoggerConfig['actions'] = array();

        $request = $serviceManager->get('Request');
        if (!$request instanceof \Zend\Console\Request && $phpErrorsLoggerConfig['handle_errors'] && count($phpErrorsLoggerConfig['actions']) > 0)
        {
            $actions = array();

            if (isset($phpErrorsLoggerConfig['actions']['log']))
            {
                $defaultStream = __DIR__ . '/../../../../data/logs/phpErrors_default.log';

                $stream = isset($phpErrorsLoggerConfig['actions']['log']['config']['stream']) ? $phpErrorsLoggerConfig['actions']['log']['config']['stream'] : $defaultStream;

                $fp = @fopen($stream, 'a', false);
                if (!$fp)
                {
                    $dir = dirname($stream);
                    if (!file_exists($dir))
                    {
                        if (!mkdir($dir, 0777, true))
                            throw new \Exception('ErrorHandler[php_errors_logger]: failed to create '.$dir.' directory');
                    }

                    if (!file_exists($stream))
                    {
                        touch($stream);
                        chmod($stream, 0777);
                    }
                }

                $writer = new \Zend\Log\Writer\Stream($stream);
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);

                \Zend\Log\Logger::registerErrorHandler($logger);
                \Zend\Log\Logger::registerExceptionHandler($logger);

                $logAction = new \ErrorHandler\Action\Log($serviceManager, array());
                $logAction->setLogger($logger);
                $actions['log'] = $logAction;
            }

            if (isset($phpErrorsLoggerConfig['actions']['mail']))
            {
                $mailConfig = isset($phpErrorsLoggerConfig['actions']['mail']['config']) ? $phpErrorsLoggerConfig['actions']['mail']['config'] : array();
                $mailAction = new \ErrorHandler\Action\SendMail($serviceManager, $mailConfig);
                $actions['mail'] = $mailAction;
            }

            $GLOBALS['errorHandler_actions'] = $actions;

            if (isset($phpErrorsLoggerConfig['render_view']) &&
                isset($phpErrorsLoggerConfig['render_view']['layout_view']) &&
                isset($phpErrorsLoggerConfig['render_view']['view']))
            {
                $viewRender = $serviceManager->get('ViewRenderer');

                $viewHelperManager = $serviceManager->get('viewhelpermanager');
                $layout = $viewHelperManager->get('layout');

                $GLOBALS['errorHandler_viewRenderer'] = array('viewRender' => $viewRender, 'layout' => $layout, 'layoutView' => isset($phpErrorsLoggerConfig['render_view']['layout_view']) ? $phpErrorsLoggerConfig['render_view']['layout_view'] : null, 'view' => $phpErrorsLoggerConfig['render_view']['view']);
            }

            require_once __DIR__ . '/../inc/e_errorhandler.php';
        }
        
    }

    private function _setPhpSettings($config)
    {
        $phpSettings = isset($config['phpSettings']) ? $config['phpSettings'] : array();
        if (!empty($phpSettings))
        {
            foreach($phpSettings as $key => $value)
            {
                if ($key == "date_default_timezone_set")
                    date_default_timezone_set($value);
                else if ($key == "display_errors")
                {
                    ini_set($key, (boolean)$value);
                    if ((boolean) $value === true)
                    {
                        if (isset($phpSettings['error_reporting_type']))
                            error_reporting($phpSettings['error_reporting_type']);
                        else
                            error_reporting(E_ALL | E_STRICT);                            
                    }
                }
                else if ($key != "time_limit")
                    ini_set($key, $value);
                else
                    set_time_limit((int) $value);
            }
        }
    }

    public function onError(MvcEvent $e)
    {
        $application = $e->getApplication();
        $serviceManager = $application->getServiceManager();

        if (!$e->isError())
            return;

        if ($e->getError() == 'error-unauthorized-controller')
            return;

        $error = $e->getError();
        $exception = $e->getParam('exception');

        $errorObject = new Model\Error();
        $errorObject->setError($error);
        $errorObject->setException($exception);

        $response = $e->getResponse();
        if ($response && method_exists($response, 'getStatusCode') && $response->getStatusCode() == 200)
            return $response; // probably AssetManager response

        if ($response && method_exists($response, 'getStatusCode'))
            $errorObject->setStatusCode($response->getStatusCode());

        $this->_processErrorObject($serviceManager, $errorObject);
    }

    private function _processErrorObject($serviceManager, Model\Error $error)
    {
        $config = $serviceManager->get('Configuration');

        $onErrorActions = (isset($config['zf_error_handler']) &&
            isset($config['zf_error_handler']['error_actions'])) ?
                $config['zf_error_handler']['error_actions'] :
                array();

        if (isset($onErrorActions[$error->getStatusCode()]))
            $actions = $onErrorActions[$error->getStatusCode()];
        else if (isset($onErrorActions['default']))
            $actions = $onErrorActions['default'];
        else
            $actions = array();

        foreach ($actions as $action => $actionParams)
        {
            if ($action == 'log')
                $actionClass = '\ErrorHandler\Action\Log';
            else if ($action == 'mail')
                $actionClass = '\ErrorHandler\Action\SendMail';

            $callableAction = new $actionClass($serviceManager, isset($actionParams['config']) ? $actionParams['config'] : array());

            $done = false;
            try {
                $done = $callableAction->run($error);
            }
            catch (\Exception $e)
            {
                $done = false;
            }
            return $done;
        }
    }
}
