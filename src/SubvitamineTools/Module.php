<?php

namespace SubvitamineTools;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\ModuleRouteListener;

class Module implements
	ConfigProviderInterface,
	BootstrapListenerInterface,
	AutoloaderProviderInterface
{
	/**
	 * @inheritdoc
	 */
	public function getConfig()
	{
		return include __DIR__ . '/../../config/module.config.php';
	}

	/**
	 * @inheritdoc
	 */
	public function onBootstrap(EventInterface $e)
	{
		$app = $e->getApplication();
		$em = $app->getEventManager();
//		$sm  = $app->getServiceManager();

		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($em);

		$this->_initLayout($e);
		$this->_initAssets();
	}

	/**
	 * @inheritdoc
	 */
	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/../' . __NAMESPACE__
				),
			),
		);
	}

	/**
	 * Init layout config to allow per-module layout
	 * @param MvcEvent $e
	 */
	protected function _initLayout($e)
	{
		$e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function ($e) {
			$controller = $e->getTarget();
			$controllerClass = get_class($controller);
			$moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
			$config = $e->getApplication()->getServiceManager()->get('config');
			if (isset($config['module_layouts'][$moduleNamespace])) {
				$controller->layout($config['module_layouts'][$moduleNamespace]);
			}
		}, 100);
	}

	/**
	 * Init asset symlink to public folder so that we can access module resources through http://domain.tld/modules/module-name/...
	 */
	protected function _initAssets()
	{
		if (defined('PUBLIC_PATH')) {
			//Init public path for vendors modules
			$public_assets = PUBLIC_PATH . '/modules';
			is_dir($public_assets) || @mkdir($public_assets);
			//Symlink current module assets to public
			$public_assets .= '/' . basename(realpath(__DIR__ . '/../..'));
			$module_assets = realpath(__DIR__ . '/../../resources');
			if (is_dir($module_assets) && !file_exists($public_assets)) {
//				`ln -s $module_assets $public_assets`;
			}
		}
	}

}
