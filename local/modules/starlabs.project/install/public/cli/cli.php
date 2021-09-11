#!/usr/bin/php
<?php

/**
 * Сделал символическую ссылку и дал права на запуск
 * ln -s /home/bitrix/ext_www/name.ru/cli/cli.php /usr/local/bin/cli
 * chmod 0755 /usr/local/bin/cli
 *
 *      Добавить в крон команду:
 *      0 10 * * 1 bitrix /usr/local/bin/cli __КОМАНДА__ _ПАРАМЕТРЫ_
 *
 * @link https://crontab.guru/
 * @link https://symfony.com/doc/current/components/console.html
 * @link https://code.tutsplus.com/ru/tutorials/how-to-create-custom-cli-commands-using-the-symfony-console-component--cms-31274
 */
$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__ . '/../');
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_NO_ACCELERATOR_RESET", true);
define("BX_CRONTAB", true);
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC", "Y");
define("DisableEventsCheck", true);
define("NO_AGENT_CHECK", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

// default location of composer.json
$composerJsonFile = $_SERVER["DOCUMENT_ROOT"] . '/bitrix/composer.json';

// custom location of composer.json from .settings.php
$composerSettings = \Bitrix\Main\Config\Configuration::getValue('composer');
if (!empty($composerSettings['config_path'])) {
	$jsonPath = $composerSettings['config_path'];
	$jsonPath = ($jsonPath{0} == '/')
		? $jsonPath // absolute
		: realpath($_SERVER["DOCUMENT_ROOT"] . '/' . $jsonPath); // relative

	if (!empty($jsonPath)) {
		$composerJsonFile = $jsonPath;
	}
}

// default vendor path has the same parent dir as composer.json has
$vendorPath = dirname($composerJsonFile) . '/vendor';

if (file_exists($composerJsonFile) && is_readable($composerJsonFile)) {
	$jsonContent = json_decode(file_get_contents($composerJsonFile), true);

	if (isset($jsonContent['config']['vendor-dir'])) {
		$vendorPath = realpath(dirname($composerJsonFile) . DIRECTORY_SEPARATOR . $jsonContent['config']['vendor-dir']);

		if ($vendorPath === false) {
			throw new \Bitrix\Main\SystemException(sprintf(
				'Failed to load vendor libs from %s, path \'%s\' is not readable',
				$composerJsonFile, $jsonContent['config']['vendor-dir']
			));
		}
	}
}
require $vendorPath . '/autoload.php';

$application = new Symfony\Component\Console\Application();
$application->add(new StarLabs\Project\Cli\HelloCli());
$application->run();
