<?php
class router {
//echo time_elapsed();
 private $registry;
 private $path;
 private $args = array();
 public $file;
 public $controller;
 public $action;

 //Globals
 public static $userType;
 public static $username;
 public static $systemLogSkip;
 public static $systemLogId;
 public static $systemLogAction;
 public static $systemLogController;
 function __construct($registry) {
   $this->registry = $registry;
 }

 function setPath($path) {

	/*** check if path i sa directory ***/
	if (is_dir($path) == false)
	{
		throw new Exception ('Invalid controller path: `' . $path . '`');
	}
	/*** set the path ***/
 	$this->path = $path;
}

 public function loader()
 {
	// echo time_elapsed();


	/*** check the route ***/
	$this->getController();

	/*** if the file is not there diaf ***/
	if (is_readable($this->file) == false)
	{
		$this->file = $this->path.'/error404.php';
                $this->controller = 'error404';
	}

	/*** include the controller ***/
	include $this->file;

	/*** a new controller class instance ***/
	$class = $this->controller . 'Controller';
	$controller = new $class($this->registry);

	/*** check if the action is callable ***/
	if (is_callable(array($controller, $this->action)) == false)
	{
		$action = 'index';
	}
	else
	{
		$action = $this->action;
	}

    try
        {
			if(!isset($_POST['logintype']))   // shop/backend logintype=userType
			  $_POST['logintype'] = 'backend';

			router::$systemLogSkip==false;
			router::$systemLogController = $this->controller;
            router::$systemLogAction = $action;
			router::$userType = $_POST['logintype'];  // shop,backend

			//A
			if(router::$systemLogAction=="getMailStats") // skal senere blot tjekkes på em der er en servicebruger
			  router::$systemLogSkip = true;




			//Brug pt altid kss som backenduser
			if(router::$userType=="backend")
			{                                    
                $systemuser = SystemUser::all(array('username' => 'skytte_dk'));
				$systemuser = $systemuser[0];
				$_POST['token'] = "59d98ef3-eba0-4d04-a173-236f5ad8b9fa";
			}


    		//echo time_elapsed();
			if(router::$systemLogController!=="login") {  // tillad altid adgang til login controller

                //throw exception("whoot");

				// if not login controller called, we expect a token
				if(router::$userType=="backend") {
					/*
				//login backend user
					$systemUser = Login::testToken(router::$userType,$_POST['token']); // check if we have a valid token
					if(isset($systemUser)) {
			  	      router::$username = $systemUser->username;
				      if($systemUser->is_service_user==1)
					  {
						router::$systemLogSkip = true;
					  }	 else {
							//Ugyldig token
							throw new loginException("Please Login");  // Generate login Exception
						}
					}
						*/
				}	else if(router::$userType=="shop") {
						if(!isset($_POST['token']))
							throw new loginException("Invalid token");  // Generate login Exception
						$shopUser = Login::testToken(router::$userType,$_POST['token']); // check if we have a valid token
						if(!isset($shopUser)) {
							throw new loginException("Please Login");
						}
					//login shop user
				}
			}


			//echo time_elapsed();

			if(router::$systemLogController=="SystemLog") {
				router::$systemLogSkip = true;
			}

			//at this point we have a token

			//Update SystemUser last login  <<<< hmme denne er vist ikke nødvendig
			//
		   // if(router::$userType=="backend") {
           //   System::connection()->transaction();
         //	  $systemUser->last_login = date('d-m-Y H:i:s');
		//	  $systemUser->Save();
		//	  system::connection()->commit();
		//	}

			//Lav UNSET af token og logintype, så de ikke forstyrrer dataset.
			unset($_POST['logintype']);
			unset($_POST['token']);

            // Create a System Log Entry
            if(router::$systemLogSkip==false) {
                System::connection()->transaction();
                $systemlog = new SystemLog();
                $systemlog->user_id = router::$username;
                $systemlog->controller = $this->controller;
                $systemlog->action = $action;
                $systemlog->data = json_encode ($_POST);
                $systemlog->save();
				//noget user id herer ????
                router::$systemLogId = $systemlog->id;
                System::connection()->commit();
            }

			//Handle Request
            System::connection()->transaction();
			$controller->$action();


        }   catch(loginException $lex) {
			response::loginRequest();
		}
		catch(Exception $ex)  {

		//Rollback TRansaction
		 try {System::connection()->rollback();} catch(Exception $ex2)  {};

		 // Update system Log With Exception Message
		 System::connection()->transaction();
		 $systemlog = SystemLog::find(router::$systemLogId);
 		 try {
				$systemlog->error_message = $ex->getMessage();
				$systemlog->error_trace = $ex->getTraceAsString();
				$systemlog->save();
				System::connection()->commit();
		} catch(Exception $ex2)  {};


			//Create Error Response
           $system = System::first();

           if($system->full_trace == 1) {
                response::error($ex->getTraceAsString());
           }   else {
                response::error($ex->getMessage());
           }



        }
 }

 /**
 *
 * @get the controller
 *
 * @access private
 *
 * @return void
 *
 */
private function getController() {

	/*** get the route from the url ***/
	$route = (empty($_GET['rt'])) ? '' : $_GET['rt'];

	if (empty($route))
	{
	   oute = 'index';
	}
	else
	{
		/*** get the parts of the route ***/
		$parts = explode('/', $route);
		$this->controller = $parts[0];
		if(isset( $parts[1]))
		{
			$this->action = $parts[1];
		}
	}

	if (empty($this->controller))
	{
		$this->controller = 'index';
	}

	if (empty($this->action))
	{
		$this->action = 'index';
	}

	$this->file = $this->path .'/'. $this->controller . 'Controller.php';
    //echo - ($this->file);



}


}
?>