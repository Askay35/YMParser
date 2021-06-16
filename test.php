<?php

require_once 'parser.php';

$parser = new Parser();


function getHtml($url){
  $ch = curl_init();


  $headers = [
    "Cache-Control: no-cache",
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
    'Accept-Encoding: gzip, deflate',
    'Connection: keep-alive',
    'Pragma: no-cache',
    'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    'sec-ch-ua: " Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
    'sec-ch-ua-mobile: ?0',
    'Sec-Fetch-Dest: document',
    'Sec-Fetch-Mode: navigate',
    'Sec-Fetch-Site: none',
    'Sec-Fetch-User: ?1',
    'Upgrade-Insecure-Requests: 1'
  ];

  $opts = array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36",
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_ENCODING => "",
    CURLOPT_HEADER => 1,
    CURLOPT_MAXREDIRS => 30,
  );


  curl_setopt_array($ch,$opts);

  $res = curl_exec($ch);
  if(!$res){
    echo $url, "\n";
    echo curl_error($ch), "\n";
  }
  preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $res, $matches);
  $cookies = "Cookie: ";
  foreach($matches[1] as $item) {
      $cookies .= $item . "; ";
  }
  echo $res, "\n\n";
  echo $cookies;
  curl_close($ch);
}

$url = $parser->getUrl(530375023,1,0);
getHtml($url);
