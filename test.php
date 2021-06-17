<?php

function getHtml($url){
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
  $ch = curl_init();
  $opts = array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36",
    CURLOPT_ENCODING => "",
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_HEADER => 1,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_AUTOREFERER => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  );

  curl_setopt_array($ch,$opts);

  $res = curl_exec($ch);
  if(!$res){
    echo curl_error($ch), "\n";
  }
  echo $res;

  curl_close($ch);
  return $res;
}
//100956434277
//673263882
echo "\n\n\n\n",strlen(getHtml("https://market.yandex.ru/product--videoregistrator-70mai-rearview-dash-cam-wide-midrive-d07-2-kamery/673263882/reviews?page=1"));
