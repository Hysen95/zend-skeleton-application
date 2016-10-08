<?php

namespace ErrorHandler\Action;

use ErrorHandler\Model\Error;

interface ActionInterface
{
    /**
     * Set the configuration array for the action object
     * @abstract
     * @param array|null $config
     */
    public function setConfig($config);

    /**
     * @abstract
     * @param Error $error
     * @return bool
     */
    public function run(Error $error);
}