<?php
namespace Starlabs\Project;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Exception;
use Starlabs\Tools\Ajax\RequireModule;

/**
 * Основной класс модуля
 */
class Module
{
	/**
	 * Обработчик начала отображения страницы
	 * @return void
	 * @throws Exception
	 * @throws LoaderException
	 */
	public static function onPageStart()
	{
		if (!Loader::includeModule('starlabs.tools')) {
			throw new Exception('Module "starlabs.tools" was not installed.');
		}

		//Регистрация возможности обрабатывать запросы на адрес /ajax/ данным модулем
		RequireModule::getInstance()->add('starlabs.project');

		self::defineConstants();
		self::setupEventHandlers();
	}

	/**
	 * Задает константы проекта
	 */
	protected static function defineConstants()
   {

   }

	/**
	 * Обработчики событий.
	 */
	protected static function setupEventHandlers()
	{
//        Events\Example::setHandlers();
	}
}
