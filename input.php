<?php


function getIds($fp){
  $ids = explode("\n", file_get_contents($fp));
  for ($i=0; $i < sizeof($ids); $i++) {
    $ids[$i] = trim($ids[$i]);
  }
  $ids = array_filter($ids, function($v){
    return $v!="";
  }, ARRAY_FILTER_USE_BOTH);
  return $ids;
}
