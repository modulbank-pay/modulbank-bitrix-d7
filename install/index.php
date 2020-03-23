<?php

use \Bitrix\Main\IO\Directory;

IncludeModuleLangFile(__FILE__);

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
		["tools/modulbank.payments", "bitrix/tools/modulbank.payments"],
    ];

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage('MODULBANK_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('MODULBANK_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('MODULBANK_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = "https://modulbank.ru/";
    }


    function InstallEvents()
    {
        RegisterModuleDependences("main",  "OnAdminSaleOrderView", "modulbank.payments", "modulbankOrderEdit", "addHoldButtons");
		RegisterModuleDependences("main",  "OnAdminSaleOrderEdit", "modulbank.payments", "modulbankOrderEdit", "addHoldButtons");
		
		return true;
    }

    function UnInstallEvents()
    {
        UnRegisterModuleDependences("main",  "OnAdminSaleOrderView", "modulbank.payments", "modulbankOrderEdit", "addHoldButtons");
		UnRegisterModuleDependences("main",  "OnAdminSaleOrderEdit", "modulbank.payments", "modulbankOrderEdit", "addHoldButtons");
		
		return true;
    }

    function InstallFiles($arParams = array())
    {
        $srcRoot = dirname(__FILE__);
        $dstRoot = $_SERVER['DOCUMENT_ROOT'];
        
        foreach ($this::INSTALL_SCHEMA as $pair) {
            $src = $srcRoot.'/'.$pair[0];
            $dst = $dstRoot.'/'.$pair[1];
        
            $dstDir = is_dir($src) ? $dst : dirname($dst);

            if (!Directory::createDirectory($dstDir)) {
                return false;
            }
        
            if (!CopyDirFiles($src, $dst, true, true)) {
                return false;
            }
        }

        return true;
    }

    function UnInstallFiles()
    {
        foreach ($this::INSTALL_SCHEMA as $pair) {
            $dst = $pair[1];
            if (!DeleteDirFilesEx($dst)) {  // relative to DOCUMENT_ROOT path here
                return false;
            }
        }

        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;

        $this->InstallFiles();
        $this->InstallEvents();

        RegisterModule(self::MODULE_ID);
    }

    function DoUninstall()
    {
        global $APPLICATION;

        UnRegisterModule(self::MODULE_ID);

        $this->UnInstallEvents();
        $this->UnInstallFiles();
    }
}
