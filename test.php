<?php $_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';
use Bitrix\Main\Loader;

define('NOT_CHECK_PERMISSIONS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

Loader::IncludeModule('crm');

$res = \CCrmDeal::GetStageUpdatePermissionType('WIN');

\Starlabs\Tools\Helpers\p::init($res);
?>