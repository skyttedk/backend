<?php
// USED as DataProcessor for DHTMLX

class genericController Extends baseController {
    public function Index() {
  }
   //http://bitworks.dk/gf/index.php?rt=generic/getList&model=present&select=id,present_no,name,price,active,created_datetime
   public function getList() {
       $model = 'Present';
       $model = $_GET["model"];
       $select = $_GET["select"];
       $filter =  "";
//       $conditions = array('deleted = ?  AND (shop_id = ?)',0,0);
       $conditions = array();

       $data = $model::all( array(
            'conditions' => $conditions,
            'select' => $select

       ));
       echo $this->render_json($data);
   }



 function render_json($dataset) {
   $recordCount = countgf($dataset);
   $j=0;
   echo "{\"rows\":[";
   foreach($dataset as $record) {
       $attributeCount = countgf($record->attributes);
       $i = 1;
       echo "{\"id\":".$record->{"id"}.", \"data\":[";
       foreach($record->attributes as $destinationKey => $destinationValue)
        {
            if($destinationKey!="id"){
                if(gettype($record->{$destinationKey})=="object")
                  echo '"'.$record->{$destinationKey}->format('d-m-Y H:m:s').'"';
                else
                  echo '"'.$record->{$destinationKey}.'"';
            $i++;
            if($i<$attributeCount)
              echo ",";

            }
        }
       echo "]}";
       $j++;
       if($j<$recordCount)
       echo ",";

    }
    echo  "]}";

  }

}
?>
