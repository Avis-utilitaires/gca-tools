<?php
/**
 * Created by PhpStorm.
 * User: ddattee
 * Date: 29/06/2015
 * Time: 17:29
 */

namespace SubvitamineTools\Controller;


use General\Utils\Cli;
use Vendors\Getext;
use Zend\View\Model\ViewModel;

class UtilsController extends AbstractController
{

	public function composerAction()
	{

	}

	public function composerUpdateAction()
	{
		//Init composer home for the project
		$comp_home = APPLICATION_PATH . '/.composer';
		is_dir($comp_home) || @mkdir($comp_home);
		putenv("COMPOSER_HOME=" . $comp_home);
		//Find which composer to use
		$cmd = 'composer';
		$manual = APPLICATION_PATH . '/composer.phar';
		if (Cli::cmdExists($cmd) || file_exists($manual)) {
			$errors = '';
			$out = '';
			$cmd = (Cli::cmdExists($cmd) ? $cmd : $manual);
			$res = Cli::cmd($cmd . ' update -d ' . APPLICATION_PATH, APPLICATION_PATH, $out, $errors);
			$out = "Composer Home : " . $comp_home . "\n" . $out;
			$out = "Using " . (Cli::cmdExists($cmd) ? $cmd : $manual) . "\n" . $out;
		}

		$viewModel = new ViewModel();
		$viewModel
			->setVariables(array(
				'code' => $res,
				'output' => $out,
				'errors' => $errors
			))
			->setTerminal(true);
		return $viewModel;
	}

	public function translationsAction()
	{
		return array(
			'toconvert' => $this->loadTranslationStorage()
		);
	}

	public function translationsConvertAction()
	{
		$generation = false;
		$msg = '';
		$files = array();
		$toconvert = $this->loadTranslationStorage();
		if ($this->getRequest()->isPost()) {
			$indexCache = $this->getRequest()->getPost('index');
			if ($indexCache == 'all' || isset($toconvert[(int)$indexCache])) {
				if ($indexCache == 'all') {
					foreach ($toconvert as $path) {
						$this->generateMos($path, $files, $generation);
						$folders[] = $path;
					}
				} else {
					$this->generateMos($toconvert[(int)$indexCache], $files, $generation);
					$folders[] = $toconvert[(int)$indexCache];
				}
			}
		}
		$viewModel = new ViewModel();
		$viewModel
			->setVariables(array(
				'result' => $generation,
				'folders' => ($indexCache == 'all' ? $toconvert : array($toconvert[(int)$indexCache])),
				'files' => $files,
				'msg' => $msg
			))
			->setTerminal(true);
		return $viewModel;
	}

	private function generateMos($path, &$files, &$generation)
	{
		$convertor = new Getext();
		foreach (new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS) as $file) {
			if ($file->isFile() && $file->getExtension() == 'po') {
				if ($convertor->phpmo_convert(realpath($file->getPathname()))) {
					$files[] = str_replace('po', 'mo', $file->getFilename());
					$generation = true;
				}
			}

		}
		return $generation;
	}

	/**
	 * Extract the translation path from the config
	 * @return array
	 */
	private function loadTranslationStorage()
	{
		$toconvert = array();
		$config = $this->getServiceLocator()->get('config');
		foreach ($config['translator']['translation_file_patterns'] as $translator) {
			if ($translator['type'] == 'gettext') {
				if (isset($translator['base_dir'])) {
					$toconvert[] = $translator['base_dir'];
				}
			}
		}
		return $toconvert;
	}

}