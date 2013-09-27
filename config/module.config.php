<?php

namespace JVPhpThumb;

return array(
	'router' => array(
		'routes' => array(
			'thumbnail' => array(
            	'type' => 'Segment',
            	'options' => array(
            		'route'    => '/thumbnail[/:folder][/:width][/:height][/:cropratio][/:image]',
            		'defaults' => array(
            			'__NAMESPACE__' => 'JVPhpThumb\Controller',
            			'controller'    => 'Index',
            			'action'        => 'index',
            		),
            	),
            ),
		),
	),
	'controllers' => array(
		'invokables' => array(
			'JVPhpThumb\Controller\Index' => 'JVPhpThumb\Controller\IndexController',
		),
	),
);