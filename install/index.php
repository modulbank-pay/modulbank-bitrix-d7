<?php

use \Bitrix\Main\IO\Directory;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Class modulbank_payments extends CModule
{
    const MODULE_ID = 'modulbank.payments';
    var $MODULE_ID = 'modulbank.payments';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    const INSTALL_SCHEMA = [
        # source dir/file -> destination dir/file
        # NB: please use destination path with a final directory/file name (not just a target dir to copy files to).
        # The destination path is what going to be deleted on uninstallation. Don't put system directories here.
        ["handlers", "bitrix/php_interface/include/sale_payment/modulbank"],
        ["images/modulbank.png", "bitrix/images/sale/sale_payments/modulbank.png"],
    ];

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage('MODULBANK_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULBANK_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('MODULBANK_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = "https://modulbank.ru/";
    }


    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles($arParams = array())
    {
        $srcRoot = dirname(__FILE__);
        $dstRoot = $_SERVER['DOCUMENT_ROOT'];
        //error_log("Installing files");
        //error_log("  source dir full path: $srcRoot");
        //error_log("  destination dir full path: $dstRoot");

        foreach ($this::INSTALL_SCHEMA as $pair) {
            $src = $srcRoot.'/'.$pair[0];
            $dst = $dstRoot.'/'.$pair[1];
            //error_log("Installing $src to $dst");

            $dstDir = is_dir($src) ? $dst : dirname($dst);

            //error_log(" -> Creating target dir $dstDir...");
            if (!Directory::createDirectory($dstDir)) {
                //error_log(" -> failed: ". error_get_last()['message']);
                return false;
            }
            //error_log(" -> OK");

            //error_log(" -> Copying files...");

            if (!CopyDirFiles($src, $dst, true, true)) {
                //error_log(" -> failed: ". error_get_last()['message']);
                return false;
            }
            //error_log(" -> OK");
        }

        return true;
    }

    function UnInstallFiles()
    {
        //error_log("Uninstalling files");
        foreach ($this::INSTALL_SCHEMA as $pair) {
            $dst = $pair[1];
            //error_log(" -> removing $dst");
            if (!DeleteDirFilesEx($dst)) {  // relative to DOCUMENT_ROOT path here
                //error_log(" -> failed: " . error_get_last()['message']);
                return false;
            }
        }

        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;

        $this->InstallFiles();

        RegisterModule(self::MODULE_ID);
    }

    function DoUninstall()
    {
        global $APPLICATION;

        UnRegisterModule(self::MODULE_ID);

        $this->UnInstallFiles();
    }
}
