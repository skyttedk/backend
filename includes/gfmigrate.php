<?php

/*
 * GF MIGRATE FUNCTIONS
 * Disse funktioner er lavet til at håndtere problemer i funktioner ved skift fra 5.6 til 8.1
 */

function trimgf($string,$characters=" \n\r\t\v\x00")
{
    if($string === null) $string = "";
    return trim($string,$characters);
}

function countgf($value,$mode=COUNT_NORMAL)
{
    if($value === null || !is_countable($value)) {
        if($value === null) return 0;
        else if(is_object($value)) return 1;
        else return 0;
    }
    return count($value,$mode);
}

function sizeofgf($value,$mode=COUNT_NORMAL)
{
    if($value === null || !is_countable($value)) {
        if($value === null) return 0;
        else if(is_object($value)) return 1;
        else return 0;
    }
    return count($value,$mode);
}

function htmlspecialcharsgf($string) {
    if($string === null) $string = "";
    return htmlspecialchars($string);
}

function strstrgf($haystack,$needle,$before_needle=null) {
    if($haystack === null) $haystack = "";
    if($needle === null) $needle = "";
    return strstr($haystack,$needle,$before_needle);
}

function strlengf($string) {
    if($string === null) return 0;
    if(is_string($string)) return strlen($string);
    return null;
}

function intvalgf($val) {
    if($val === null) return 0;
    else return intval($val);
}

// Direct mailer
function mailgf($to,$subject,$message,$headers=null,$parameters=null)
{

    try {

        $mail = new Email('smtp.mandrillapp.com', 587);
        $mail->setProtocol(Email::TLS)
            ->setLogin('GaveFabrikken A/S', 'fQr2Re5wZHH4y-edenFHoA')
            ->setFrom('noreply@gavefabrikken.net')
            ->setSubject($subject)
            ->setTextMessage(strip_tags($message))
            ->setHtmlMessage($message);

        $receivers = 0;
        if(is_array($to)) {
            foreach($to as $toi) {
                $mail->addTo($toi);
                $receivers++;
            }
        } else if(is_string($to)) {
            $toparts = explode(strstr($to,";") ? ";" : ",",$to);
            foreach($toparts as $toi) {
                $mail->addTo($toi);
                $receivers++;
            }
        }
        else return false;
        if($receivers == 0) return false;

        $mail->send();
        return true;

    }
    catch (Exception $e) {
        return false;
    }

}

/** MINIFIED VERSION OF EMAIL CLASS: https://github.com/snipworks/php-smtp */
class Email{const CRLF="\r\n";const TLS='tcp';const SSL='ssl';const OK=250;protected $server;protected $hostname;protected $port;protected $socket;protected $username;protected $password;protected $connectionTimeout;protected $responseTimeout;protected $subject;protected $to=array();protected $cc=array();protected $bcc=array();protected $from=array();protected $replyTo=array();protected $attachments=array();protected $protocol=null;protected $textMessage=null;protected $htmlMessage=null;protected $isHTML=false;protected $isTLS=false;protected $logs=array();protected $charset='utf-8';protected $headers=array();public function __construct($server,$port=25,$connectionTimeout=30,$responseTimeout=8,$hostname=null){$this->port=$port;$this->server=$server;$this->connectionTimeout=$connectionTimeout;$this->responseTimeout=$responseTimeout;$this->hostname=empty($hostname)?gethostname():$hostname;$this->setHeader('X-Mailer','PHP/'.phpversion());$this->setHeader('MIME-Version','1.0');}
    public function setHeader($key,$value=null){$this->headers[$key]=$value;return $this;}
    public function addTo($address,$name=null){$this->to[]=array($address,$name);return $this;}
    public function addCc($address,$name=null){$this->cc[]=array($address,$name);return $this;}
    public function addBcc($address,$name=null){$this->bcc[]=array($address,$name);return $this;}
    public function addReplyTo($address,$name=null){$this->replyTo[]=array($address,$name);return $this;}
    public function addAttachment($attachment){if(file_exists($attachment)){$this->attachments[]=$attachment;}
        return $this;}
    public function setLogin($username,$password){$this->username=$username;$this->password=$password;return $this;}
    public function setCharset($charset){$this->charset=$charset;return $this;}
    public function setProtocol($protocol=null){if($protocol===self::TLS){$this->isTLS=true;}
        $this->protocol=$protocol;return $this;}
    public function setFrom($address,$name=null){$this->from=array($address,$name);return $this;}
    public function setSubject($subject){$this->subject=$subject;return $this;}
    public function setTextMessage($message){$this->textMessage=$message;return $this;}
    public function setHtmlMessage($message){$this->htmlMessage=$message;return $this;}
    public function getLogs(){return $this->logs;}
    public function send(){  $message=null;$this->socket=fsockopen($this->getServer(),$this->port,$errorNumber,$errorMessage,$this->connectionTimeout);if(empty($this->socket)){return false;}
        $this->logs['CONNECTION']=$this->getResponse();$this->logs['HELLO'][1]=$this->sendCommand('EHLO '.$this->hostname);if($this->isTLS){$this->logs['STARTTLS']=$this->sendCommand('STARTTLS');stream_socket_enable_crypto($this->socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT);$this->logs['HELLO'][2]=$this->sendCommand('EHLO '.$this->hostname);}
        $this->logs['AUTH']=$this->sendCommand('AUTH LOGIN');$this->logs['USERNAME']=$this->sendCommand(base64_encode($this->username));$this->logs['PASSWORD']=$this->sendCommand(base64_encode($this->password));$this->logs['MAIL_FROM']=$this->sendCommand('MAIL FROM: <'.$this->from[0].'>');$recipients=array_merge($this->to,$this->cc,$this->bcc);foreach($recipients as $address){$this->logs['RECIPIENTS'][]=$this->sendCommand('RCPT TO: <'.$address[0].'>');}
        $this->setHeader('Date',date('r'));$this->setHeader('Subject',$this->subject);$this->setHeader('From',$this->formatAddress($this->from));$this->setHeader('Return-Path',$this->formatAddress($this->from));$this->setHeader('To',$this->formatAddressList($this->to));if(!empty($this->replyTo)){$this->setHeader('Reply-To',$this->formatAddressList($this->replyTo));}
        if(!empty($this->cc)){$this->setHeader('Cc',$this->formatAddressList($this->cc));}
        if(!empty($this->bcc)){$this->setHeader('Bcc',$this->formatAddressList($this->bcc));}
        $boundary=md5(uniqid(microtime(true),true));$this->setHeader('Content-Type','multipart/mixed; boundary="mixed-'.$boundary.'"');if(!empty($this->attachments)){$this->headers['Content-Type']='multipart/mixed; boundary="mixed-'.$boundary.'"';$message.='--mixed-'.$boundary.self::CRLF;$message.='Content-Type: multipart/alternative; boundary="alt-'.$boundary.'"'.self::CRLF.self::CRLF;}else{$this->headers['Content-Type']='multipart/alternative; boundary="alt-'.$boundary.'"';}
        if(!empty($this->textMessage)){$message.='--alt-'.$boundary.self::CRLF;$message.='Content-Type: text/plain; charset='.$this->charset.self::CRLF;$message.='Content-Transfer-Encoding: base64'.self::CRLF.self::CRLF;$message.=chunk_split(base64_encode($this->textMessage)).self::CRLF;}
        if(!empty($this->htmlMessage)){$message.='--alt-'.$boundary.self::CRLF;$message.='Content-Type: text/html; charset='.$this->charset.self::CRLF;$message.='Content-Transfer-Encoding: base64'.self::CRLF.self::CRLF;$message.=chunk_split(base64_encode($this->htmlMessage)).self::CRLF;}
        $message.='--alt-'.$boundary.'--'.self::CRLF.self::CRLF;if(!empty($this->attachments)){foreach($this->attachments as $attachment){$filename=pathinfo($attachment,PATHINFO_BASENAME);$contents=file_get_contents($attachment);$type=mime_content_type($attachment);if(!$type){$type='application/octet-stream';}
            $message.='--mixed-'.$boundary.self::CRLF;$message.='Content-Type: '.$type.'; name="'.$filename.'"'.self::CRLF;$message.='Content-Disposition: attachment; filename="'.$filename.'"'.self::CRLF;$message.='Content-Transfer-Encoding: base64'.self::CRLF.self::CRLF;$message.=chunk_split(base64_encode($contents)).self::CRLF;}
            $message.='--mixed-'.$boundary.'--';}
        $headers='';foreach($this->headers as $k=>$v){$headers.=$k.': '.$v.self::CRLF;}
        $this->logs['MESSAGE']=$message;$this->logs['HEADERS']=$headers;$this->logs['DATA'][1]=$this->sendCommand('DATA');$this->logs['DATA'][2]=$this->sendCommand($headers.self::CRLF.$message.self::CRLF.'.');$this->logs['QUIT']=$this->sendCommand('QUIT');fclose($this->socket);return substr($this->logs['DATA'][2],0,3)==self::OK;}
    protected function getServer(){return($this->protocol)?$this->protocol.'://'.$this->server:$this->server;}
    protected function getResponse(){$response='';stream_set_timeout($this->socket,$this->responseTimeout);while(($line=fgets($this->socket,515))!==false){$response.=trim($line)."\n";if(substr($line,3,1)==' '){break;}}
        return trim($response);}
    protected function sendCommand($command){fputs($this->socket,$command.self::CRLF);return $this->getResponse();}
    protected function formatAddress($address){return(empty($address[1]))?$address[0]:'"'.addslashes($address[1]).'" <'.$address[0].'>';}
    protected function formatAddressList(array $addresses){$data=array();foreach($addresses as $address){$data[]=$this->formatAddress($address);}
        return implode(', ',$data);}}
