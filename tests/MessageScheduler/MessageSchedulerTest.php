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
class MessageSchedulerTest extends MessageSchedulerTestCase{


  public function setup():void{
    parent::setup();
  }

  public function testScheduleDailyStartDateText(){
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    null, $text='hola mundo');
    $this->runScheduler();
    $this->assertMessageQueued(1);
  }

  public function testScheduleDailyEndDateTextFail(){
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    date('Y-m-d H:i:s',time() - 86400), $text='hola mundo');
    $this->runScheduler();
    $this->assertMessageQueued(0);
  }

  public function testScheduleDailyEndDateTextOk(){
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    date('Y-m-d H:i:s',time() + 86400), $text='hola mundo');
    $this->runScheduler();
    $this->assertMessageQueued(1);
  }

  public function testScheduleInactive(){
    $this->createSchedule(0, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    null, $text='hola mundo');
    $this->runScheduler();
    $this->assertMessageQueued(0);
  }
  
  public function testScheduleDailyStartDateLaterText(){
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s', time() + 86400), 
    null, $text='hola mundo');
    $this->runScheduler();
    $this->assertMessageQueued(0);
  }

  public function testScheduleDailyNoStartDateText(){
    $this->createSchedule(1, date('H:i:s'), 1, null, 
    null, $text='hola mundo');
    $this->runScheduler();
    $this->assertMessageQueued(1);
  }

  public function testScheduleDailyNoStartDateText7daysperiod(){
    $this->createSchedule(1, date('H:i:s'), 7, null, 
    null, $text='hola mundo');
    $this->runScheduler();
    $this->assertMessageQueued(1);
  }

  public function testDailyNoStartDateText7daysperiodLastDeliveryDate(){
    $this->createSchedule(1, date('H:i:s'), 7, null, 
    null, $text='hola mundo',date('Y-m-d H:i:s',time() - 86400 * 7));
    $this->runScheduler();
    $this->assertMessageQueued(1);
  }

  public function testDailyNoStartDateText7daysperiodLastDeliveryDateFail(){
    $this->createSchedule(1, date('H:i:s'), 7, null, 
    null, $text='hola mundo',date('Y-m-d H:i:s',time() - 86400 * 3));
    $this->runScheduler();
    $this->assertMessageQueued(0);
  }

  public function testScheduleDailyNoStartDateTextWeekly(){
    $this->createSchedule(1, date('H:i:s'), 1, null, 
    null, $text='hola mundo',null,$this->getWeekdayMysql(time()));
    $this->runScheduler();
    $this->assertMessageQueued(1);
  }

  public function testScheduleDailyNoStartDateTextWeeklyFail(){
    $this->createSchedule(1, date('H:i:s'), 1, null, 
    null, $text='hola mundo',null,$this->getWeekdayMysql(time() + 86400));
    $this->runScheduler();
    $this->assertMessageQueued(0);
  }

  public function testScheduleDailyStartDateMsgId(){
    $msg_gen = $this->createMsgGeneral()[0];
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    null,null,null,null,$msg_gen->id);
    $this->runScheduler();
    $this->assertMessageQueued(1);
  }

  public function testScheduleDailyStartDateMsgIdNotValid(){
    $msg_gen = $this->createMsgGeneral()[0];
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    null,null,null,null,-1);
    $this->runScheduler();
    $this->assertMessageQueued(0);
    $pm=DB::query('SELECT * FROM prog_msg')[0];
    $this->assertNotEmpty($pm->result);
  }

  public function testScheduleDailyStartDateServiceCategory(){
    $service_id=1;
    $cat_id=1;
    $msg_gen = $this->createMsgGeneral($service_id, $cat_id)[0];
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    null,null,null,null, null, $service_id, $cat_id);
    $this->runScheduler();
    $this->assertMessageQueued(1);
  }

  public function testScheduleDailyStartDateServiceCategoryNotValid(){
    $service_id=1;
    $cat_id=1;
    $msg_gen = $this->createMsgGeneral($service_id, $cat_id)[0];
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    null,null,null,null, null, -1, -1);
    $this->runScheduler();
    $this->assertMessageQueued(0);
    $pm=DB::query('SELECT * FROM prog_msg')[0];
    $this->assertNotEmpty($pm->result);
  }

  public function testScheduleDailyStartDateServiceCategoryWithLastId(){
    $service_id=1;
    $cat_id=1;
    
    $msg_gen_list = $this->createMsgGeneral($service_id, $cat_id,2);
    $ult_msg=$msg_gen_list[0];
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    null,null,null,null, null, $service_id, $cat_id, $ult_msg->id);
    $this->runScheduler();
    $this->assertMessageQueued(1);
    $ps=DB::query('SELECT * FROM prog_msg')[0];
    $this->assertEquals(2, $ps->ult_msg_id);
  }

  public function testScheduleDailyStartDateTextOtherHourFail(){
    $this->createSchedule(1, date('H:i:s',time()+60), 1, date('Y-m-d H:i:s'), 
    null, $text='hola mundo');
    $this->runScheduler();
    $this->assertMessageQueued(0);
  }

  protected function createMsgGeneral($service_id=1, $cat_id=1, $count=1){
    DB::query('DROP TABLE IF EXISTS mensajes_general');

    DB::query(
      "CREATE TABLE `mensajes_general` (
        `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `activo` tinyint(1) NOT NULL DEFAULT '1',
        `categoria_mensaje_id` int NOT NULL,
        `texto` varchar(400) NOT NULL,
        `servicio_id` int NOT NULL,
        `url` varchar(200) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1"
    );
    for($i=0; $i< $count; $i++){
      DB::query("INSERT INTO `mensajes_general` (`id`, `activo`, `categoria_mensaje_id`, `texto`, `servicio_id`, `url`) VALUES
      (".($i+1).", 0, $cat_id, 'Mensaje de prueba para scheduler no 1', $service_id, NULL)");
    }
    return DB::query('SELECT * FROM mensajes_general');
  }

  protected function getWeekdayMysql($time){
    $w=date('w',$time);
    $wm=$w-1;
    if($wm < 0){
      $wm=6;
    }
    return $wm;
  }

  
}