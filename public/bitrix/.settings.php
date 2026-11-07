<?php

return array(
    'routing' => [
        'value' => [
            'config' => ['web.php', 'api.php'],
        ],
        'readonly' => true,
    ],
    'cache_flags' =>
        array(
            'value' =>
                array(
                    'config_options' => 3600.0,
                ),
            'readonly' => false,
        ),
    'cookies' =>
        array(
            'value' =>
                array(
                    'secure' => false,
                    'http_only' => true,
                ),
            'readonly' => false,
        ),
    'exception_handling' =>
        array(
            'value' =>
                array(
                    'debug' => true,
                    'handled_errors_types' => 4437,
                    'exception_errors_types' => 4437,
                    'ignore_silence' => false,
                    'assertion_throws_exception' => true,
                    'assertion_error_type' => 256,
                    'log' => NULL,
                ),
            'readonly' => false,
        ),
    'connections' =>
        array(
            'value' =>
                array(
                    'default' =>
                        array(
                            'host' => 'notes_mariadb',
                            'database' => 'bitrix',
                            'login' => 'bitrix',
                            'password' => 'bitrix',
                            'options' => 2.0,
                            'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
                        ),
                ),
            'readonly' => true,
        ),
    'crypto' =>
        array(
            'value' =>
                array(
                    'crypto_key' => '400ffd840bc99944f3e23bccf3929ac5',
                ),
            'readonly' => true,
        ),
    'messenger' =>
        array(
            'value' =>
                array(
                    'run_mode' => NULL,
                    'shuffle' => true,
                    'brokers' =>
                        array(
                            'default' =>
                                array(
                                    'type' => 'db',
                                    'params' =>
                                        array(
                                            'table' => 'Bitrix\\Main\\Messenger\\Internals\\Storage\\Db\\Model\\MessengerMessageTable',
                                        ),
                                ),
                        ),
                    'queues' =>
                        array(),
                ),
            'readonly' => true,
        ),
);
