<?php
namespace Starlabs\Project;

use Bitrix\Main\Event;

const BASE_DIR = __DIR__;

//@todo Изучить вопрос событий https://dev.1c-bitrix.ru/api_d7/bitrix/main/EventManager/index.php
$event = new Event('starlabs.project', 'onModuleInclude');
$event->send();
