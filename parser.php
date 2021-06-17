<?php

class Formater{
  static public function getDateFromTs($ts)
  {
    return date('Y-m-d', substr($ts, 0, strlen($ts)-3));
  }
}

class Parser{

  public function __construct($proxies=false){

    if($proxies!=false){
      $this->proxies = $proxies;
      $this->proxyid = 0;
      $this->proxy = $this->proxies[$this->proxyid];
      $this->lastproxy = sizeof($proxies)-1;
      $this->useproxylist = true;
    }
    else{
      $this->proxy = "";
      $this->useproxylist = false;
      $this->switchProxy();
    }
    $this->headers = [
      'Accept: */*',
      'Accept-Encoding: gzip, deflate',
      'Connection: keep-alive',
    ];
  }

  private function switchProxy()
  {
    if(is_file(realpath('cookies.txt'))){
      unlink(realpath('cookies.txt'));
    }
    $apiurl = "https://api.getproxylist.com/proxy?allowsCustomHeaders=1&allowsUserAgentHeader=1&allowsCookies=1&allowsCustomHeaders=1";

    if($this->useproxylist){
      $this->proxyid++;
      if($this->proxyid > $this->lastproxy){
        $this->proxyid = 0;
      }
      $this->proxy = $this->proxies[$this->proxyid];
    }
    else{
      $res = file_get_contents($apiurl);
      if(!$res){
        echo "proxy limit\n";
        sleep(5);
        exit();
      }
      $apidata = json_decode($res, true);
      if(isset($apidata['ip'])){
        $this->proxy = "{$apidata['ip']}:{$apidata['port']}";
      }
      else{
        echo "proxy limit\n";
        sleep(5);
        exit();
      }
    }
  }

  public function getHtml($url){
    $ch = curl_init();

    $opts = array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36",
      CURLOPT_HTTPHEADER => $this->headers,
      CURLOPT_ENCODING => "",
      CURLOPT_PROXY => $this->proxy,
      CURLOPT_HEADER => 1,
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_COOKIEJAR => 'cookies.txt',
      CURLOPT_COOKIEFILE => 'cookies.txt',
      CURLOPT_CONNECTTIMEOUT => 60
    );

    echo "using proxy $this->proxy\n";

    curl_setopt_array($ch,$opts);
    $res = curl_exec($ch);
    if(!$res){
      echo "timeout, switching proxy $this->proxy\n";
      $this->switchProxy();
      curl_close($ch);
      return $this->getHtml($url);
    }
    if(strlen($res)<30000){
      echo "get captcha\n";
      $this->switchProxy();
      curl_close($ch);
      return $this->getHtml($url);
    }


    curl_close($ch);
    return $res;
  }

  public function getUrl($id, $pagenum,$pokupki)
  {
    if($pokupki)
    {
      $url = "https://pokupki.market.yandex.ru/product/{$id}/reviews?page={$pagenum}";
      return $url;
    }
    $url = "https://market.yandex.ru/product/{$id}/reviews?page={$pagenum}";
    return $url;
  }

  public function getReviewsJson($id, $pagenum, $pokupki){
    $url = $this->getUrl($id, $pagenum,$pokupki);
    $html = $this->getHtml($url);
    if(!$html){
      return false;
    }
    $json = $this->getJson($html);
    if(!$json){
      echo "no reviews\n";
      return false;
    }
    return $json;
  }

  private function searchPokupkiJson($search, $matches)
  {
    $json = false;
    $searchlen = strlen($search);
    foreach ($matches as $match) {
      $subst = substr($match, 0, $searchlen);
      if($subst==$search){
        $json = json_decode($match, true)['collections'];
        break;
      }
    }
    return $json;
  }


  private function getJson($html){
    $matches = array_values(array_filter(preg_split('/(?<=(apiary-patch">))|(?=<\/noframes)/m', $html), function($k){
      return $k%2!=0;
    }, ARRAY_FILTER_USE_KEY));
    if(sizeof($matches)==0){
      return false;
    }
    $search = '{"widgets":{"@marketplace/ProductReviews"';
    $json = $this->searchPokupkiJson($search,$matches);
    if(!$json){
      $search = '{"widgets":{"@MarketNode/ProductReviewsList"';
      $json = $this->searchPokupkiJson($search, $matches);
    }
    return $json;
  }
}

class MJsonParser{

  static public function getReviews($json){
      $reviews = self::parseReviews($json);
      if(!$reviews){
        echo "no reviews\n";
        return false;
      }
      for ($i=0; $i < sizeof($reviews); $i++)
      {
        $reviews[$i]['review_author'] = "";
        $uid = $reviews[$i]['uid'];
        if($uid!=""){
          if(isset($json['publicUser'])){
            if(isset($json['publicUser'][$uid])){
              $reviews[$i]['review_author'] = $json['publicUser'][$uid]['publicDisplayName'];
            }
            else{
              echo "anon user\n";
            }
          }
          else{
            echo "no user array\n";
          }
        }
      }
      return $reviews;
  }

  static private function parseReviews(&$json)
  {
    $reviews = [];
    if(!isset($json['review'])){
      return false;
    }
    $reviewsSrc = $json['review'];
    $reviewsIds = array_keys($reviewsSrc);
    for ($i=0; $i < sizeof($reviewsSrc); $i++) {
        array_push($reviews, self::formatReview($reviewsSrc[$reviewsIds[$i]]));
    }
    return $reviews;
  }

  static private function formatReview($reviewObj){
    $userId = "";
    if(isset($reviewObj['anonymous'])){
      if($reviewObj['anonymous'] == 0){
        $userId = strval($reviewObj['userId']);
      }
    }
    $review_date = Formater::getDateFromTs($reviewObj['created']);
    $productId = $reviewObj['productId'];
    $review_id = intval($reviewObj['id']);
    $review_grade = $reviewObj['averageGrade'];
    $review_good = $reviewObj['pro'];
    $review_bad = $reviewObj['contra'];
    $review_comment = $reviewObj['comment'];
    $review = ["review_id"=>$review_id, "review_comment"=>$review_comment,"review_good"=>$review_good,"review_bad"=>$review_bad, "review_grade"=>$review_grade,"model_id"=>$productId, "review_date"=>$review_date, 'uid'=>$userId];
    return $review;
  }
}

class PJsonParser{

  static public function getPagesCount(&$json){
    if(isset($json['reviewsResult'])){
      return $json['reviewsResult'][array_key_first($json['reviewsResult'])]['pager']['totalPageCount'];
    }
    return false;
  }

  static public function getReviews($json){
      $reviews = self::parseReviews($json);
      if(!$reviews){
        echo "no reviews\n";
        return false;
      }
      for ($i=0; $i < sizeof($reviews); $i++)
      {
        if($reviews[$i]['uid']!=0){
          if(isset($json['user'])){
            $reviews[$i]['review_author'] = $json['user'][$reviews[$i]['uid']]['displayName'];
          }
          else{
            echo "no user array\n";
          }
        }
        else{
          $reviews[$i]['review_author'] = "";
        }
      }
      return $reviews;
  }

  static private function parseReviews(&$json)
  {
    $reviews = [];
    if(!isset($json['review'])){
      return false;
    }
    $reviewsSrc = $json['review'];
    $reviewsIds = array_keys($reviewsSrc);
    for ($i=0; $i < sizeof($reviewsSrc); $i++) {
        array_push($reviews, self::formatReview($reviewsSrc[$reviewsIds[$i]]));
    }
    return $reviews;
  }

  static private function formatReview($reviewObj){
    $userId = 0;
    if(isset($reviewObj['anonymous'])){
      if($reviewObj['anonymous'] == 0){
        $userId = $reviewObj['userId'];
      }
    }
    else{
      if(isset($reviewObj['isAnonymous'])){
        if($reviewObj['isAnonymous'] == 0){
          if(isset($reviewObj['userUid'])){
            $userId = $reviewObj['userUid'];
          }
          else{
            $userId = $reviewObj['userId'];
          }
        }
      }
    }
    $review_date = Formater::getDateFromTs($reviewObj['created']);
    $productId = $reviewObj['productId'];
    $review_id = intval($reviewObj['id']);
    $review_grade = $reviewObj['averageGrade'];
    $review_good = $reviewObj['pro'];
    $review_bad = $reviewObj['contra'];
    $review_comment = $reviewObj['comment'];
    $review = ["review_id"=>$review_id, "review_comment"=>$review_comment,"review_good"=>$review_good,"review_bad"=>$review_bad, "review_grade"=>$review_grade,"model_id"=>$productId, "review_date"=>$review_date, 'uid'=>$userId];
    return $review;
  }
}
