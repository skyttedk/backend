<?php

// Controller SystemLog
// Date created  Wed, 13 Apr 2016 20:48:50 +0200
// Created by Bitworks
class lastyearController Extends baseController {

  public function cardShopSale() {
 echo    $sql = "SELECT company.name, COUNT(*) as antal  FROM `shop_user`
INNER JOIN company ON
`shop_user`.`company_id` = company.id

WHERE `shop_user`.`company_id` = ".$_POST["company_id"]."
AND
`shop_user`.`blocked` = 0
GROUP BY
`shop_user`.`shop_id`";
  //  Dbsqli::getSql($sql);
  //  $dummy = array();
  //  response::success(make_json("result", $dummy));
  }

}
  
?>