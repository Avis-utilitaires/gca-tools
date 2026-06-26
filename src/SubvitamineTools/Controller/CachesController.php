<?php
/**
 * Created by PhpStorm.
 * User: ddattee
 * Date: 29/06/2015
 * Time: 17:29
 */

namespace SubvitamineTools\Controller;


use General\Utils\Filesystem\Dir;
use Zend\Cache\StorageFactory;
use Zend\View\Model\ViewModel;

class CachesController extends AbstractController
{

	public function listAction()
	{
		$config = $this->getServiceLocator()->get('config');
		return array(
			'caches' => $config['cache_manager']['caches']
		);
	}

	public function emptyAction()
	{
		$caches = $this->getServiceLocator()->get('config')['cache_manager']['caches'];
		$empty = false;
		$msg = '';
		if ($this->getRequest()->isPost()) {
			$indexCache = (int)$this->getRequest()->getPost('index');
			if (!is_null($indexCache) && isset($caches[(int)$indexCache])) {
				$cache = $caches[(int)$indexCache];
				try {
					if (is_dir($cache['path'])) {
						$size_before = $size_before = Dir::dirSize($cache['path']);
						$empty = $this->loadCacheStorage($cache)->flush();
					} else {
						$msg = 'Le dossier à nettoyé n\'existe pas';
					}
					$size_after = Dir::dirSize($cache['path']);
				} catch (\Exception $e) {
					$msg = $e->getMessage();
				}
			}
		}
		$viewModel = new ViewModel();
		$viewModel
			->setVariables(array(
				'result' => $empty,
				'folder' => realpath($cache['path']),
				'spared' => number_format(($size_before - $size_after) / 1000, 1, ',', ' '),
				'msg' => $msg
			))
			->setTerminal(true);
		return $viewModel;
	}

	public function cleanAction()
	{
		$caches = $this->getServiceLocator()->get('config')['cache_manager']['caches'];
		$indexCache = (int)$this->getRequest()->getparam('id', null);
		$clean = false;

		if (!is_null($indexCache) && isset($caches[(int)$indexCache])) {
			$cache = $caches[(int)$indexCache];
			$size_before = $size_before = Dir::dirSize($cache['path']);
			$clean = $this->loadCacheStorage($cache)->clearExpired();
			$size_after = Dir::dirSize($cache['path']);
		}

		$viewModel = new ViewModel();
		$viewModel
			->setVariables(array(
				'result' => $clean,
				'folder' => realpath($cache['path']),
				'spared' => number_format(($size_before - $size_after) / 1000, 2, ',', ' ')
			))
			->setTerminal(true);
		return $viewModel;
	}

	private function loadCacheStorage($cache)
	{
		$cacheStorage = StorageFactory::factory(array(
			'adapter' => array(
				'name' => $cache['type'],
				'options' => array(
					'cache_dir' => realpath($cache['path']),
					'ttl' => 1
				),
			),
			'plugins' => array(
				'exception_handler' => array(
					'throw_exceptions' => true
				)
			),
		));
		return $cacheStorage;
	}

}