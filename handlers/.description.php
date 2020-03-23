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
                'PROVIDER_VALUE' => '{schema}://{host}/bitrix/tools/sale_ps_success.php',
                'PROVIDER_KEY' => 'VALUE'
            ]
        ],
        'FAIL_URL' => [
            'NAME' => Loc::getMessage('MODULBANK_FAIL_URL'),
            'SORT' => 5,
            'GROUP' => Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => '{schema}://{host}/bitrix/tools/sale_ps_fail.php',
                'PROVIDER_KEY' => 'VALUE'
            ]
        ],
        'CANCEL_URL' => [
            'NAME' => Loc::getMessage('MODULBANK_CANCEL_URL'),
            'SORT' => 6,
            'GROUP' => Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => '{schema}://{host}/bitrix/tools/sale_ps_fail.php',
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
            'SORT' => 8,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'CURRENCY',
                'PROVIDER_KEY' => 'PAYMENT'
            ]
        ],
        'PAYMENT_ID' => [
            'NAME' => Loc::getMessage('MODULBANK_ORDER_ID'),
            'SORT' => 9,
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
            'SORT' => 11,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'LAST_NAME',
                'PROVIDER_KEY' => 'USER'
            ]
        ],
        'CLIENT_PHONE' => [
            'NAME' => Loc::getMessage('MODULBANK_CLIENT_PHONE'),
            'SORT' => 12,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'PERSONAL_MOBILE',
                'PROVIDER_KEY' => 'USER'
            ]
        ],
        'CLIENT_EMAIL' => [
            'NAME' => Loc::getMessage('MODULBANK_CLIENT_EMAIL'),
            'SORT' => 13,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'EMAIL',
                'PROVIDER_KEY' => 'USER'
            ]
        ],
        'ORDER_DESCRIPTION' => [
            'NAME' => Loc::getMessage('MODULBANK_ORDER_DESCRIPTION'),
            'DESCRIPTION' => Loc::getMessage('MODULBANK_ORDER_DESCRIPTION_DESC'),
            'SORT' => 14,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
            'DEFAULT' => [
                'PROVIDER_VALUE' => Loc::getMessage('MODULBANK_DEFAULT_ORDER_DESCRIPTION'),
                'PROVIDER_KEY' => 'VALUE'
            ]
        ],
		
		'PAYMENT_MODE' => [
			'NAME' => Loc::getMessage('MODULBANK_PAYMENT_MODE'),
            'DESCRIPTION' => Loc::getMessage('MODULBANK_PAYMENT_MODE_DESC'),
            'SORT' => 15,
            'GROUP' => Loc::getMessage('MODULBANK_ORDER_GROUP'),
			'TYPE' => 'SELECT',
			'INPUT' => [
                'TYPE' => 'ENUM',
                'OPTIONS' => [
                    'default' => Loc::getMessage('MODULBANK_PAYMENT_MODE_DEFAULT'),
                    'hold' => Loc::getMessage('MODULBANK_PAYMENT_MODE_HOLD'),
                ]
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => 'default'
            ]
		],

        // FOR CASHBOX

        'SNO' => [
            'NAME' => Loc::getMessage('MODULBANK_CASHBOX_SNO'),
            'GROUP' => Loc::getMessage('MODULBANK_CASHBOX_GROUP'),
            'SORT' => 16,
            'TYPE' => 'SELECT',
            'INPUT' => [
                'TYPE' => 'ENUM',
                'OPTIONS' => [
                    'osn' => Loc::getMessage('MODULBANK_CASHBOX_SNO_OSN'),
                    'usn_income' => Loc::getMessage('MODULBANK_CASHBOX_SNO_USN_INCOME'),
                    'usn_income_outcome' => Loc::getMessage('MODULBANK_CASHBOX_SNO_USN_INCOME_OUTCOME'),
                    'envd' => Loc::getMessage('MODULBANK_CASHBOX_SNO_ENVD'),
                    'esn' => Loc::getMessage('MODULBANK_CASHBOX_SNO_ESN'),
                    'patent' => Loc::getMessage('MODULBANK_CASHBOX_SNO_PATENT'),
                ]
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => 'osn'
            ]
        ],

        'PAYMENT_OBJECT' => [
            'NAME' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT'),
            'GROUP' => Loc::getMessage('MODULBANK_CASHBOX_GROUP'),
            'SORT' => 17,
            'TYPE' => 'SELECT',
            'INPUT' => [
                'TYPE' => 'ENUM',
                'OPTIONS' => [
                    'commodity' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_COMMODITY'),
                    'excise' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_EXCISE'),
                    'job' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_JOB'),
                    'service' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_SERVICE'),
                    'gambling_bet' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_GAMBLING_BET'),
                    'gambling_prize' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_GAMBLING_PRIZE'),
                    'lottery' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_LOTTERY'),
                    'lottery_prize' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_LOTTERY_PRIZE'),
                    'intellectual_activity' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_INTELLECTUAL_ACTIVITY'),
                    'payment' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_PAYMENT'),
                    'agent_commission' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_AGENT_COMMISSION'),
                    'composite' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_COMPOSITE'),
                    'another' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_OBJECT_ANOTHER'),
                ]
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => 'commodity'
            ]
        ],

        'PAYMENT_METHOD' => [
            'NAME' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_METHOD'),
            'GROUP' => Loc::getMessage('MODULBANK_CASHBOX_GROUP'),
            'SORT' => 18,
            'TYPE' => 'SELECT',
            'INPUT' => [
                'TYPE' => 'ENUM',
                'OPTIONS' => [
                    'full_prepayment' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_METHOD_FULL_PREPAYMENT'),
                    'prepayment' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_METHOD_PREPAYMENT'),
                    'advance' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_METHOD_ADVANCE'),
                    'full_payment' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_METHOD_FULL_PAYMENT'),
                    'partial_payment' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_METHOD_PARTIAL_PAYMENT'),
                    'credit' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_METHOD_CREDIT'),
                    'credit_payment' => Loc::getMessage('MODULBANK_CASHBOX_PAYMENT_METHOD_CREDIT_PAYMENT'),
                ]
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => 'full_prepayment'
            ]
        ],
    ]
);
