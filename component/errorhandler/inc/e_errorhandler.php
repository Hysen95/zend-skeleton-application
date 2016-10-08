<?php
// ./e_errorhandler.php in root of ZF2 app
//adapt from http://stackoverflow.com/questions/277224/how-do-i-catch-a-php-fatal-error

define('E_FATAL',  E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR |
        E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
 
//define('DISPLAY_ERRORS', TRUE);
define('ERROR_REPORTING', ini_get('error_reporting'));
 
register_shutdown_function('shut');
set_error_handler('handler');
 
//catch function
function shut()
{
    $error = error_get_last();
    if ($error && ($error['type'] & E_FATAL)) {
        handler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}
 
function handler($errno, $errstr, $errfile, $errline)
{
    $typestr = 'errorno:'.$errno;
    switch ($errno) {
 
        case E_ERROR: // 1 //
            $typestr = 'E_ERROR'; break;
        case E_WARNING: // 2 //
            $typestr = 'E_WARNING'; break;
        case E_PARSE: // 4 //
            $typestr = 'E_PARSE'; break;
        case E_NOTICE: // 8 //
            $typestr = 'E_NOTICE'; break;
        case E_CORE_ERROR: // 16 //
            $typestr = 'E_CORE_ERROR'; break;
        case E_CORE_WARNING: // 32 //
            $typestr = 'E_CORE_WARNING'; break;
        case E_COMPILE_ERROR: // 64 //
            $typestr = 'E_COMPILE_ERROR'; break;
        case E_CORE_WARNING: // 128 //
            $typestr = 'E_COMPILE_WARNING'; break;
        case E_USER_ERROR: // 256 //
            $typestr = 'E_USER_ERROR'; break;
        case E_USER_WARNING: // 512 //
            $typestr = 'E_USER_WARNING'; break;
        case E_USER_NOTICE: // 1024 //
            $typestr = 'E_USER_NOTICE'; break;
        case E_STRICT: // 2048 //
            $typestr = 'E_STRICT'; break;
        case E_RECOVERABLE_ERROR: // 4096 //
            $typestr = 'E_RECOVERABLE_ERROR'; break;
        case E_DEPRECATED: // 8192 //
            $typestr = 'E_DEPRECATED'; break;
        case E_USER_DEPRECATED: // 16384 //
            $typestr = 'E_USER_DEPRECATED'; break;
    }
    
    if (isset($_SERVER['REQUEST_URI']))
        $message = " Error PHP in file : ".$errfile." at line : ".$errline." with type error : ".$typestr." : ".$errstr." in ".$_SERVER['REQUEST_URI'];
    else
        $message = " Error PHP in file : ".$errfile." at line : ".$errline." with type error : ".$typestr." : ".$errstr;
 
    if(!($errno & ERROR_REPORTING)) {
        return;
    }

    if (error_reporting() == 0)
        return;

    if (isset($GLOBALS['errorHandler_actions']) && is_array($GLOBALS['errorHandler_actions']))
    {
        $actions = $GLOBALS['errorHandler_actions'];
    }
    else
        $actions = array();

    foreach ($actions as $action)
    {
        if ($action instanceof \ErrorHandler\Action\Log)
        {
            $action->getLogger()->crit("\r\n".$message."\r\n");
        }
        else if ($action instanceof \ErrorHandler\Action\SendMail)
        {
            $subject = '[error] PHP error: '.$typestr;

            $text = new \Zend\Mime\Part($message);
            $text->type = "text/plain";

            $body = new \Zend\Mime\Message();
            $body->setParts(array($text));

            $message = new \Zend\Mail\Message();
            $message->setEncoding('UTF-8');
            $message->setSubject($subject);
            $message->setBody($body);

            $action->sendMessage($message);
        }
    }

    if (isset($GLOBALS['errorHandler_viewRenderer']))
        $renderedView = $GLOBALS['errorHandler_viewRenderer'];
    else
        $renderedView = false;

    if ($renderedView !== false && $typestr == 'E_ERROR')
    {
        $layout = $renderedView['layout'];
        $viewRender = $renderedView['viewRender'];

        $viewModel = new \Zend\View\Model\ViewModel();
        $viewModel->setTemplate($renderedView['view']);

        $layout()->addChild($viewModel);
        if (!empty($renderedView['layoutView']))
            $layout()->setTemplate($renderedView['layoutView']);
        $layout()->setVariable("content", $viewRender->render($viewModel));

        $layout()->setOption('has_parent', true);

        echo $viewRender->render($layout());
        die();
    }
    
} 
