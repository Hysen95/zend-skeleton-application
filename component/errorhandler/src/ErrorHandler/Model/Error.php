<?php

namespace ErrorHandler\Model;

class Error
{
	
    /**
     * @var string
     */
    protected $error;

    /**
     * @var integer
     */
    protected $statusCode = -1;

    /**
     * @var \Exception
     */
    protected $exception = false;


    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }
    public function getError()
    {
        return $this->error;
    }


    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    public function getStatusCode()
    {
        return $this->statusCode;
    }


    public function setException($exception)
    {
        $this->exception = $exception;
        return $this;
    }
    public function getException()
    {
        return $this->exception;
    }

    public function getLogAsString()
    {
        $ipAddress = null;
        if (array_key_exists('REMOTE_ADDR', $_SERVER))
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $tmp = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ipAddress = array_pop($tmp);
        }

        $log = "\r\n";
        $log .= '[DATETIME]: '.date('Y-m-d H:i:s')."\r\n";
        $log .= '[IPADDRESS]: '.$ipAddress."\r\n";
        $log .= '[REQUEST_URI]: '.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'NULL')."\r\n";
        $log .= '[HTTP_HOST]: '.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'NULL')."\r\n";
        $log .= '[HTTP_REFERER]: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'NULL')."\r\n";
        $log .= '[ERROR]: '.$this->getError()."\r\n";

        if ($this->getStatusCode() != -1)
            $log .= '[STATUSCODE]: '.$this->getStatusCode()."\r\n";
        
        if ($this->getException())
        {
            $exception = $this->getException();
            $log .= '[EXCEPTION_MESSAGE]: '.$exception->getMessage()."\r\n";
            $log .= '[EXCEPTION_TRACE]: '.$exception->getTraceAsString()."\r\n";

            $e = $exception->getPrevious();
            if ($e)
            {
                $log .= '[PREVIOUS_EXCEPTIONS]'."\r\n";
                do {
                    $log .= "\t".'[EXCEPTION_MESSAGE]: '.$e->getMessage()."\r\n";
                    $log .= "\t".'[EXCEPTION_TRACE]: '.$e->getTraceAsString()."\r\n";
                    $e = $e->getPrevious();
                } while ($e);
            }
        }

        $log .= "\r\n";

        return $log;
    }
}
 
 
