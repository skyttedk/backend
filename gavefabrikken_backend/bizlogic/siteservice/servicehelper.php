<?php

namespace GFBiz\Siteservice;

class ServiceHelper
{


    /**
     * AUTH HELPERS
     */

    protected $currentAuthCode = null;
    protected $currentAuthDetails = null;

    protected function getAuthorizationList()
    {
        return array(
            "IHdnBJszrRfKxrWdG5SHdzLzpcedaBdeREtd6RlK" => array("actions" => array("all"), "concepts" => array("all"),"language" => array(1,4,5))
        );
    }

    protected function hasAuthCardshopSetting($cardshopSetting)
    {

        if($this->currentAuthDetails == null) return false;
        if(!in_array($cardshopSetting->language_code,$this->currentAuthDetails["language"])) return false;

        if(!in_array("all",$this->currentAuthDetails["concepts"])) {
            if(!in_array($cardshopSetting->concept_parent,$this->currentAuthDetails["concepts"])) return false;
        }

        return true;
    }

    public function authorize()
    {

        // Get data
        $authList = $this->getAuthorizationList();
        $authHeader = $this->getAuthorizationHeader();

        // If header empty, error
        if($authHeader == null || trimgf($authHeader) == "") {
            $this->outputServiceError(10,"No authorization header set");
            return false;
        }

        if(!isset($authList[$authHeader])) {
            $this->outputServiceError(10,"Invalid authorization header");
            return false;
        }

        $this->currentAuthCode = $authHeader;
        $this->currentAuthDetails = $authList[$authHeader];
        return true;

    }

    protected function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trimgf($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trimgf($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trimgf($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * OUTPUT HELPERS
     */

    protected function outputServiceError($errorCode,$errorMessage,$terminate=true,$data=null)
    {

        header('Content-Type: application/json');
        if(!is_array($data)) $data = array();
        echo json_encode(array_merge(array("status" => $errorCode,"error" => $errorMessage),$data));

        
        $this->mailLog("Web order error",print_r(array_merge(array("status" => $errorCode,"error" => $errorMessage),$data),true));

        $weborderlog = new \WebOrderLog();
        $weborderlog->error = $errorMessage;
        $weborderlog->input = json_encode($_POST);
        $weborderlog->output = json_encode(array_merge(array("status" => $errorCode,"error" => $errorMessage),$data));
        $weborderlog->orderid = 0;
        $weborderlog->shop_id = isset($_POST["shop_id"]) ? intval($_POST["shop_id"]) : 0;
        $weborderlog->url = $_SERVER["REQUEST_URI"];
        $weborderlog->save();
        
        if($terminate == true) {
            \system::connection()->commit();
            exit();
        }

    }

    protected function outputServiceSuccess($data)
    {

        header('Content-Type: application/json');
        if(!is_array($data)) $data = array();
        echo json_encode(array_merge(array("status" => 1),$data));

    }

    protected function isJSONRequest()
    {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? strtolower($_SERVER["CONTENT_TYPE"]) : "";
        return ($contentType == "application/json" || $contentType == "text/json");
    }

    protected function mailLog($subject,$content)
    {

        $modtager = "sc@interactive.dk";
        $message = "Extservice mail log<br><br>".$content."\r\n<br>\r\n<br>Data:<br>\r\n<pre>".print_r($_POST,true)."</pre>";
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";

        $result = mail($modtager,"extlog: ".$subject, $message, $headers);

    }
}
