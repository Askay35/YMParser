<?php

require_once 'parser.php';
require_once 'db.php';
require_once 'input.php';



function start(){
  $settings = json_decode(file_get_contents('settings.json'),true);
  $db = new DB($settings['host'], $settings['db'], $settings['user'], $settings['password']);
  $ids = getIds($settings['ids_file']);
  $delay = 10;
  $proxies = getProxies($settings['proxies_file']);
  $parser;
  if(sizeof($proxies)>0){
    $parser = new Parser($proxies);
  }
  else{
    $parser = new Parser();
  }
  foreach ($ids as $id) {
    echo "parsing id $id \n";
    $pokupki = strlen($id)>11;
    if($pokupki){
      $json = $parser->getReviewsJson($id, 1, $pokupki);
      if(!$json){
        echo "no response for id {$id}\n";
        continue;
      }
        $reviews = PJsonParser::getReviews($json);
      $db->addReviews($reviews);
      echo "page 1 for id {$id} parsed\n";
      $pagecount = PJsonParser::getPagesCount($json);
      if(!$pagecount){
        echo "no pages count for id {$id}\n";
        continue;
      }
      $page = 2;
      while($page < $pagecount){
        if(!$json){
          echo "Parse {$id} error on page {$page}\n";
          sleep($delay);
          break;
        }
        $json = $parser->getReviewsJson($id, $page);
        $reviews = PJsonParser::getReviews($json);
        $db->addReviews($reviews);
        echo "page {$page} for id {$id} parsed\n";
        $page++;
        sleep($delay);
      }
    }
    else{
      $page = 1;
      while(true){
        $json = $parser->getReviewsJson($id, $page,$pokupki);
        $reviews = MJsonParser::getReviews($json);
        if(!$json){
          echo "Parse {$id} error on page {$page}\n";
          break;
        }
        if(!$reviews){
          echo "no reviews found on $id";
          break;
        }
        $db->addReviews($reviews);
        echo "page {$page} for id {$id} parsed\n";
        $page++;
        sleep($delay);
      }
    }
    echo "id {$id} parsed\n";
  }
}

start();

if(is_file(realpath('cookies.txt'))){
  unlink(realpath('cookies.txt'));
}
