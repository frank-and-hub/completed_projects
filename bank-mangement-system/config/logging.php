<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channelsdddd
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety hbnof powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],
        'commissioninvestment' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission/commission_investment_monthly.log'),
            'level' => 'info',
        ],
        'commissionfule' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission/commission_fule_monthly.log'),
            'level' => 'info',
        ],
        'commissioncarry' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission/commission_carry_monthly.log'),
            'level' => 'info',
        ],
        'commissioncollection' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission/commission_collection_monthly.log'),
            'level' => 'info',
        ],

        'commissionloan' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission/commission_loan_monthly.log'),
            'level' => 'info',
        ],
        'commissionsum' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission/commission_sum_monthly.log'),
            'level' => 'info',
            'days' => '', //30,
        ],
        'ssbEcsCronLoan' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan/monthly_ESC_transaction_for_loan.log'),
            'level' => 'info',
            'days' =>  30,
        ],
        'ssbEcsCronGroupLoan' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan/monthly_ESC_transaction_for_group_loan.log'),
            'level' => 'info',
            'days' => 30,
        ],
        'fulecalculation' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission/fule_calculation.log'),
            'level' => 'info',
        ],
        'associateexception' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission/associate_exception.log'),
            'level' => 'info',
        ],
        'branchLimit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/branch/branchLimit.log'),
            'level' => 'info',
        ],

        'moneyBack' => [
            'driver' => 'daily',
            'path' => storage_path('logs/moneyBack/money_back.log'),
            'level' => 'info',
        ],
        
        'loanEmiquery' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan/loanEmiquery.log'),
            'level' => 'info',
        ],
        'grploanemi' => [
            'driver' => 'daily',
            'path' => storage_path('logs/grp/grploanemi.log'),
            'level' => 'info',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,// 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],


        'mis' => [
            'driver' =>'daily',
            'path' => storage_path('logs/mis/mis.log'),
            'level' => 'info',
        ],
        'notification' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan/notification.log'),
            'level' => 'info',
            'days' => 30,
        ],

        'calender' => [
            'driver' => 'daily',
            'path' => storage_path('logs/calender/calender.log'),
            'level' => 'info',
            'days' => 365,  // Log for one year
        ],

        'holi' => [
            'driver' => 'daily',
            'path' => storage_path('logs/holi/holi.log'),
            'level' => 'info',
            'days' => 365,  // Log for one year
        ],

        'newyear' => [
            'driver' => 'daily',
            'path' => storage_path('logs/newyear/newyear.log'),
            'level' => 'info',
            'days' => 365,  // Log for one year
        ],

        'birthday' => [
            'driver' => 'daily',
            'path' => storage_path('logs/birthday/birthday.log'),
            'level' => 'info',
            'days' => 14,  // Log for one year
        ],
        'Holiday' => [
            'driver' => 'daily',
            'path' => storage_path('logs/holiday/holiday.log'),
            'level' => 'info',
            'days' => 14,  // Log for one year
        ],
        'SendEcsReminder' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sendEcsSms/SendEcsReminder.log'),
            'level' => 'info',
            'days' => 30,
        ],
        
    ],

];
