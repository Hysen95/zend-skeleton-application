<?php

$mail = [];

$mail["config"] = 
[
		'fromEmail' => 'noreply@tesoridelgusto.it',
		'fromName' => 'Error Handler',
		'SMTP' => [
				'enabled' => true,
				'config' => [
						'name' => 'fast.smtpok.com',
						'host' => 'fast.smtpok.com',
						'port' => 1025,
						'connection_class' => 'login',
						'connection_config' => [
								'username' => 's61170_7',
								'password' => 'IQEIO-I2UU',
								'ssl' => 'tls'
						]
				]
		],
		'toEmails' => [
				"overflowrish@gmail.com"
		]
];

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
						404 => [ 
								'log' => [ 
										'config' => [ 
												'priority' => \Zend\Log\Logger::INFO,
												'stream' => __DIR__ . '/../../../data/logs/error404_' . date ( 'Y-m-d' ) . '.log' 
										] 
								] 
						],
						500 => [ 
								'log' => [ 
										'config' => [ 
												'priority' => \Zend\Log\Logger::ERR,
												'stream' => __DIR__ . '/../../../data/logs/' . date ( 'Y-m-d' ) . '.log' 
										] 
								],
								'mail' => [ 
										'config' => $mail["config"],
								] 
						],
						'default' => [ 
								'log' => [ 
										'config' => [ 
												'priority' => \Zend\Log\Logger::WARN,
												'stream' => __DIR__ . '/../../../data/logs/default_' . date ( 'Y-m-d' ) . '.log' 
										] 
								] 
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
								'mail' => [
										'config' => $mail["config"],
								] 
						] 
				] 
		] 
];

return $config;
