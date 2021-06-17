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

//function getProxies($fp){
//  $proxies = explode("\n", file_get_contents($fp));
//  for ($i=0; $i < sizeof($proxies); $i++) {
//    $proxies[$i] = trim($proxies[$i]);
//  }
//  $proxies = array_filter($proxies, function($v){
//    return $v!="";
//  }, ARRAY_FILTER_USE_BOTH);
//  return $proxies;
//}
