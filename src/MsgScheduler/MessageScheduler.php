<?php 

namespace Tdm\MsgScheduler;

use Tdm\DB\DB;

class MessageScheduler{

  private $dsn ;
  private $dispatcher;
  private $show_error ;

  public function setup(array $dsn,callable $dispatcher, $show_error=false){
    $this->dsn = $dsn ;
    $this->dispatcher = $dispatcher ;
    $this->show_error = $show_error ;
  }

  public function run(){
    // obtener la programacion de hoy con fecha y hora
    DB::conn(...$this->dsn);
    $msg_list = DB::query(
    'SELECT * FROM prog_msg p 
    WHERE
    (
      fecha_inicio <= CURRENT_TIMESTAMP
      OR fecha_inicio IS NULL
    ) 
    AND (
      fecha_fin IS NULL
      OR fecha_fin > CURRENT_TIMESTAMP 
      )
    AND activo=1
    AND (
      -- dia de la semana
      -- 0 = Monday, 1 = Tuesday, 2 = Wednesday, 3 = Thursday,
      -- 4 = Friday, 5 = Saturday, 6 = Sunday
      dia_semana IS NOT NULL AND dia_semana = WEEKDAY(CURRENT_DATE)
      OR
      dia_semana IS NULL AND (
          ult_fecha_entrega IS NOT NULL 
          AND DATEDIFF(CURRENT_TIMESTAMP, ult_fecha_entrega) = periodo
          OR
          ult_fecha_entrega IS NULL
      )
    )');

    // send each msg

    foreach($msg_list as $msg){
      $this->processMsg($msg);
    }
  }

  private function processMsg($msg){
    if(! empty($msg->texto)){
      $msg_obj =new Message($msg->texto, $msg->msg_id); 
    }elseif(! empty($msg->msg_id)){
      $msg_obj = $this->getMessageFromId($msg->msg_id);
      if($msg_obj === false){
        $this->showError($msg->id, "msg id '$msg->msg_id' not exists in table mensajes_general");
        return false;
      }
    }elseif(! empty($msg->servicio_id) && ! empty($msg->cat_msg_id)){
      $msg_list = $this->getNextMessage(
        $msg->servicio_id, 
        $msg->cat_msg_id,
        $msg->ult_msg_id
      );
      
      $num_msgs=count($msg_list);

      if($num_msgs == 2){
        $msg_obj=$msg_list[1];
      }elseif($num_msgs == 1){
        // use same message as before
        $msg_obj = $msg_list[0];
      }else{
        $this->showError($msg->id, "There are no messages with service '$msg->servicio_id' ". 
          "and category '$msg->cat_msg_id' in table mensajes_general");
        return false;
      }
    }
    $this->dispatch($msg_obj);
    // update info in proc_msg ;
    DB::query('UPDATE prog_msg SET 
    ult_msg_id=? ,
    ult_fecha_entrega=CURRENT_TIMESTAMP
    WHERE id=?',[$msg_obj->id, $msg->id]);
    return true ;
  }

  private function showError($id,$text){
    //file_put_contents('php://stderr',$text);
    if($this->show_error){
      echo "$text\n" ;
    }
    DB::query('UPDATE prog_msg SET result=? WHERE id=?',[$text, $id]);
  }

  private function getNextMessage($serv_id, $cat_id, $last_msg_id){
    $r=DB::query(
      'SELECT m.* FROM mensajes_general m 
      WHERE m.servicio_id = ?
      AND m.categoria_mensaje_id =? 
      AND m.id >= IFNULL(?,0)
      ORDER BY id LIMIT 2',
      [$serv_id, $cat_id, $last_msg_id]
    );

    return $r;
  }


  private function getMessageFromId($id){
    $r=DB::query('SELECT * FROM mensajes_general WHERE id=?',[$id]);
    return $r[0] ?? false ;
  }

  private function dispatch($msg){
    return call_user_func_array($this->dispatcher, [$msg]);
  }
}

class Message{
  public $texto ;
  public $id ;
  public $servicio_id ;
  public $categoria_mensaje_id ;

  public function __construct($texto, $id=null, $servicio_id=null, $categoria_mensaje_id=null)
  {
    $this->texto = $texto ;
    $this->id = $id ;
    $this->servicio_id = $servicio_id ;
    $this->categoria_mensaje_id = $categoria_mensaje_id;
  }
}