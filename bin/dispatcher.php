#!/bin/env php 
<?php 

use Tdm\MsgScheduler\MessageScheduler;

$dir_base = __DIR__ . '/..';

require_once $_composer_autoload_path ?? $dir_base . '/vendor/autoload.php' ;

$ms = new MessageScheduler();

$dispatcher = function($message){
  echo 'sending message: ' . json_encode($message). "\n";
};

//
// modify .env file parameters to setup database connection
//
$info=parse_ini_file(__DIR__ .'/.env');

$dsn_conf = [$info['DB_DSN'], $info['DB_USER'], $info['DB_PASSWD']];

$ms->setup($dsn_conf, $dispatcher, true);

$ms->run();
