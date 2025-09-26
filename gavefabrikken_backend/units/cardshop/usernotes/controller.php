<?php

namespace GFUnit\cardshop\usernotes;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function userremindercount($output=true) {


        $count = $this->loadActiveReminders();
        echo json_encode(array("status" => 1, "count" => $count));

    }

    private function loadActiveReminders()
    {
        $systemUserID = \router::$systemUser->id;
        $sql = "SELECT count(id) as remindercount FROM `company_notes` WHERE created_by = ".$systemUserID."  AND reminder_datetime < NOW() + INTERVAL 1 DAY  AND resolved_datetime IS NULL  AND deleted_datetime IS NULL;";
        $count = \CompanyNotes::find_by_sql($sql);
        if($count == null || count($count) == 0) {
            $count = 0;
        } else {
            $count = $count[0]->remindercount;
        }

        return $count;
    }

    public function noteview($companyid = 0)
    {

        $companyid = intvalgf($companyid);

        try {
            $company = \Company::find($companyid);
        } catch (\Exception $e) {
            $company = null;
        }

        if($company == null || $company->id == 0) {
            echo "Kan ikke finde virksomhed, prøv igen.";
            exit();
        }
        
        $this->view("noteview", array("company" => $company));
        
    }



    public function resolve()
    {


        $company = $this->getCompany();
        $editid = intvalgf($_POST["noteid"]);
        $editnote = $this->getCompanyNoteID($company->id,$editid);

        if($editnote == null) {
            $this->outputServiceError("Kan ikke finde note der skal redigeres!");
        }

        $editnote = \CompanyNotes::find($editnote->id);

        // Update note
        $editnote->resolved_by = \router::$systemUser->id;
        $editnote->resolved_datetime = new \DateTime("now");

        // Save note
        $editnote->save();

        // Add action log
        \ActionLog::logAction("note", "Note løst",substr($editnote->note,0,100).(strlen($editnote->note) > 100 ? "..." : "")." (Prioritet: ".($editnote->priority == 1 ? "Ja" : "Nej").")".($editnote->reminder_datetime != null ? " (Påmindelse: ".$editnote->reminder_datetime->format("d/m/Y").")" : ""),0,0,$company->id,0,0,0,0,"CompanyNotes:".$editnote->id.";");

        // Add to log
        \System::connection()->commit();

        // Output service success
        $this->outputServiceSuccess(array("note" => $this->noteToJson($editnote)));
    }


    public function resolveremove()
    {


        $company = $this->getCompany();
        $editid = intvalgf($_POST["noteid"]);
        $editnote = $this->getCompanyNoteID($company->id,$editid);

        if($editnote == null) {
            $this->outputServiceError("Kan ikke finde note der skal redigeres!");
        }

        $editnote = \CompanyNotes::find($editnote->id);

        // Update note
        $editnote->resolved_by = null;
        $editnote->resolved_datetime = null;

        // Save note
        $editnote->save();

        // Add action log
        \ActionLog::logAction("note", "Note sat til ikke løst",substr($editnote->note,0,100).(strlen($editnote->note) > 100 ? "..." : "")." (Prioritet: ".($editnote->priority == 1 ? "Ja" : "Nej").")".($editnote->reminder_datetime != null ? " (Påmindelse: ".$editnote->reminder_datetime->format("d/m/Y").")" : ""),0,0,$company->id,0,0,0,0,"CompanyNotes:".$editnote->id.";");

        // Add to log
        \System::connection()->commit();

        // Output service success
        $this->outputServiceSuccess(array("note" => $this->noteToJson($editnote)));
    }




    public function delete() {



        $company = $this->getCompany();
        $editid = intvalgf($_POST["noteid"]);
        $editnote = $this->getCompanyNoteID($company->id,$editid);

        if($editnote == null) {
            $this->outputServiceError("Kan ikke finde note der skal redigeres!");
        }

        $editnote = \CompanyNotes::find($editnote->id);

        $editnote->deleted_by = \router::$systemUser->id;
        $editnote->deleted_datetime = new \DateTime("now");

        // Save note
        $editnote->save();

        // Add action log
        \ActionLog::logAction("note", "Note slettet",substr($editnote->note,0,100).(strlen($editnote->note) > 100 ? "..." : "")." (Prioritet: ".($editnote->priority == 1 ? "Ja" : "Nej").")".($editnote->reminder_datetime != null ? " (Påmindelse: ".$editnote->reminder_datetime->format("d/m/Y").")" : ""),0,0,$company->id,0,0,0,0,"CompanyNotes:".$editnote->id.";");

        // Add to log
        \System::connection()->commit();

        // Output service success
        $this->outputServiceSuccess(array());

    }




    public function create() {


        // Load data into variables
        $text = trimgf($_POST['text']);
        $priority = intvalgf($_POST['priority']) == 1 ? 1 : 0;
        $reminderDate = trimgf($_POST['reminderDate']);
        $usereminder = intvalgf($_POST['usereminder']) == 1 ? 1 : 0;
        $companyid = intvalgf($_POST['companyid']);
        $companyhash = trimgf($_POST['companyhash']);

        // Get company
        $company = $this->getCompany();

        // Check data
        if($text == "") {
            $this->outputServiceError("Der er ikke angivet en note tekst.");
        }

        if($usereminder == 1) {

            // Check reminder date is yyyy-mm-dd and is valid
            if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$reminderDate)) {
                $this->outputServiceError("Der er ikke angivet en gyldig påmindelsesdato.");
            }

            // Reminder must be after now
            if(strtotime($reminderDate) < strtotime(date("Y-m-d"))) {
                $this->outputServiceError("Påmindelsesdatoen skal være en dato i fremtiden.");
            }

            // Create datetime object
            $reminderDate = \DateTime::createFromFormat("Y-m-d",$reminderDate);

        }

        // Create note
        $note = new \CompanyNotes();
        $note->company_id = $company->id;
        $note->priority = $priority;
        $note->created_by = \router::$systemUser->id;
        $note->created_datetime = new \DateTime("now");
        $note->note = $text;
        if($usereminder == 1) {
            $note->reminder_datetime = $reminderDate;
        }

        // Save note
        $note->save();

        // Add action log
        \ActionLog::logAction("note", "Note oprettet",substr($note->note,0,100).(strlen($note->note) > 100 ? "..." : "")." (Prioritet: ".($note->priority == 1 ? "Ja" : "Nej").")".($note->reminder_datetime != null ? " (Påmindelse: ".$note->reminder_datetime->format("d/m/Y").")" : ""),0,0,$company->id,0,0,0,0,"CompanyNotes:".$note->id.";");

        // Add to log
        \System::connection()->commit();

        // Output service success
        $this->outputServiceSuccess(array("note" => $this->noteToJson($note)));

    }


    public function getlist() {



        // Get company
        $company = $this->getCompany();

        $noteList = $this->getCompanyNoteList($company->id);
        $responseData = [];

        foreach($noteList as $note) {
            $responseData[] = $this->noteToJson($note);
        }

        $this->outputServiceSuccess(array("notes" => $responseData));

    }

    public function edit() {


        $company = $this->getCompany();
        $editid = intvalgf($_POST["noteid"]);
        $editnote = $this->getCompanyNoteID($company->id,$editid);

        if($editnote == null) {
            $this->outputServiceError("Kan ikke finde note der skal redigeres!");
        } else {
            $this->outputServiceSuccess(array("note" => $this->noteToJson($editnote)));
        }

    }

    public function update() {
        

        $company = $this->getCompany();
        $editid = intvalgf($_POST["noteid"]);
        $editnote = $this->getCompanyNoteID($company->id,$editid);

        if($editnote == null) {
            $this->outputServiceError("Kan ikke finde note der skal redigeres!");
        }

        $editnote = \CompanyNotes::find($editnote->id);

        // Load data into variables
        $text = trimgf($_POST['text']);
        $priority = intvalgf($_POST['priority']) == 1 ? 1 : 0;
        $reminderDate = trimgf($_POST['reminderDate']);
        $usereminder = intvalgf($_POST['usereminder']) == 1 ? 1 : 0;

        // Check data
        if($text == "") {
            $this->outputServiceError("Der er ikke angivet en note tekst.");
        }

        if($usereminder == 1) {

            // Check reminder date is yyyy-mm-dd and is valid
            if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$reminderDate)) {
                $this->outputServiceError("Der er ikke angivet en gyldig påmindelsesdato.");
            }

            // Reminder must be after now
            if(strtotime($reminderDate) < strtotime(date("Y-m-d"))) {
                $this->outputServiceError("Påmindelsesdatoen skal være en dato i fremtiden.");
            }

            // Create datetime object
            $reminderDate = \DateTime::createFromFormat("Y-m-d",$reminderDate);

        }

        // Update note
        $editnote->company_id = $company->id;
        $editnote->priority = $priority;
        $editnote->note = $text;
        if($usereminder == 1) {
            $editnote->reminder_datetime = $reminderDate;
        }

        // Save note
        $editnote->save();

        // Add action log
        \ActionLog::logAction("note", "Note redigeret",substr($editnote->note,0,100).(strlen($editnote->note) > 100 ? "..." : "")." (Prioritet: ".($editnote->priority == 1 ? "Ja" : "Nej").")".($editnote->reminder_datetime != null ? " (Påmindelse: ".$editnote->reminder_datetime->format("d/m/Y").")" : ""),0,0,$company->id,0,0,0,0,"CompanyNotes:".$editnote->id.";");

        // Add to log
        \System::connection()->commit();

        // Output service success
        $this->outputServiceSuccess(array("note" => $this->noteToJson($editnote)));

    }


    /**
     * HELPER FUNCTIONS
     */

    public function getCompanyNoteList($companyid) {
        $noteList = \CompanyNotes::find_by_sql("select * from company_notes where deleted_datetime IS NULL and company_id = ".$companyid." order by created_datetime desc");
        return $noteList;
    }

    private function getCompanyNoteID($companyid,$noteid) {
        $noteList = $this->getCompanyNoteList($companyid);
        foreach($noteList as $note) {
            if($note->id == $noteid) {
                return $note;
            }
        }
        return null;
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

    private function noteToJson($note) {

        $data = array(
            "id" => $note->id,
            "text" => $note->note,
            "created_at" => $note->created_datetime->format("d/m/Y H:i"),
            "resolved_datetime" => $note->resolved_datetime != null ? $note->resolved_datetime->format("d/m/Y H:i") : null,
            "author" => $this->getSystemUserName($note->created_by),
            "resolved_by" => $note->resolved_by != null ? $this->getSystemUserName($note->resolved_by) : null,
            "priority" => $note->priority == 1,
            "reminder_date" => $note->reminder_datetime != null ? $note->reminder_datetime->format("Y-m-d") : null,
            "is_resolved" => $note->resolved_datetime != null
        );

        return $data;
        
    }

    private function outputServiceSuccess($data) {
        $response = array_merge(array("success" => true),$data);
        echo json_encode($response);
        exit();
    }

    private function outputServiceError($errorMessage) {
        echo json_encode(array("success" => false,"error" => $errorMessage));
        exit();
    }

    private function getCompany()
    {
        $companyId = intvalgf($_POST['companyid']);
        $companyHash = trimgf($_POST['companyhash']);

        $company = \Company::find($companyId);
        if($company == null || $company->id == 0 || $company->token != $companyHash) {
            $this->outputServiceError("Kan ikke finde virksomhed, prøv igen.");
            exit();
        }
        return $company;
    }



    public function remindermodal() {

        $reminderCount = $this->loadActiveReminders();

      $modal = new ReminderModal();
      $modal->showModal($reminderCount);

    }

}