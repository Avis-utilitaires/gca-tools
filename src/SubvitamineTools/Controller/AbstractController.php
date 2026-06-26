<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace SubvitamineTools\Controller;

use Zend\Mvc\MvcEvent;

abstract class AbstractController extends \General\Controller\AbstractController
{

	public function preDispatch(MvcEvent $e)
	{
		$config_tools = $this->getServiceLocator()->get('config')['tools'];
		if (!in_array($_SERVER['REMOTE_ADDR'], $config_tools['ips'])) {
			return $this->unauthorized($e);
		}

	}

	protected function unauthorized($e)
	{
		$response = $e->getResponse();
		$response->setStatusCode(403);
		$response->setContent('Vous n\'avez pas les autorisations n&eacute;cesaire pour acc&eacute;der &agrave; cette page.');
		return $response;
	}

}
