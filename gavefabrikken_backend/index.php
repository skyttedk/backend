<?php



/*** error reporting on ***/

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


 /*** define the site path ***/
 $site_path = realpath(dirname(__FILE__));

 define ('__SITE_PATH', $site_path);



 /*** include the init.php file ***/
 include 'includes/init.php';

 /*** load the router ***/
 $registry->router = new router($registry);

 /*** set the controller path ***/
 $registry->router->setPath (__SITE_PATH . '/controller');

 /*** load up the template ***/
 $registry->template = new template($registry);

 /*** load the controller ***/
 $registry->router->loader();
 // echo "System stopped"
?>
