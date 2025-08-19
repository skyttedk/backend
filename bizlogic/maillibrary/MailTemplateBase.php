<?php


abstract class MailTemplateBase {

    
    /* ABSTRACT FUNCTIONS */

    public abstract function getMailHandle();
    abstract protected function replaceData($key);

    /* CLASS MEMBERS */
    protected $template;

    protected $mailserver;

    /* CLASS CONSTRUCTOR */
    public function __construct($handle,$language_id,$shop_id=0,$company_id=0) {

        // Load mail template
        $this->template = LibraryLoader::getMailTemplateObject($handle,$language_id,$shop_id,$company_id);
        if($this->template == null) {
            throw new Exception("Could not find e-mail template: ".$handle);
        }

        // Load mailserver
        $this->mailserver = \MailServer::find($this->template->mailserver_id);

        // Process mail texts
        $this->processTemplate();

    }

    /* GETTERS */

    public function getLibraryID() {
        return $this->template->id;
    }

    public function getHandle() {
        return $this->template->handle;
    }

    public function getLanguageID() {
        return $this->template->language_id;
    }

    public function getMailShopID() {
        return $this->template->shop_id;
    }

    public function getMailCompanyID() {
        return $this->template->company_id;
    }

    /* SEND EMAIL */

    /**
     * Send e-mail
     * @param $recipientEmail
     * @param $recipientName
     * @param $orderID
     * @param $archive
     * @return void
     */
    public function sendEmail($recipientEmail,$recipientName="",$orderID=0,$archive=false) {


        $mailServer = \MailServer::find($this->getMailServerID());

        $maildata = [];
        $maildata['recipent_email'] = $recipientEmail;
        $maildata['order_id'] = $orderID;
        $maildata['sent'] = ($archive ? 1 : 0);

        // Load from mailserver
        $maildata['mailserver_id'] = 4;
        $maildata['sender_email'] = $mailServer->sender_email;
        $maildata["sender_name"] = $this->getSenderName();
        $maildata["recipent_name"] = $recipientName;
        $maildata["send_group"] = $this->getSendGroup();

        // Content
        $maildata['subject']= $this->getMailSubject();
        $maildata['body'] = $this->getMailBody();

        \MailQueue::createMailQueue($maildata,1);

    }

    private function getSender() {
        return $this->mailserver->sender_name;
    }

    private function getSendGroup() {
        return "lib:".$this->getLibraryID();
    }

    private function getMailSubject() {
        return $this->processedSubject;
    }

    private function getMailBody() {
        return $this->processedBody;
    }

    protected $predefinedEmail = "";
    protected $predefinedName = "";
    protected $predefinedOrder = "";

    public function hasPredefinedEmail() {
        return $this->predefinedEmail != "";
    }

    public function sendPredefinedEmail() {
        if(!$this->hasPredefinedEmail()) {
            return false;
        }
        $this->sendEmail($this->predefinedEmail,$this->predefinedName,$this->predefinedOrder,false);
        return true;
    }

    /* PROCESS TEMPLATE TEXTS */

    private $processedSubject = "";
    private $processedBody = "";

    /**
     * Process e-mail texts
     * @return void
     */
    private function processTemplate() {
        $this->processedBody = $this->processText($this->template->template);
        $this->processedSubject = $this->processText($this->template->subject);
    }

    private function processText($text) {
        return preg_replace_callback('/{{(.*?)}}/', function ($matches) {
            return $this->processReplace($matches[1]);
        }, $text);
    }

    /* REPLACING FUNCTIONALITY */

    private $cachedValues = array();

    public function addReplaceValue($key,$value) {
        $this->cachedValues[trim(strtolower($key))] = $value;
    }

    private $cachedObjects = array();

    public function addReplaceObject($name,$object) {
        $this->cachedObjects[trim(strtolower($name))] = $object;
    }

    private function processReplace($key) {

        // Look in cache
        if(isset($this->cachedValues[trim(strtolower($key))])) {
            return $this->cachedValues[trim(strtolower($key))];
        }

        // Look for objects
        if(strstr($key,".")) {
            $keyParts = explode(".",$key);
            if(count($keyParts) == 2 && trim($keyParts[0]) != "" && trim($keyParts[1]) != "" && isset($this->cachedObjects[trim(strtolower($keyParts[0]))])) {

                // Look for property in object and return it
                $object = $this->cachedObjects[trim(strtolower($keyParts[0]))];
                $attributeName = trim($keyParts[1]);
                if (property_exists($object, $attributeName)) {

                    $value = $object->$attributeName;
                    $this->addReplaceValue($key,$value);
                    return $value;

                }

            }
        }

        // Check if method exists and call it
        $methodName = "replace".$key;
        if (method_exists($this, $methodName)) {
            $value = $this->$key();
            $this->addReplaceValue($key,$value);
            return $value;

        }

        // Call object replacer
        $replacerValue = $this->replaceData($key);
        if($replacerValue != null && is_string($replacerValue)) {
            $this->addReplaceValue($key,$replacerValue);
            return $replacerValue;
        }

        // Return empty string
        $this->addReplaceObject($key,"");
        return "";

    }

}
