<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace SubvitamineTools\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class WebhookController extends AbstractActionController
{
	public function indexAction()
	{

	}

	public function runAction()
	{
		error_reporting(E_ALL);
		ignore_user_abort(true);

		$view = new JsonModel();
		$trace = [];
		$errors = '';
		$status = 0;

		$trace[] = "Mise en " . APPLICATION_ENV . " de modification";
		$trace[] = 'Appellé depuis ' . $_SERVER['REMOTE_ADDR'];

		if ($this->getRequest()->isPost()) {
			try {
				$this->runPull($trace, $status, $errors);
			} catch (\Exception $e) {
				$status = $e->getCode();
				$errors = $e->getMessage();
			}
		} else {
			$status = -100;
			$errors = 'Le service de mis à jour ne peut être appellé qu\'en interne.';
		}
		$view->setVariables([
			'status' => $status,
			'details' => $trace,
			'errors' => $errors
		]);
		return $view;
	}

	/**
	 * Run Git pull, require that Git Pull can be called by PHP
	 *
	 * @param array $trace Array containing message for trace purpose
	 * @param int $status Status of the result
	 * @param string $errors Error msg to return
	 * @return int
	 */
	private function runPull(&$trace, &$status, &$errors)
	{
		//Set the command to call
		$cmd = "sudo /usr/bin/git pull";
		if (APPLICATION_ENV == 'staging') {
			//Si en staging on ne tire que la branche de staging
			$cmd .= ' origin +refs/heads/staging:refs/remotes/origin/staging';
		} else if (APPLICATION_ENV == 'production') {
			//Si en production on ne tire que la branche de master
			$cmd .= ' origin +refs/heads/prod:refs/remotes/origin/prod';
		}
		//Locate the repository
		$cwd = realpath(APPLICATION_PATH);
		if (file_exists($cwd . '/.git')) {
			//Run the command
			$descriptorspec = array(1 => array('pipe', 'w'), 2 => array('pipe', 'a'));
			$trace[] = 'Lancement de la mise à jour';
			$resource = proc_open($cmd, $descriptorspec, $pipes, $cwd);
			if (is_resource($resource)) {
				$output = stream_get_contents($pipes[1]);
				$errors = stream_get_contents($pipes[2]);
				fclose($pipes[1]);
				fclose($pipes[2]);
				proc_close($resource);

				if (!empty($output))
					$trace[] = $output;
				if (!empty($errors)) {
					$status = -210;
					$errors = $errors;
				}
			} else {
				$status = -110;
				$errors = 'Problème lors de la commande de mise à jour.';
			}
		} else {
			$status = -120;
			$errors = 'Le projet ne semble pas être une repository Git assurez vous de la présence d\'un dossier .git à la racine du projet.';
		}

		return $status;
	}
}
