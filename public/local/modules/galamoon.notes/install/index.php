<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die;
}

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

if (class_exists('galamoon_notes')) {
    return;
}

Loc::loadMessages(__FILE__);

class galamoon_notes extends CModule
{
    public $MODULE_ID = 'galamoon.notes';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/../.description.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('NOTES_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('NOTES_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('NOTES_PARTNER_NAME');
    }

    public function InstallDB()
    {
        $application = Application::getInstance();
        $connection = $application->getConnection();
        $sql = file_get_contents(__DIR__ . '/db/mysql/install.sql');
        try {
            $connection->queryExecute($sql);
        } catch (\Exception $e) {
            $application->getExceptionHandler()->writeToLog($e);
            return false;
        }
        return true;
    }

    public function UnInstallDB($keepTables = false)
    {
        if (!$keepTables) {
            $application = Application::getInstance();
            $connection = $application->getConnection();
            $sql = file_get_contents(__DIR__ . '/db/mysql/uninstall.sql');
            try {
                $connection->queryExecute($sql);
            } catch (\Exception $e) {
                $application->getExceptionHandler()->writeToLog($e);
                return false;
            }
        }
        return true;
    }

    public function InstallFiles()
    {
        global $DOCUMENT_ROOT;

        $installPath = __DIR__;
        $localPath = $DOCUMENT_ROOT . '/local';

        // Copy components
        $srcComponents = $installPath . '/components';
        $dstComponents = $localPath . '/components';
        if (is_dir($srcComponents)) {
            CopyDirFiles($srcComponents, $dstComponents, true, true);
        }

        // Copy JS extensions
        $srcJs = $installPath . '/js';
        $dstJs = $localPath . '/js';
        if (is_dir($srcJs)) {
            CopyDirFiles($srcJs, $dstJs, true, true);
        }

        return true;
    }

    public function UnInstallFiles()
    {
        return true;
    }

    public function DoInstall()
    {
        global $APPLICATION, $step;
        if ($step == 2) {
            $this->InstallDB();
            $this->InstallFiles();
            ModuleManager::registerModule($this->MODULE_ID);
        } elseif($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('NOTES_INSTALL_TITLE'),
                __DIR__ . '/step1.php'
            );
        }

    }

    public function DoUninstall()
    {
        global $APPLICATION, $step;
        if ($step == 2) {
            $application = Application::getInstance();
            $request = $application->getContext()->getRequest();
            $keepTables = $request->getPost('savedata') === 'Y';
            $this->UnInstallDB($keepTables);
            $this->UnInstallFiles();
            ModuleManager::unRegisterModule($this->MODULE_ID);
        }elseif ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('NOTES_UNINSTALL_TITLE'),
                __DIR__ . '/unstep1.php'
            );
        }
    }
}
