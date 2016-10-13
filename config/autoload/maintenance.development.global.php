<?php

use Zend\Http\Response;

return [
    'maintenance' => [
        'message'     => 'Service unavailable',
        'status_code' => Response::STATUS_CODE_503,
        'flag_file'   => 'config/maintenance.flag',
        //'html'        => __DIR__ . '/../data/Maintenance.html',
    ],
];
