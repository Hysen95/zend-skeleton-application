<?php

$mailPath = __DIR__ . '/../../../config/autoload/errorhandler.mail';

$currentEnv = defined("APP_ENV") ? "." . APP_ENV : "";

$mailPath .= $currentEnv . ".local.php";

$mail = NULL;

if (file_exists($mailPath)) {
	$includedMailConfig = include $mailPath;
	if (is_array($includedMailConfig) && !empty($includedMailConfig)) {
		$mail = $includedMailConfig;
	}
}

$config = [ 
		
		'view_manager' => [ 
				'display_not_found_reason' => (defined("APP_ENV") && APP_ENV != "production"),
				'display_exceptions' => (defined("APP_ENV") && APP_ENV != "production"),
		        'not_found_template'       => 'error/404',
		        'exception_template'       => 'error/index',
		],
		
		'phpSettings' => [ 
				'display_startup_errors' => (defined("APP_ENV") && APP_ENV != "production"),
				'display_errors' => (defined("APP_ENV") && APP_ENV != "production"),
				"error_reporting_type" => E_ALL,
				"date_default_timezone_set" => "Europe/Rome", 
		],
		
		'zf_error_handler' => [ 
				'error_actions' => [ 
						'404' => [ 
								'log' => [ 
										'config' => [ 
												'priority' => \Zend\Log\Logger::INFO,
												'stream' => __DIR__ . '/../../../data/logs/error404_' . date ( 'Y-m-d' ) . '.log' 
										] 
								],
								'mail' => $mail,
						],
						'500' => [ 
								'log' => [ 
										'config' => [ 
												'priority' => \Zend\Log\Logger::ERR,
												'stream' => __DIR__ . '/../../../data/logs/' . date ( 'Y-m-d' ) . '.log' 
										] 
								],
								'mail' => $mail,
						],
						'default' => [ 
								'log' => [ 
										'config' => [ 
												'priority' => \Zend\Log\Logger::WARN,
												'stream' => __DIR__ . '/../../../data/logs/default_' . date ( 'Y-m-d' ) . '.log' 
										] 
								],
								'mail' => $mail,
						] 
				],
				'php_errors_handler' => [ 
						'handle_errors' => true,
						'actions' => [ 
								'log' => [ 
										'config' => [ 
												'stream' => __DIR__ . '/../../../data/logs/phpErrors_' . date ( 'Y-m-d' ) . '.log' 
										] 
								],
								'mail' => $mail,
						],
			            'render_view' => [
			                'layout_view' => 'layout/layout',
			                'view' => 'error/index'
			            ],
				],
		] 
];

return $config;
