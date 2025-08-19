<?php

namespace GFBiz\Siteservice;

class OrderMailDA extends ServiceHelper
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
        <p class="MsoNormal">Tak for din bestilling af gavekort. Vi behandler din ordre hurtigst muligt og sender en ordrebekræftelse hurtigst muligt.
          <o:p></o:p></p>
        <p class="MsoNormal">Skulle du have spørgsmål i mellemtiden, er du meget velkommen til at kontakte os på telefon 70 70 20 27.<o:p></o:p><o:p> </o:p>
        </p>
        <p class="MsoNormal">Vi glæder os til at levere gavekort og gaverne.<o:p></o:p></p>
        <p class="MsoNormal"><span
            style="color:black;mso-fareast-language:DA">Med venlig
            hilsen<br>
            <b>GaveFabrikken A/S</b><i><br>
            </i></span><span
            style="font-size:9.0pt;mso-fareast-language:DA"><br>
          </span><span style="color:black;mso-fareast-language:DA">Gavekort-teamet<o:p></o:p></span><span
            style="font-size:9.0pt;color:black;mso-fareast-language:DA"><br>
            </span><br>
        </p>
        <span
          style="font-size:9.0pt;color:black;mso-fareast-language:DA"></span><span
          style="font-size:9.0pt;color:black;mso-fareast-language:DA"></span>
        <p class="MsoNormal" style="margin-bottom:12.0pt"><span
            style="font-size:9.0pt;color:black;mso-fareast-language:DA">Telefon
                 </span><span
            style="font-size:8.0pt;color:black;mso-fareast-language:DA"> </span><span
            style="font-size:9.0pt;color:black;mso-fareast-language:DA"> </span><span
            style="font-size:9.0pt;mso-fareast-language:DA">
            <span style="color:black"> (+45) 70 70 20 27<br>
              <br>
            </span></span><span style="mso-fareast-language:DA"><a
              href="http://www.gavefabrikken.dk/" moz-do-not-send="true"><span
                style="font-size:9.0pt;color:blue">www.gavefabrikken.dk</span></a></span><span
style="font-size:9.0pt;color:#AEAAAA;mso-fareast-language:DA">
          </span><span style="font-size:9.0pt;mso-fareast-language:DA"><o:p></o:p></span></p>
        <p class="MsoNormal" style="margin-bottom:12.0pt"><span
            style="mso-fareast-language:DA"><img
              style=""
              id="Billede_x0020_1"
              src="data:image/jpeg;base64,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"
              alt="logo" class="" border="0"></span><span
            style="font-size:9.0pt;mso-fareast-language:DA"><o:p></o:p></span></p>
       
      </div>
    </div>
  </body>                                                  
</html>';



        $maildata = [];
        $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
        $maildata['recipent_email'] = $contact_email;
        $maildata['subject']= utf8_encode("Tak for din bestilling af gavekort");
        $maildata['body'] = ($mailcontent);
        $maildata['mailserver_id'] = 4;
        \MailQueue::createMailQueue($maildata);


    }

}