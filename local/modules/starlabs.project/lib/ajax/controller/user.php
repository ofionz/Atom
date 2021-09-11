<?php

namespace Starlabs\Project\Ajax\Controller;

use http\Exception;
use Starlabs\Tools\Ajax\Controller\Prototype;
use Starlabs\Tools\Ajax\View\Xml;
use Bitrix\Main\Context;
use Starlabs\Tools\Helpers\IBlockPrototype;



class User extends Prototype
{
    public function getManagerListAction()
    {
        $this->view = new Xml();
        $departments = htmlspecialchars($this->request->getQuery("departments"));
        if ($departments) {
            $departments = explode(';', $departments);
        } else {
            throw new \Exception('Parameters exception: No such parameters. Add department!');
        }

        if (!$departments || !count($departments)) {
            throw new \Exception('Parameters exception: No such parameters. Add department!');
        }

        if (\Bitrix\Main\Loader::IncludeModule('crm')) {
            $arTotalDepartment = null;
            $saveMarginRight = null;
            $saveDepthLevel = null;

            $rsSection = \CIBlockSection::GetTreeList(
                ['IBLOCK_ID' => 5, 'ACTIVE' => 'Y'],
                ['DEPTH_LEVEL', 'ID', 'RIGHT_MARGIN']
            );
            while ($arSection = $rsSection->Fetch()) {
                if (in_array($arSection['ID'], $departments)) {
                    $arTotalDepartment[] = $arSection['ID'];
                    $saveMarginRight = $arSection['RIGHT_MARGIN'];
                    $saveDepthLevel = $arSection['DEPTH_LEVEL'];
                } elseif (
                    (is_null($saveMarginRight) === false && is_null($saveDepthLevel) === false) &&
                    ($saveDepthLevel < $arSection['DEPTH_LEVEL'] && $saveMarginRight > $arSection['RIGHT_MARGIN'])
                ) {
                    $arTotalDepartment[] = $arSection['ID'];
                } else {
                    $saveMarginRight = null;
                    $saveDepthLevel = null;
                }
            }

          if (!$arTotalDepartment) {
              throw new \Exception('Parameters exception: Wrong department!');
          }
            $arFilter = [
                "UF_DEPARTMENT" => $arTotalDepartment,
                'ACTIVE' => 'Y',
            ];
            if (!empty($execute)) {
                $arFilter = array_merge($arFilter, ['!ID' => $execute]);
            }
            $resUser = \Bitrix\Main\UserTable::getList(
                [
                    "filter" => $arFilter,
                    "select" => [
                        "ID",
                        "NAME",
                        "SECOND_NAME",
                        "LAST_NAME",
                    ],
                    'cache' => [
                        'ttl' => 60 * 30,
                        'cache_joins' => true
                    ]
                ]
            );


            return $resUser->fetchAll();
        }
    }


}