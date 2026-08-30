<?php 

function url_to(string $destino, ?array $param = null)
{
  
  $url = "http://mundo_senai.test".$destino;
  if($param != null)
  {
    foreach($param as $p){
      $url.='/'.$p;
    }
  }
  return $url;
}

?>