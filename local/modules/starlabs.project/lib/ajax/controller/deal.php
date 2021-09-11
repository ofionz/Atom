<?php

namespace Starlabs\Project\Ajax\Controller;

use CUserFieldEnum;
use http\Exception;
use Starlabs\Tools\Ajax\Controller\Prototype;
use Starlabs\Tools\Ajax\View\Xml;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Starlabs\Tools\Helpers\IBlockPrototype;
use Bitrix\Crm\DealTable;


class Deal extends Prototype
{
    public function __construct()
    {

        if (Loader::IncludeModule('crm')) {
            parent::__construct();
        } else {
            throw new \Exception ('Module CRM error');
        }
    }

    public function getDealListAction()
    {
        $this->view = new Xml();
        $phone = htmlspecialchars($this->request->getQuery("phone"));
        $managers = htmlspecialchars($this->request->getQuery("manager"));
        $period = htmlspecialchars($this->request->getQuery("period"));

        if (!$phone && !$managers && !$period) {
            throw new \Exception('Parameters exception: No such parameters. Add phone, manager or period.');
        }
        $filter['CHECK_PERMISSIONS'] = 'N';
        $filter ['STAGE_ID'] = 'FINAL_INVOICE';     // Стадия сделки - заявление
        $filter ['UF_CRM_1626448401'] = false;      // Пользовательское поле номер паспорта в 1С - не заполнено
        $select = ['ID', 'TITLE', 'ASSIGNED_BY_ID', "OPPORTUNITY", 'DATE_CREATE', 'UF_CRM_1573802636556'];
        if ($phone && count($phones = explode(';', $phone))) {

            foreach ($phones as $key => $phone) {
                $phone = trim(str_replace ('+', '', $phone ));
                if (substr($phone,0,1) === '8' || substr($phone,0,1) === '7' ) {
                    $phone = substr($phone,1);
                }

                $parsedPhone = \Bitrix\Main\PhoneNumber\Parser::getInstance()->parse($phone);
                if ($parsedPhone->isValid()) {
                    $phoneVariants = [];
                    $phoneVariants[] = $parsedPhone->format('');
                    $phoneVariants[] = $parsedPhone->format('E.164', true);
                } else {
                    throw new \Exception('Parameters exception: Phone - valiadation exception.');
                }


                $dealByPhone = [];
                foreach ($phoneVariants as $var) {
                    $criterion = new \Bitrix\Crm\Integrity\DuplicateCommunicationCriterion('PHONE', $var);
                    $duplicate = $criterion->find(\CCrmOwnerType::Contact, 20);
                    if ($duplicate !== null) {
                        $entities = $duplicate->getEntities();
                        foreach ($entities as $entity) {
                            $dealByPhone = array_merge(
                                $dealByPhone,
                                \Bitrix\Crm\Binding\DealContactTable::getContactDealIDs(
                                    $entity->getEntityID()
                                )
                            );
                        }
                    }
                }
                $dealByPhone = array_unique($dealByPhone);
                if (!count($dealByPhone)) {
                    $filter ['ID'][] =  ($key + 1)*-1;
                }
                foreach ($dealByPhone as $elem) {
                    $filter ['ID'][] = $elem;
                }
            }
        }

        if ($managers && count($managers = explode(';', $managers))) {
            $filter ['ASSIGNED_BY_ID'] = $managers;
        }
        if ($period && count($period = explode(';', $period))) {
            if ($period[0] && !$period[1]) {
                $startDate = $period[0];
                $endDate = $period[0];
            } elseif ($period[0] && $period[1]) {
                $startDate = $period[0];
                $endDate = $period[1];
            } else {
                throw new \Exception('Parameters exception: Period - too much parameters!');
            }

            try {
                $filter ['>DATE_CREATE'] = (new \Bitrix\Main\Type\DateTime($startDate))->setTime(0, 0, 0);
                $filter ['<DATE_CREATE'] = (new \Bitrix\Main\Type\DateTime($endDate))->setTime(23, 59, 59);
            } catch (\Bitrix\Main\Object\Exception $err) {
                throw new \Exception('Parameters exception: Period - parsing date error!');
            }
        }
//        \CCrmFieldMulti::
        $res = \CCrmDeal::GetList(
            [],
            $filter,
            $select,
            false
        );
        $deals = [];
        $i = 0;
        while ($row = $res->fetch()) {
            $index = 'contract__'.$i;
            $objects = [];
          foreach ($row['UF_CRM_1573802636556'] as $indx => $objCode)  {
              $objects[] = (new CUserFieldEnum)->GetList([], ['ID'=>$objCode])->Fetch()['VALUE'];
//              $objects['object_'.$indx] = (new CUserFieldEnum)->GetList([], ['ID'=>$objCode])->Fetch()['VALUE'];
          }
          unset($row['UF_CRM_1573802636556']);
            $deals [$index] = $row;
            $deals [$index]['OBJECTS'] = $objects;
            $deals [$index]['URL'] = 'https://bitrix.atomsk.ru/crm/deal/details/' . $row['ID'] . '/';
            $contactIds = \Bitrix\Crm\Binding\DealContactTable::getDealContactIDs($row['ID']);
            foreach ($contactIds as $key => $conId) {
                $indexCont = 'contact__'.$key;
                $arFilter = [
                    'ENTITY_ID' => 'CONTACT',
                    'ELEMENT_ID' => $conId,
                    'TYPE_ID' => 'PHONE',
                ];

                $arContact = \CCrmContact::GetByID($conId, false);
                $deals [$index]['CONTACTS'][$indexCont]['FULL_NAME'] = $arContact['FULL_NAME'];

                $resPhones = \CCrmFieldMulti::GetListEx([], $arFilter, false, [], ['VALUE']);
                $k = 0;
                while ($contactPhone = $resPhones->fetch()['VALUE']) {
                    $deals [$index]['CONTACTS'][$indexCont]['PHONES']['phone__' . $k] = $contactPhone;
                    $k++;
                }
            }
            $i++;
        }
        return $deals;
    }

    public function setDealLinkAction()
    {
        $this->view = new Xml();
        $dealId = htmlspecialchars($this->request->getQuery("deal_id"));
        $passportId = htmlspecialchars($this->request->getQuery("passport_id"));
        if (!$dealId || !$passportId) {
            throw new \Exception('Parameters exception: No such parameters. Add deal_id and passport_id.');
        }
        $filter['CHECK_PERMISSIONS'] = 'N';
        $filter['ID'] = $dealId;
        $select = ['ID', 'UF_CRM_1626448401'];
        $deal = \CCrmDeal::GetList([], $filter, $select)->Fetch();
        if (!$deal) {
            throw new \Exception('Parameters exception: deal not found');
        }
        if ($deal['UF_CRM_1626448401']) {
            throw new \Exception('Parameters exception: passport id already exist in this deal');
        }
        $fields = ['UF_CRM_1626448401' => $passportId];
        $result = (new \CCrmDeal(false))->Update($dealId, $fields);
        if (!$result)   throw new \Exception('Update exception! Can not update deal');

        return true;

    }

    public function setDealStageAction() {
        $this->view = new Xml();
        $dealId = htmlspecialchars($this->request->getQuery("deal_id"));
        $stage = htmlspecialchars($this->request->getQuery("stage"));
        if (!$dealId||!$stage) {
            throw new \Exception('Parameters exception: No such parameters. Add deal_id and stage.');
        }
        if ($stage == 'win') {
            $fields = ['STAGE_ID' => \CCrmDeal::GetFinalStageID()];
        } elseif ($stage == 'fail') {
            $fields = ['STAGE_ID' => 5];
        } else {
            throw new \Exception('Parameters exception: stage must be win or fail string');
        }
        $deal  = \CCrmDeal::GetByID($dealId, false);
        if ($deal['CLOSED'] == 'Y') {
            throw new \Exception('Current deal already closed');
        }

        if (!(new \CCrmDeal(false))->Update($dealId, $fields, true, true, ['DISABLE_REQUIRED_USER_FIELD_CHECK' => true])) {
            throw new \Exception('Can not update stage id');
        }
        return true;
    }

}