<?php
class routing
{
    private $requestMethod;
    private $requestClass;
    private $classObj;

    public function __construct($path) {
       $pathPart = explode("/",$path);
       $sizeOfPath = sizeofgf($pathPart);
       $this->requestMethod = (isset($pathPart[$sizeOfPath-1])) ? $pathPart[$sizeOfPath-1] : "";
       $this->requestClass = (isset($pathPart[$sizeOfPath-2])) ? $pathPart[$sizeOfPath-2] : "";
       if($this->requestClass == "" || $this->requestMethod == "") { throw new Exception('Route not correct'); };
    }
    public function run(){
        // inset evt middle layer or security check
        $this->autoload();
        return $this->callMethod();
    }
    private function callMethod()
    {
        return call_user_func( array( $this->classObj, $this->requestMethod ) );
    }

    private function autoload()
    {
        $classPath =  constant("routeUrl") . $this->requestClass . ".controller.php";
        if(file_exists( $classPath )) {
            require_once( $classPath );
            $class = $this->requestClass."Controller";
            $this->classObj = new $class;
            if (method_exists ( $this->classObj , $this->requestMethod )){
                return $this;
            } else {
                throw new Exception('Function not definded');
            }
        } else {
                throw new Exception('Class not definded');
        }
    }
}











?>