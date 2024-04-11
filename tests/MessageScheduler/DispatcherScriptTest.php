<?php 

namespace Tdm\Tests\MessageScheduler;

class DispatcherScriptTest extends MessageSchedulerTestCase{

  public function setup():void{
    parent::setup();
  }

  public function testScheduleDailyStartDateText(){
    $this->createSchedule(1, date('H:i:s'), 1, date('Y-m-d H:i:s'), 
    null, $text='hola mundo');
    $this->runSchedulerScript();
  }

  protected function runSchedulerScript(){
    $script = __DIR__ .'/../../bin/dispatcher.php' ;
    exec($script, $out, $r);
    $this->assertEquals(0, $r);
    $this->assertStringContainsString('sending message:',join('\n',$out));
  }
}