<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'default' => [
        'handlers' => [
            //info、waring、notice日志等
            [
                'class' => App\Core\Handler\LogFileHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/info/[datetime].log',
                    'level' => Monolog\Logger::INFO,
                ],
                'formatter' => [
                    'class' => \App\Core\Formatter\LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ]
            ],
            //数据库语句
            [
                'class' => App\Core\Handler\LogFileHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/sql/[datetime].log',
                    'level' => Monolog\Logger::INFO,
                    'channel' => 'sql'
                ],
                'formatter' => [
                    'class' => \App\Core\Formatter\LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ]
            ],
            // debug日志
            [
                'class' => App\Core\Handler\LogFileHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/debug/[datetime].log',
                    'level' => Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => \App\Core\Formatter\LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ]
            ],
            // error日志
            [
                'class' => App\Core\Handler\LogFileHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/error/[datetime].log',
                    'level' => Monolog\Logger::ERROR,
                ],
                'formatter' => [
                    'class' => \App\Core\Formatter\LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ]
            ],
        ]
    ]
];
