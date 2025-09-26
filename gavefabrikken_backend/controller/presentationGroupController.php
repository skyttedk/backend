<?php

class PresentationGroupController Extends baseController
{

    public function Index()
    {
        echo "ping";
    }
    public function update()
    {
        try {
            $groupId = $_POST['group_id'];           // f.eks. '564'
            // Find record
            $record = PresentationGroup::find_by_group_id($groupId);
            if (!$record) {
                $record = new PresentationGroup();
                //          $record->type        = $_POST["type"];
                $record->group_id = $groupId;
            }
            foreach ($_POST as $field => $value) {
                if ($field != 'group_id' ) {
                        $record->$field = $value;
                }
            }
            $record->save();
            response::success(json_encode($record));

        } catch (Exception $e) {
            response::error("Fejl ved opdatering: " . $e->getMessage());
        }
    }



}
