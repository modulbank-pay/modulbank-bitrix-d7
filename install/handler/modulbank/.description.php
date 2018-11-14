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

        // ДЛЯ КАССЫ

        'SNO' => [
            'NAME' => 'Система налогообложения',
            'GROUP' => 'Параметры для кассы',//Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'SORT' => 14,
            'TYPE' => 'SELECT',
            'INPUT' => [
                'TYPE' => 'ENUM',
                'OPTIONS' => [
                    'osn' => 'Общая',
                    'usn_income' => 'Упрощенная СН (доходы)',
                    'usn_income_outcome' => 'Упрощенная СН (доходы минус расходы)',
                    'envd' => 'Единый налог на вмененный доход',
                    'esn' => 'Единый сельскохозяйственный налог',
                    'patent' => 'Патентная СН'
                ]
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => 'osn'
            ]
        ],

        'PAYMENT_OBJECT' => [
            'NAME' => 'Предмет расчета',
            'GROUP' => 'Параметры для кассы', //Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'SORT' => 15,
            'TYPE' => 'SELECT',
            'INPUT' => [
                'TYPE' => 'ENUM',
                'OPTIONS' => [
                    'commodity' => 'Товар',
                    'excise' => 'Подакцизный товар',
                    'job' => 'Работа',
                    'service' => 'Услуга',
                    'gambling_bet' => 'Ставка азартной игры',
                    'gambling_prize' => 'Выигрыш азартной игры',
                    'lottery' => 'Лотерейный билет',
                    'lottery_prize' => 'Выигрыш лотереи',
                    'intellectual_activity' => 'Предоставление результатов интеллектуальной деятельности',
                    'payment' => 'Платеж',
                    'agent_commission' => 'Агентское вознаграждение',
                    'composite' => 'Составной предмет расчета',
                    'another' => 'Другое'
                ]
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => 'commodity'
            ]
        ],

        'PAYMENT_METHOD' => [
            'NAME' => 'Метод платежа',
            'GROUP' => 'Параметры для кассы', //Loc::getMessage('MODULBANK_CONNECT_GROUP'),
            'SORT' => 16,
            'TYPE' => 'SELECT',
            'INPUT' => [
                'TYPE' => 'ENUM',
                'OPTIONS' => [
                    'full_prepayment' => 'Предоплата 100%',
                    'prepayment' => 'Предоплата',
                    'advance' => 'Аванс',
                    'full_payment' => 'Полный расчет',
                    'partial_payment' => 'Частичный расчет и кредит',
                    'credit' => 'Передача в кредит',
                    'credit_payment' => 'Оплата кредита'
                ]
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => 'full_prepayment'
            ]
        ],
    ]
);
