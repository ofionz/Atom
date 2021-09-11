<?php
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
set_time_limit(0);

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

Loader::IncludeModule('crm');
Loader::IncludeModule('fileman');

class SendSMS
{
    const URL = 'http://ask-it.dom.ru/ws-itsbus/SMS_Tools.asmx';
    const SENDER = 'Bitrix24 on Host.dom.ru';

    public function __construct()
    {
        if (!isset($_REQUEST['id'])) {
            $this->writeToLog('id not found');
            die;
        }

        if (!Loader::includeModule('crm')) {
            $this->writeToLog('crm module not installed');
        }
    }

    private function getDealById($dealId)
    {
        $dbCCrmDeal = CCrmDeal::GetList([], ['ID' => $dealId, 'CHECK_PERMISSIONS' => 'N']);
        $deal = [];
        while ($arDeal = $dbCCrmDeal->Fetch()) {
            $deal = $arDeal;
        }


        return $deal;
    }

    private function getContactById($contactId)
    {
        $arFilter = [
            'CHECK_PERMISSIONS' => 'N',
            'ENTITY_ID' => 'CONTACT',
            'ELEMENT_ID' => $contactId,
            'TYPE_ID' => 'PHONE',
        ];
        $resPhones = \CCrmFieldMulti::GetListEx([], $arFilter, false, ['nTopCount' => 1], ['*']);
        $arPhone = $resPhones->fetch();

        $dbCCrmContact = CCrmContact::GetList([], ['ID' => $contactId, 'CHECK_PERMISSIONS' => 'N']);
        $arContact = $dbCCrmContact->fetch();
        $arContact['PHONE'] = $arPhone['VALUE'];

        return $arContact;
    }

    private function makeRequest($params)
    {
        $soapClient = new SoapClient(self::URL . '?wsdl', ["trace" => 1]);

        try {
            $result = $soapClient->__soapCall("SendCrmMessage", [
                "SendCrmMessage" => array(
                    'sPhoneNo' => $params['sPhoneNo'],
                    'sMessageText' => $params['sMessageText'],
                    'sSender' => self::SENDER
                )
            ]);
        } catch (SoapFault $fault) {
            $this->writeToLog($fault);
        }
    }

    private function getActivity($ownerId/*, $type = 'MEETING', $select = '*'*/)
    {
        $dbCCrmActivity = CCrmActivity::GetList(
            [],
            ['OWNER_ID' => $ownerId, 'CHECK_PERMISSIONS' => 'N', 'PROVIDER_TYPE_ID' => 'MEETING'],
            ['START_TIME', 'LOCATION']);
        $activity = [];
        while ($arActivity = $dbCCrmActivity->Fetch()) {
            $activity = $arActivity;
        }

        return $activity;
    }

    public function send()
    {
        $deal = $this->getDealById($_REQUEST['id']);
        $activity = $this->getActivity($_REQUEST['id']);
        $contact = $this->getContactById($deal['CONTACT_ID']);
        $contactName = $contact['NAME'];

        $userData = Bitrix\Main\UserTable::getRow([
            'select' => ['PERSONAL_MOBILE', 'EMAIL'],
            'filter' => ['=ID' => $deal['ASSIGNED_BY_ID']]
        ]);
        $phoneUser = $userData['PERSONAL_MOBILE'];
        $emailUser = $userData['EMAIL'];

        $template = '#CONTACT_NAME#спасибо, что обратились в Атомстройкомплекс. Ваш менеджер:#RESPONSIBLE_NAME##RESPONSIBLE_LAST_NAME##RESPONSIBLE_MOBILE##RESPONSIBLE_EMAIL#Сайт: www.atomstroy.pro';
        $templateVars = [
            "#CONTACT_NAME#",
            "#RESPONSIBLE_NAME#",
            "#RESPONSIBLE_LAST_NAME#",
            '#RESPONSIBLE_MOBILE#',
            '#RESPONSIBLE_EMAIL#'
        ];
        $templateData = [
            $contactName ? $contactName . ', ' : '',
            $deal['ASSIGNED_BY_NAME'] ? ' '.$deal['ASSIGNED_BY_NAME'].' ' : '',
            $deal['ASSIGNED_BY_LAST_NAME'] ? $deal['ASSIGNED_BY_LAST_NAME'].' ' : '',
            $phoneUser ? 'Тел.: '.$phoneUser.' ' : '',
            $emailUser ? 'E-mail: '.$emailUser.' ' : ''
        ];
        $textSms = str_replace($templateVars, $templateData, $template);
        if ($_REQUEST['type'] == 'VSTRECHA') {
            $template = '#CONTACT_NAME#спасибо, что обратились в Атомстройкомплекс. Ждем вас по адресу: #LOCATION##START_TIME# Ваш менеджер:#RESPONSIBLE_NAME##RESPONSIBLE_LAST_NAME##RESPONSIBLE_MOBILE##RESPONSIBLE_EMAIL#Сайт: www.atomstroy.pro';
            $templateVars = [
                "#CONTACT_NAME#",
                '#LOCATION#',
                '#START_TIME#',
                "#RESPONSIBLE_NAME#",
                "#RESPONSIBLE_LAST_NAME#",
                '#RESPONSIBLE_MOBILE#',
                '#RESPONSIBLE_EMAIL#'
            ];
			$activity['START_TIME'] = (new DateTime($activity['START_TIME']))->format('d.m.Y H:i');
            $templateData = [
                $contactName ? $contactName . ', ' : '',
                $activity['LOCATION'] ? $activity['LOCATION'] : '',
                $activity['START_TIME'] ? ' '.str_replace(" ", " в ", $activity['START_TIME']) : '',
                $deal['ASSIGNED_BY_NAME'] ? ' '.$deal['ASSIGNED_BY_NAME'].' ' : '',
                $deal['ASSIGNED_BY_LAST_NAME'] ? $deal['ASSIGNED_BY_LAST_NAME'].' ' : '',
                $phoneUser ? 'Тел.: '.$phoneUser.' ' : '',
                $emailUser ? 'E-mail: '.$emailUser.' ' : ''
            ];
            $textSms = str_replace($templateVars, $templateData, $template);
        }
		//$this->writeToLog($textSms);

		$this->makeRequest(['sPhoneNo' => $contact['PHONE'], 'sMessageText' => $textSms]);
		$this->dealUpdate();
    }

    private function dealUpdate()
    {
        $field = 'UF_SMS_VISITKA';
        if ($_REQUEST['type'] == 'VSTRECHA') {
            $field = 'UF_SMS_VSTRECHA';
        }
        $deal = new CCrmDeal(false);
        $arDealUpdate = [$field => 1];
        $resUpd = $deal->Update($_REQUEST['id'],
            $arDealUpdate
        );
        if (!$resUpd) {
            $this->writeToLog($deal);
        }
    }

    public function writeToLog($data, $title = '')
    {
        $log = "\n------------------------\n";
        $log .= date("Y.m.d G:i:s") . "\n";
        $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents(getcwd() . '/logs.txt', $log, FILE_APPEND);
        return true;
    }
}

$sendSms = new SendSMS();
$sendSms->send();

