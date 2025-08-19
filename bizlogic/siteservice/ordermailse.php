<?php

namespace GFBiz\Siteservice;

class OrderMailSE extends ServiceHelper
{

    public function sendConfirmationEmail($contact_name,$contact_email)
    {

        $mailcontent = '<html>
  <head>

    <meta http-equiv="content-type" content="text/html; charset=utf-8">
  </head>
  <body text="#000000" bgcolor="#FFFFFF">
    <div class="moz-forward-container">
      <div class="WordSection1">
        <p class="MsoNormal">Hej '.$contact_name.'<o:p></o:p><o:p> </o:p>
        </p>
        <p class="MsoNormal">Tack för din beställning av presentkort. Vi behandlar din beställning så fort vi kan och skickar en orderbekräftelse inom några dagar.
          <o:p></o:p></p>
        <p class="MsoNormal">Om du har några frågor under tiden är du välkommen att kontakta oss på telefon: 0771-600005.<o:p></o:p><o:p> </o:p>
        </p>
        <p class="MsoNormal">Vi ser fram emot att få leverera presentkorten och gåvorna till er.<o:p></o:p></p>
        <p class="MsoNormal"><span
            style="color:black;mso-fareast-language:DA">Med vänlig hälsning<br>
            <b>PresentBolaget AB</b><i><br>
            </i></span><span
            style="font-size:9.0pt;mso-fareast-language:DA"><br>
          </span><br>
        </p>
        <span
          style="font-size:9.0pt;color:black;mso-fareast-language:DA"></span><span
          style="font-size:9.0pt;color:black;mso-fareast-language:DA"></span>
        <p class="MsoNormal" style="margin-bottom:12.0pt"><span
            style="font-size:8.0pt;color:black;mso-fareast-language:DA"> </span><span
            style="font-size:9.0pt;color:black;mso-fareast-language:DA"> </span>';

        $mailcontent .= '</p>
      </div>
    </div>
  </body>                                                  
</html>';


        $maildata = [];
        $maildata['sender_email'] =  "no-reply@presentbolaget.net";
        $maildata['recipent_email'] = $contact_email;
        $maildata['subject']= ("Tack för din beställning av presentkort");
        $maildata['body'] = ($mailcontent);
        $maildata['mailserver_id'] = 5;
        \MailQueue::createMailQueue($maildata);


    }

}