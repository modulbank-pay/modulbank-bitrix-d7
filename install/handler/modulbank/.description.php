<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$description = Loc::getMessage('MODULBANK_DESCRIPTION');


$data = array(
    'NAME' => Loc::getMessage('MODULBANK_TITLE'),
    'SORT' => 1,
    'CODES' => [
        'MERCHANT_ID' => [
            'NAME' => Loc::getMessage('MODULBANK_MERCHANT_ID'),
            'GROUP' => Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'SORT' => 1,
        ],
        'SECRET_KEY' => [
            'NAME' => Loc::getMessage('MODULBANK_SECRET_KEY'),
            'GROUP' => Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'SORT' => 2,
        ],
        'TEST_MODE' => [
            'NAME' => Loc::getMessage('MODULBANK_TEST_MODE'),
            'DESCRIPTION' => Loc::getMessage('MODULBANK_TEST_MODE_DESC'),
            'GROUP' => Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'SORT' => 3,
            'INPUT' => [
                'TYPE' => 'Y/N'
            ],
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'N',
                'PROVIDER_KEY' => 'VALUE'
            ]
        ],
        'SUCCESS_URL' => [
            'NAME' => Loc::getMessage('MODULBANK_SUCCESS_URL'),
            'SORT' => 4,
            'GROUP' => Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => '{schema}://{host}/personal/order/modulbank_finish.php',
                'PROVIDER_KEY' => 'VALUE'
            ]
        ],
        'FAIL_URL' => [
            'NAME' => Loc::getMessage('MODULBANK_FAIL_URL'),
            'SORT' => 5,
            'GROUP' => Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => '{schema}://{host}/personal/order/',
                'PROVIDER_KEY' => 'VALUE'
            ]
        ],
        'CANCEL_URL' => [
            'NAME' => Loc::getMessage('MODULBANK_CANCEL_URL'),
            'SORT' => 6,
            'GROUP' => Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => '{schema}://{host}/personal/order/',
                'PROVIDER_KEY' => 'VALUE'
            ]
        ],
        'AMOUNT' => [
            'NAME' => Loc::getMessage('MODULBANK_AMOUNT'),
            'SORT' => 7,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'SUM',
                'PROVIDER_KEY' => 'PAYMENT'
            ]
        ],
        'CURRENCY' => [
            'NAME' => Loc::getMessage('MODULBANK_CURRENCY'),
            'DESCRIPTION' => Loc::getMessage('MODULBANK_CURRENCY_DESC'),
            'SORT' => 7,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'CURRENCY',
                'PROVIDER_KEY' => 'PAYMENT'
            ]
        ],
        'ORDER_ID' => [
            'NAME' => Loc::getMessage('MODULBANK_ORDER_ID'),
            'SORT' => 8,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'ID',
                'PROVIDER_KEY' => 'PAYMENT'
            ]
        ],

        'CLIENT_FIRST_NAME' => [
            'NAME' => Loc::getMessage('MODULBANK_CLIENT_FIRST_NAME'),
            'SORT' => 10,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'NAME',
                'PROVIDER_KEY' => 'USER'
            ]
        ],
        'CLIENT_LAST_NAME' => [
            'NAME' => Loc::getMessage('MODULBANK_CLIENT_LAST_NAME'),
            'SORT' => 10,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'LAST_NAME',
                'PROVIDER_KEY' => 'USER'
            ]
        ],
        'CLIENT_PHONE' => [
            'NAME' => Loc::getMessage('MODULBANK_CLIENT_PHONE'),
            'SORT' => 11,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'PERSONAL_MOBILE',
                'PROVIDER_KEY' => 'USER'
            ]
        ],
        'CLIENT_EMAIL' => [
            'NAME' => Loc::getMessage('MODULBANK_CLIENT_EMAIL'),
            'SORT' => 12,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'EMAIL',
                'PROVIDER_KEY' => 'USER'
            ]
        ],
        'ORDER_DESCRIPTION' => [
            'NAME' => Loc::getMessage('MODULBANK_ORDER_DESCRIPTION'),
            'DESCRIPTION' => Loc::getMessage('MODULBANK_ORDER_DESCRIPTION_DESC'),
            'SORT' => 13,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => Loc::getMessage('MODULBANK_DEFAULT_ORDER_DESCRIPTION'),
                'PROVIDER_KEY' => 'VALUE'
            ]
        ],
    ]
);
