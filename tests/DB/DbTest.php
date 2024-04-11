<?php 

namespace Tdm\Tests\DB ;

use PHPUnit\Framework\TestCase;
use Tdm\DB\DB;

/**
 * 
 * ---------
 * IMPORTANT
 * ---------
 * 
 * This test needs access to database 'test' with user 'testuser@localhost'
 */

class DbTest extends TestCase {

  public $dsn_data = [];

  public function setup():void{
    $this->dsn_data=[$GLOBALS['DB_DSN'],$GLOBALS['DB_USER'],$GLOBALS['DB_PASSWD']];

    $c=DB::conn(...$this->dsn_data);
    DB::query('drop table if exists testtable');
  }

  public function testConnect(){
    $c=DB::conn(...$this->dsn_data);
    $this->assertInstanceOf('\\PDO', $c);
  }

  public function testQuery(){
    $c=DB::conn(...$this->dsn_data);
    
    $r=DB::query('CREATE table testtable (a int, b varchar(10), c timestamp)');

    $r=DB::query('INSERT INTO testtable VALUES (1, "name1", current_timestamp() )');

    $r=DB::query('INSERT INTO testtable VALUES (2, "name2", current_timestamp() )');

    $r=DB::query('INSERT INTO testtable VALUES (3, "name3", current_timestamp() )');

    $r=DB::query('SELECT * FROM testtable');

    $this->assertCount(3, $r);
    $this->assertEquals(1,$r[0]->a);
    $this->assertEquals('name2',$r[1]->b);
  }

}