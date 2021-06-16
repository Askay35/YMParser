<?php

require_once 'parser.php';
require_once 'db.php';
require_once 'input.php';




function getAllReviews(){
  $parser = new Parser();
  $settings = json_decode(file_get_contents('settings.json'),true);
  $db = new DB($settings['host'], $settings['db'], $settings['user'], $settings['password']);
  $ids = getIds($settings['ids_file']);
  foreach ($ids as $id) {
    echo "parsing id $id \n";
    $json = $parser->getReviewsJson($id, 1);
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
    $page = $pagecount-2;
    while($page < $pagecount){
      if(!$json){
        echo "Parse {$id} error on page {$page}\n";
        sleep(60);
        break;
      }
      $json = $parser->getReviewsJson($id, $page);
      $reviews = PJsonParser::getReviews($json);
      $db->addReviews($reviews);
      echo "page {$page} for id {$id} parsed\n";
      $page++;
      sleep(60);
    }
    echo "id {$id} parsed\n";
  }
}

getAllReviews();
