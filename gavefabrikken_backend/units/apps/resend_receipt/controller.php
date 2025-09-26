<?php
// https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/resend_receipt/resendReceipt

namespace GFUnit\apps\resend_receipt;
use GFBiz\units\UnitController;

class Controller extends UnitController
{
    private $shopID;
    private $customEmailTxt;

    private $sendgroup = "";
    public function __construct()
    {
        parent::__construct(__FILE__);
    }





    public function resendReceipt()
    {
        set_time_limit(300);
        $this->sendgroup = "Rybners";
      //  $this->shopID = 6205;
        $this->setCustomTxt();
        // Hent ordre IDs for den givne shop
        $orderIds = $this->getOrderIdsForShop();


        $processedCount = 0;
        foreach ($orderIds as $order) {
            // Hent email data fra mailkø
            $endtxt = "";
            echo $order->id."<br>";
            $emailData = $this->getEmailDataFromQueue($order->order_id);

            // $userAttr = $this->getUserAttribute($order->id,41349);

            //$endtxt = $userAttr->attribute_value ?? "";

            if ($emailData) {
                // Tilføj custom tekst til body

                $modifiedBody = $emailData['body']; //  $this->addCustomTextToBody($emailData['body']);
               // $modifiedBody.="<hr><p>Leveringssted / Delivery: <b>".$endtxt."</b> </p>";

                    // Send den modificerede email via mailqueue

                    if ($this->queueEmail($emailData['email'], $emailData['subject'], $modifiedBody, $order->order_id)) {

                    }

                }



        }

        die("END");
    }

    private function setCustomTxt()
    {
        $this->customEmailTxt = "<p><em>K&aelig;re&nbsp;kollega&nbsp;</em></p>
            <p><em>Du kan snart hente din julegave fra Lundbeck. </em></p>
            <p><em>Medbring denne bekr&aelig;ftelse for at f&aring; din gave udleveret. Du kan enten printe den eller fremvise den p&aring; din smartphone.</em></p>
            <p><em>********</em></p>
            <p><em>Dear&nbsp;colleague</em></p>
            <p><em>You can soon pick up your Christmas gift from Lundbeck. </em></p>
            <p><em>Bring this confirmation when you go to collect your gift. You can either print it or show it on your smartphone.</em></p>";
        $this->customEmailTxt.= "<hr><br><div style='page-break-before: always; clear: both; mso-break-type: section-break; height: 1px;'>&nbsp;</div>";

    }

    private function queueEmail($to, $subject, $body, $userId)
    {
      // echo $to.")";
       $to = "us@gavefabrikken.dk";
      //  $to ="cle@gavefabrikken.dk";
        try {
            $mailqueue = new \MailQueue();
            $mailqueue->sender_name = 'Gavefabrikken';
            $mailqueue->mailserver_id = 4;
            $mailqueue->sender_email = 'Gavefabrikken@gavefabrikken.dk';
            $mailqueue->recipent_email = $to;
            $mailqueue->subject = $subject;
            $mailqueue->body = $body;
            $mailqueue->send_group = $this->sendgroup;
            $mailqueue->category = 1;

            $mailqueue->save();
            \System::connection()->commit();
            \System::connection()->transaction();
            return true;
        } catch (\Exception $e) {
            error_log("Fejl ved afsendelse af kvittering for ordre $userId: " . $e->getMessage());
            return false;
        }
    }
    private function addCustomTextToBody($body)
    {
        // Indsæt custom text efter <body> tag
        $bodyPosition = stripos($body, '<body>');
        if ($bodyPosition !== false) {
            return substr($body, 0, $bodyPosition + 6) . $this->customEmailTxt . substr($body, $bodyPosition + 6);
        }

        // Hvis der ikke er en body tag, tilføj teksten i starten
        return $this->customEmailTxt . $body;
    }
    private function getOrderIdsForShop()
    {
        $sql = "SELECT shop_user.id, `order`.id as order_id,blocked,shutdown
            FROM shop_user
            left JOIN `order` ON `order`.shopuser_id = shop_user.id
            WHERE `order`.shop_id = ? AND blocked = 0 AND shutdown = 0";

        return \ShopUser::find('all', array(
            'joins' => array(
                'INNER JOIN `order` ON `order`.shopuser_id = shop_user.id'
            ),
            'conditions' => array(
                '`order`.shop_id = ? AND blocked = 0 AND shutdown = 0',
                $this->shopID
            ),
            'select' => 'shop_user.id, `order`.id as order_id',
            'limit' => 3000
        ));

    }
    private function getUserAttribute($shopuser_id,$attribute_id)
    {
        return \UserAttribute::find('first', array(
            'conditions' => array(
                'shopuser_id = ? AND attribute_id = ?',
                $shopuser_id,
                $attribute_id
            )
        ));
    }

    private function getEmailDataFromQueue($orderId)
    {
        $originalMail = \MailQueue::find('first', array(
            'conditions' => array(
                'order_id = ?',
                $orderId
            )
        ));

        if ($originalMail) {
            return array(
                'email' => $originalMail->recipent_email,
                'body' => $originalMail->body,
                'subject' => $originalMail->subject
            );
        }

        return null;
    }

}
