<?php
// Controller present
// Date created  Tue, 05 Apr 2016 20:16:03 +0200
// Created by Bitworks
//   dump(Present::table()->last_sql);
class PresentChildController Extends baseController
{

    public function Index()
    {
    }
    public function read(){
        sleep(1);
        $presentId = $_POST['present_id'];
        $shopId = $_POST["shop_id"]*-1;

// Hent children med deres beskrivelser og medier

        $childPresents = Present::all(array(
            'select' => 'present.*, present_media.media_path',
            'conditions' => array(
                'pchild = ? AND shop_id = ?',
                $presentId,
                $shopId
            ),
            'joins' => array(
                'LEFT JOIN present_media ON present_media.present_id = present.id'
            ),
            'group' => 'present.id',
            'order' => 'present_media.index DESC'  // Dette sikrer vi får den nyeste media_path
        ));

// Hent presentation group data
        $presentationGroup = PresentationGroup::find(array(
            'conditions' => array('group_id = ?', $presentId)
        ));

        $data = array(
            "status" => 1,
            "message" => "Success",
            "data" => array(
                "present" => $childPresents,
                "presentation_group" => $presentationGroup
            )
        );

        echo json_encode($data);

    }

    public function remove(){
        $presentId =   $_POST['present_id'];
        $shopId = $_POST["shop_id"]*-1;
        $sql = "update present set shop_id = 1,internal_name='".$shopId."' where shop_id = ".$shopId." and id = ".$presentId;
        Dbsqli::setSql2($sql);
        response::success(make_json("present",[]));

    }


}
