<?php 

namespace Tdm\DB ;

use PDO;

class DB{

  /**
   * @var PDO
   */
  private static $pdo ;
  
  static function conn($dsn, $username, $password){
    if(! is_a(self::$pdo, '\\PDO')){
      self::$pdo = new PDO($dsn, $username, $password);
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return self::$pdo;
  }

  static function query($query, $params=[], $options=[]){
    $st=self::$pdo->prepare($query, $options);
    $st->execute($params);

    $res = $st->fetchAll(PDO::FETCH_CLASS,'stdClass');

    return $res ;
  }
}