<?php

echo "<table>";

foreach (apache_request_headers() as $k => $v){
  echo "<tr><td style=\"border: 2px solid black;\">$k</td><td style=\"border: 2px solid black;\">$v</td></tr>";
}
echo "</table>";
