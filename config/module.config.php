<?php

return array(
	'controllers' => array(
		'invokables' => array(
			'SubvitamineTools\Controller\Index' => 'SubvitamineTools\Controller\IndexController',
			'SubvitamineTools\Controller\Utils' => 'SubvitamineTools\Controller\UtilsController',
			'SubvitamineTools\Controller\Caches' => 'SubvitamineTools\Controller\CachesController',
			'SubvitamineTools\Controller\Webhook' => 'SubvitamineTools\Controller\WebhookController'
		),
	),
	'router' => array(
		'routes' => array(
			'tools' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/tools/[:controller[/:action]]',
					'defaults' => array(
						'__NAMESPACE__' => 'SubvitamineTools\Controller',
						'controller' => 'Index',
						'action' => 'index',
					),
					'constraints' => array(
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
					),
				)
			)
		)
	),
	'module_layouts' => array(
		'SubvitamineTools' => 'layout/clean.phtml'
	),
	'view_manager' => array(
		'template_path_stack' => array(
			'tools' => __DIR__ . '/../view',
		),
		'strategies' => array(
			'ViewJsonStrategy',
		),
	),
	'cache_manager' => array(
		'caches' => array(),
	),
	'tools' => array(
		'ips' => array()
	)
//	'asset_bundle' => array(
//		'assets' => array(
//			'SubvitamineTools' => array(
//				'css' => array(
//					'http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css',
//					'http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'
//				),
//				'js' => array(
//					'http://oss.maxcdn.com/respond/1.4.2/respond.min.js',
//					'http://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js',
//					'http://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js',
//					'http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js',
//				),
//				'media' => array(
//					'@zfRootPath/vendor/subvitamine-dev/subvitamine-tools/resources/img/loader.gif',
//				)
//			)
//		),
//	),
);
