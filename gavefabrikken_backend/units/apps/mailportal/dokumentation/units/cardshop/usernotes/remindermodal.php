<?php

namespace GFUnit\cardshop\usernotes;

class ReminderModal
{

    function createNoteHTML($note) {
        $specialText = "";

        // Resolved
        if ($note->resolved_datetime != null) {
            $resolvedDate = $note->resolved_datetime->format('d/m/Y H:i');
            $resolvedBy = $note->resolved_by != null ? $this->getSystemUserName($note->resolved_by) : '';
            $specialText .= "<div>Løst d. {$resolvedDate} af {$resolvedBy}</div>";
        }
        // Reminder
        if ($note->reminder_datetime != null) {
            $reminderDate = $note->reminder_datetime->format('Y-m-d');
            $specialText .= "<div>Reminder d. {$reminderDate}</div>";
        }
        // High priority
        if ($note->priority == 1) {
            $specialText .= "<div style=\"color: red;\">Høj prioritet</div>";
        }

        $resolveButton = "";
/*
        $resolveButton = '<button class="btn btn-sm btn-info" onclick="noteManager.resolveNote(' . $note->id . ')">Sæt til løst</button>';
        if ($note->resolved_datetime != null) {
            $resolveButton = '<button class="btn btn-sm btn-warning" onclick="noteManager.resolveNoteRemove(' . $note->id . ')">Fjern løst</button>';
        }
*/
        $createdAt = $note->created_datetime->format('d/m/Y H:i');
        $author = $this->getSystemUserName($note->created_by);
        $html = '
        <div class="noteitem ' . ($note->resolved_datetime != null ? 'noteitem-solved' : '') . ' ' . ($note->priority == 1 ? 'noteitem-priority' : '') . '" id="note_' . $note->id . '">
            <div class="noteitemhead">
                <div style="float: right;">af: ' . $author . ' d. ' . $createdAt . '</div>
                <b>' . $note->company_name . '</b>
            </div>
            <div class="noteitembody">
                ' . $note->note . '
            </div>
            <div class="noteitemfooter">
                <div style="float: right;">
                    <button class="btn btn-sm btn-primary reminder-go-to-company" data-id="'.$note->company_id.'">Gå til kunden</button>
                    ' . $resolveButton . '
                </div>
                ' . $specialText . '
            </div>
        </div>
    ';

        return $html;
    }

    private $systemUserNameCache = null;

    private function getSystemUserName($systemUserID) {

        if($this->systemUserNameCache == null) {

            $this->systemUserNameCache = array();
            $userList = \SystemUser::find('all');
            foreach($userList as $user) {
                $this->systemUserNameCache[$user->id] = $user->name;
            }
        }

        if($systemUserID < 0) return "Ikke gyldig bruger";
        else if($systemUserID == 0) return "Ikke angivet";
        else if(isset($this->systemUserNameCache[$systemUserID])) return $this->systemUserNameCache[$systemUserID];
        else return "Ukendt bruger";

    }



    public function showModal($reminderCount=null)
    {

        $adminUsers = array(50);

        // Input
        $reminderviewtype = intval($_POST["reminderviewtype"] ?? 0);
        $reminderviewuser = intval($_POST["reminderviewuser"] ?? 0);
        $isAdmin = in_array(\router::$systemUser->id, $adminUsers);

        $limit = 1000;

        // Lock to user
        $userSql = " and company_notes.created_by = ".intval(\router::$systemUser->id);
        if($isAdmin && $reminderviewuser == -1) {
            $userSql = "";
        } else if($isAdmin && $reminderviewuser > 0) {
            $userSql = " and company_notes.created_by = ".intval($reminderviewuser);
        }

        // Lock to time
        $timeLock = "AND company_notes.reminder_datetime < NOW() + INTERVAL 1 DAY AND company_notes.resolved_datetime IS NULL";
        $order = " company_notes.reminder_datetime ASC";

        if($reminderviewtype == 1) {
            $timeLock = "AND company_notes.reminder_datetime > NOW() AND company_notes.resolved_datetime IS NULL";
        } else if($reminderviewtype == 2) {
            $timeLock = "AND company_notes.resolved_datetime IS NOT NULL";
            $order = "company_notes.resolved_datetime DESC";
        }

        $sql = "SELECT company_notes.*, company.name as company_name FROM `company_notes`, company WHERE company_notes.company_id = company.id and company_notes.reminder_datetime is not null ".$userSql." ".$timeLock." AND company_notes.deleted_datetime IS NULL ORDER BY ".$order." LIMIT ".$limit;
        $notesList = \CompanyNotes::find_by_sql($sql);


        // Body
        ob_start();

        // Create status text from filters and count
        $statusText = (count($notesList) == 0) ? "Fandt ingen " : "Fandt ".count($notesList)." ";
        if($reminderviewtype == 0) $statusText .= "aktuelle";
        else if($reminderviewtype == 1) $statusText .= "aremtidige";
        else if($reminderviewtype == 2) $statusText .= "løste";

        if($isAdmin) {
            $statusText .= " noter";
            if($reminderviewuser == 0) $statusText .= " fra din bruger";
            else if($reminderviewuser == -1) $statusText .= " fra alle";
            else $statusText .= " fra ".$this->getSystemUserName($reminderviewuser);
        }

        echo "<div style='text-align: center; font-weight: bold; margin-bottom: 10px; font-size: 1.2em;'>$statusText</div>";

        echo "<div style='max-height: 75vh; overflow-y: auto;'>";


        foreach($notesList as $note) {
            echo $this->createNoteHTML($note);
        }

   
        echo "</div>";

        $body = ob_get_contents();
        ob_end_clean();

        // Footer
        ob_start();

        ?><style>
        .noteheader { padding: 15px; background-color: #B8B8B8; }
        .notesubheader { padding: 15px; background-color: #f1f1f1; padding-top: 10px; padding-bottom: 10px; }
        .noteform { padding: 15px;}
        .noteitem {  border-bottom: 2px solid #E0E0E0; border-top: 2px solid #E0E0E0; margin-bottom: 10px; margin-right: 10px; border-left: 6px solid #E0E0E0; border-right: 6px solid #E0E0E0; }
        .noteitem-mine { border-left: 6px solid #6895D2; border-right: 6px solid #6895D2; }
        .noteitem-priority { border-left: 6px solid #D04848; border-right: 6px solid #D04848; }
        .noteitem-date { border-left: 6px solid #FDE767; border-right: 6px solid #FDE767; }
        .noteitem-solved { border-left: 6px solid #BFEA7C; border-right: 6px solid #BFEA7C; }
        .noteitemhead { padding: 9px; padding-right: 15px; padding-top: 5px; padding-bottom: 5px; background: #E0E0E0;}
        .noteitembody {padding: 9px; padding-right: 15px;padding-top: 8px; padding-bottom: 8px; }
        .noteitemfooter { padding: 9px; padding-top: 10px; padding-right: 15px; padding-bottom: 10px; background: #F5F5F5; }
    </style><?php

        echo "<table style='width: 100%;'>
        <tr>
            <td>
                Vis:
                <select id='reminderviewtype'>
                    <option value='0' ".($reminderviewtype == 0 ? "selected" : "").">Aktuelle</option>
                    <option value='1' ".($reminderviewtype == 1 ? "selected" : "").">Fremtidige</option>
                    <option value='2' ".($reminderviewtype == 2 ? "selected" : "").">Løste</option>
                </select>
            </td>";

        if($isAdmin) {

            $systemUserList = \SystemUser::find_by_sql("select * from system_user where id in (SELECT DISTINCT created_by FROM `company_notes`) order by name");


         echo "<td>
                Bruger:
                <select id='reminderviewuser'>
                    <option value='0' ".($reminderviewuser == 0 ? "selected" : "").">Mig</option>
                    <option value='-1' ".($reminderviewuser == -1 ? "selected" : "").">Alle</option>";

                foreach($systemUserList as $systemUser) {

                    echo "<option value='".$systemUser->id."' ".($reminderviewuser == $systemUser->id ? "selected" : "").">".$systemUser->name."</option>";

                }

                echo "</select>
            </td>";
        }

         echo "<td style='text-align: right;'>
                <button class=\"btn btn-sm btn-default closebtn\">Luk vindue</button>
                <button class=\"btn btn-sm btn-primary refreshbtn\">Opdater</button>
            </td>
        </tr>
        </table>";


        $footer = ob_get_contents();
        ob_end_clean();

        // To json
        echo json_encode(array("body" => $body, "footer" => $footer, "remindercount" => $reminderCount,"status" => 1));

    }

}