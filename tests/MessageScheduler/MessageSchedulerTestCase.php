<?php 

namespace Tdm\Tests\MessageScheduler;

use PHPUnit\Framework\TestCase;
use Tdm\DB\DB;
use Tdm\MsgScheduler\MessageScheduler;

/**
 * 
 * ---------
 * IMPORTANT
 * ---------
 * 
 * This test needs access to database 'test' with user 'testuser@localhost'
 */
class MessageSchedulerTestCase extends TestCase{

  public $dsn_data = [];

  public static $msg_list = [];

  public function setup():void{
    $this->dsn_data=[$GLOBALS['DB_DSN'],$GLOBALS['DB_USER'],$GLOBALS['DB_PASSWD']];

    DB::conn(...$this->dsn_data);
    DB::query('DROP TABLE IF EXISTS prog_msg');
    DB::query("CREATE TABLE `prog_msg` (
      `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
      `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `fecha_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `fecha_inicio` timestamp NULL DEFAULT NULL,
      `fecha_fin` timestamp NULL DEFAULT NULL,
      `activo` tinyint(1) NOT NULL DEFAULT '1',
      `servicio_id` int DEFAULT NULL,
      `periodo` int NOT NULL,
      `dia_semana` tinyint DEFAULT NULL,
      `hora` time NOT NULL,
      `texto` varchar(500) DEFAULT NULL,
      `msg_id` int DEFAULT NULL,
      `cat_msg_id` int DEFAULT NULL,
      `ult_msg_id` int DEFAULT NULL,
      `ult_fecha_entrega` timestamp NULL DEFAULT NULL,
      `result` varchar(200) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");

    self::$msg_list = [];
  }

  protected function createSchedule(
    $active, 
    $hour,
    $period=1, 
    $start_date=null, 
    $end_date=null, 
    $text=null,
    $ult_fecha_entrega=null,
    $dia_semana=null,
    $msg_id = null ,
    $service_id=null,
    $cat_id = null
    ){
    DB::query('INSERT INTO prog_msg 
    (fecha_inicio, fecha_fin, activo, texto,periodo, hora, ult_fecha_entrega,dia_semana, msg_id, servicio_id, cat_msg_id)
    VALUES
    (?, ?, ?, ?, ?, ?,?,?,?,?,?)',[
      $start_date ,
      $end_date, 
      $active,
      $text,
      $period,
      $hour ,
      $ult_fecha_entrega,
      $dia_semana,
      $msg_id ,
      $service_id,
      $cat_id
    ]);
  }

  protected function runScheduler(){
    $ms = new MessageScheduler();
    $dispatcher = array(self::class, 'dispatcherMethod');
    $ms->setup($this->dsn_data, $dispatcher);
    $ms->run();
  }

  public static function dispatcherMethod($msg){
    self::$msg_list[] = $msg ;
  }

  protected function assertMessageQueued($count){
    $this->assertCount($count, self::$msg_list);
  }
}