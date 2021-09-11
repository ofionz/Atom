<?php
return array (
  'utf_mode' =>
  array (
    'value' => true,
    'readonly' => true,
  ),
  'cache' => array(
    'value' => array (
        'type' => 'memcache',
        'memcache' => array(
            'host' => 'unix:///tmp/memcached.sock',
            'port' => '0'
        ),
        'sid' => $_SERVER["DOCUMENT_ROOT"]."#01"
    ),
  ),
'pull_s1' => 'BEGIN GENERATED PUSH SETTINGS. DON\'T DELETE COMMENT!!!!',
  'pull' => Array(
    'value' =>  array(
        'path_to_listener' => "http://#DOMAIN#/bitrix/sub/",
        'path_to_listener_secure' => "https://#DOMAIN#/bitrix/sub/",
        'path_to_modern_listener' => "http://#DOMAIN#/bitrix/sub/",
        'path_to_modern_listener_secure' => "https://#DOMAIN#/bitrix/sub/",
        'path_to_mobile_listener' => "http://#DOMAIN#:8893/bitrix/sub/",
        'path_to_mobile_listener_secure' => "https://#DOMAIN#:8894/bitrix/sub/",
        'path_to_websocket' => "ws://#DOMAIN#/bitrix/subws/",
        'path_to_websocket_secure' => "wss://#DOMAIN#/bitrix/subws/",
        'path_to_publish' => 'http://127.0.0.1:8895/bitrix/pub/',
        'nginx_version' => '4',
        'nginx_command_per_hit' => '100',
        'nginx' => 'Y',
        'nginx_headers' => 'N',
        'push' => 'Y',
        'websocket' => 'Y',
        'signature_key' => 'lf0fENVkLUgxx3rG3uUtCUVE2SftX5YSTSIulC08QBK55BeIwV3mVUsdCaenPhKi2Xyk2d2QoKOdLE2LXn5Lz5oMGXkDmz7nZQiuFEczg163UgpTBU57iymNvIbQ8JO0',
        'signature_algo' => 'sha1',
        'guest' => 'N',
    ),
  ),
'pull_e1' => 'END GENERATED PUSH SETTINGS. DON\'T DELETE COMMENT!!!!',

  'cache_flags' =>
  array (
    'value' =>
    array (
      'config_options' => 3600,
      'site_domain' => 3600,
    ),
    'readonly' => false,
  ),
  'cookies' =>
  array (
    'value' =>
    array (
      'secure' => false,
      'http_only' => true,
    ),
    'readonly' => false,
  ),
  'exception_handling' =>
  array (
    'value' =>
    array (
      'debug' => true,
      'handled_errors_types' => 4437,
      'exception_errors_types' => 4437,
      'ignore_silence' => false,
      'assertion_throws_exception' => true,
      'assertion_error_type' => 256,
      'log' => array (
          'settings' =>
          array (
            'file' => '/var/log/php/exceptions.log',
            'log_size' => 1000000,
        ),
      ),
    ),
    'readonly' => false,
  ),
  'crypto' => 
  array (
    'value' => 
    array (
        'crypto_key' => 'b99okokppvkpwsbblsc80aebf06606qy',
    ),
    'readonly' => true,
  ),
  'connections' =>
  array (
    'value' =>
    array (
      'default' =>
      array (
        'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
        'host' => 'localhost',
        'database' => 'sitemanager',
        'login'    => 'bitrix0',
        'password' => 'z%WfrFlt?+Q[9Q+a0zrP',
        'options' => 2,
      ),
    ),
    'readonly' => true,
  )
);
