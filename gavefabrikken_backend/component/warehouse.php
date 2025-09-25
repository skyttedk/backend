<?php

echo "hej";
$options = array('shop_id' => 3809);
$settings = WarehouseSettings::find('all', $options);
print_r($settings);

?>