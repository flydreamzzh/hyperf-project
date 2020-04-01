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

            /***************************************************** 数据库性能 ******************************************************/

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


            /***************************************************** API接口 ******************************************************/
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
                        'includeStacktraces' => true,
                    ],
                ]
            ],
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

            /***************************************************** 命令行、定时任务 ******************************************************/
            [
                'class' => App\Core\Handler\LogFileHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/command/[datetime].log',
                    'level' => Monolog\Logger::INFO,
                    'channel' => 'command'
                ],
                'formatter' => [
                    'class' => \App\Core\Formatter\CommandLineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ]
            ],
            [
                'class' => App\Core\Handler\LogFileHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/command/error/[datetime].log',
                    'level' => Monolog\Logger::ERROR,
                    'channel' => 'command'
                ],
                'formatter' => [
                    'class' => \App\Core\Formatter\CommandLineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ]
            ],

            /***************************************************** 发送邮件 ******************************************************/
            [
                'class' => App\Core\Handler\LogFileHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/mailer/[datetime].log',
                    'level' => Monolog\Logger::INFO,
                    'channel' => 'mailer'
                ],
                'formatter' => [
                    'class' => \App\Core\Formatter\MailerLineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ]
            ],
            [
                'class' => App\Core\Handler\LogFileHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/mailer/error/[datetime].log',
                    'level' => Monolog\Logger::ERROR,
                    'channel' => 'mailer'
                ],
                'formatter' => [
                    'class' => \App\Core\Formatter\MailerLineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ]
            ],
        ]
    ],
];
