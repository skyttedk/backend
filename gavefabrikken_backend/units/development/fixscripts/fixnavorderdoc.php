<?php

namespace GFUnit\development\fixscripts;

class FixNavOrderDoc
{

    private $stats;

    public function run() {


        $list = \NavisionOrderDoc::find("all",array("conditions" => array("order_no" => 0)));
        foreach($list as $nod) {

            $split1 = explode("</orderno>",$nod->xmldoc);
            if(count($split1) != 2) echo "INVALID SPLIT 1: ".$nod->id."<br>";
            else {
                $split2 = explode("<orderno>",$split1[0]);
                if(count($split1) != 2) echo "INVALID SPLIT 2: ".$nod->id."<br>";
                else {
                    echo $nod->id." => ".$split2[1]."<br>";
                    $nod->order_no = $split2[1];
                    $nod->save();
                }


            }

        }

        \System::connection()->commit();

    }

}