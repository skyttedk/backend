<?php

include __SITE_PATH . '/includes/config.php';
include __SITE_PATH . '/../vendor/autoload.php';

include __SITE_PATH . '/lib/' . 'ActiveRecord.php';
include __SITE_PATH . '/lib/' . 'BaseModel.php';
include __SITE_PATH . '/lib/' . 'Profile.php';
include __SITE_PATH . '/lib/' . 'jwt.php';

include __SITE_PATH . '/application/' . 'controller_base.class.php';
include __SITE_PATH . '/application/' . 'report_base.class.php';
include __SITE_PATH . '/application/' . 'registry.class.php';
include __SITE_PATH . '/application/' . 'router.class.php';
include __SITE_PATH . '/application/' . 'template.class.php';

include __SITE_PATH . '/thirdparty/phpmailer/' . 'class.phpmailer.php';
include __SITE_PATH . '/thirdparty/phpmailer/' . 'class.smtp.php';
include __SITE_PATH . '/thirdparty/phpexcel/' . 'PHPExcel.php';


include __SITE_PATH . '/../gavefabrikken_common/utils/bootstrap.php';


 function gfAutoloader($class_name) {

     $parts = explode("\\",$class_name);
     if(strtolower($parts[0]) == "gfunit")
     {
         array_shift($parts);
         $filename = array_pop($parts);
         $commonPath = realpath(dirname(__FILE__)."/..");
         $filename =  $commonPath."/units/".strtolower(implode("/",$parts))."/".$filename.".php";
         if(file_exists($filename)) include($filename);
         else if(file_exists(strtolower($filename))) include(strtolower($filename));
         else echo "Could not find: ".$filename;
     }
     if(strtolower($parts[0]) == "gfapp")
     {
         array_shift($parts);
         $filename = array_pop($parts);
         $commonPath = realpath(dirname(__FILE__)."/..");
         $filename =  $commonPath."/app/".strtolower(implode("/",$parts))."/".$filename.".php";
         if(file_exists($filename)) include($filename);
         else if(file_exists(strtolower($filename))) include(strtolower($filename));
         else echo "Could not find: ".$filename;
     }
     if(strtolower($parts[0]) == "gfbiz")
     {
         array_shift($parts);
         $filename = array_pop($parts);
         $commonPath = realpath(dirname(__FILE__)."/..");
         $filename =  $commonPath."/bizlogic/".strtolower(implode("/",$parts))."/".$filename.".php";
         if(file_exists($filename)) include($filename);
         else if(file_exists(strtolower($filename))) include(strtolower($filename));
         else echo "Could not find: ".$filename;
     }


   $filename = strtolower($class_name) . '.class.php';
   $modelFilePath = __SITE_PATH . '/model/' . $filename;
   $reportFilePath = __SITE_PATH . '/reports/' . $filename;
   if(file_exists($modelFilePath)) {
     include($modelFilePath);
   } elseif (file_exists($reportFilePath)){
     include($reportFilePath);
   }
 }


spl_autoload_register('gfAutoloader');

$registry = new registry;


// Setup active records
ActiveRecord\Config::initialize(function($cfg)
{

    $cfg->set_model_directory(dirname(__FILE__) . '/../model');
    $cfg->set_connections(array(
        "default" => "mysql://".GFConfig::DB_USERNAME.":".GFConfig::DB_PASSWORD."@".GFConfig::DB_HOST."/".GFConfig::DB_DATABASE."?charset=utf8"
     ));

    $cfg->set_default_connection('default');
    $cfg->set_date_format( "Y-m-d" );

});

// Setup common
setupGFCommon(GFConfig::DB_HOST,GFConfig::DB_USERNAME,GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);

$jwtSecret = 'e9FkQ7X2pL9mJ3vN6wR8bT4uH5yC1zA0sG3dK6fE8xW7'; 
$registry->jwt = new JWT($jwtSecret);



?>
