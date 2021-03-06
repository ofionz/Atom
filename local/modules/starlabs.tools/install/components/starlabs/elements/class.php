<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

use Bitrix\Main\Loader;
use Starlabs\Tools\Bbc\BasisRouter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

if (!Loader::includeModule('starlabs.tools')) {
	return false;
}

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ElementsRouter extends BasisRouter
{
	protected $defaultSefPage = 'index';

	protected function setSefDefaultParams()
	{
		$this->defaultUrlTemplates404 = [
			'index' => '',
			'section' => '#SECTION_ID#/',
			'detail' => '#SECTION_ID#/#ELEMENT_ID#/'
		];

		$this->componentVariables = [
			'SECTION_ID',
			'SECTION_CODE',
			'ELEMENT_ID',
			'ELEMENT_CODE'
		];
	}
}