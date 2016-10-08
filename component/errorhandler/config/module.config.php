<?php

$mailPath = __DIR__ . '/../../../config/autoload/errorhandler.mail.local.php';

$mail = NULL;

if (file_exists($mailPath)) {
	$includedMailConfig = include $mailPath;
	if (is_array($includedMailConfig) && !empty($includedMailConfig)) {
		$mail = $includedMailConfig;
	}
}	

$config = [ 
		
		'view_manager' => [ 
				'display_not_found_reason' => true,
				'display_exceptions' => true,
		],
		
		'phpSettings' => [ 
				'display_startup_errors' => true,
				'display_errors' => true,
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
						] 
				] 
		] 
];

return $config;
