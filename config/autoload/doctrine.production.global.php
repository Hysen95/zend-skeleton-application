<?php

$config = array(
	'doctrine' => array(
			'connection' => array(
					// default connection name
					'orm_default' => array(
							'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
					)
			)
	),	
);

return $config;