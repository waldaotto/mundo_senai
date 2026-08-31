<?php 

function url_to(string $destino, ?array $param = null)
{
  
  $url = ROOT.$destino;
  if($param != null)
  {
    foreach($param as $p){
      $url.='/'.$p;
    }
  }
  return $url;
}

?>