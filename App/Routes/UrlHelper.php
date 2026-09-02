<?php 

function url_to(string $destino, ?array $param = null)
{
  
  $url = $destino;
  if($param != null)
  {
    foreach($param as $p){
      $url.='/'.$p;
    }
  }
  return '/mundo_senai'.$url;
}

?>