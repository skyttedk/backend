<?php


echo time();
echo "<br>";
echo mktime(22,0,0,1,6,2020);
if(time() > mktime(23,0,0,1,6,2020)){
  echo "go";
}