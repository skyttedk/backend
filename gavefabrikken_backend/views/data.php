<?php
$data["header"] = ["head1","head2","head3"];
$data["tableData"][] =["field1"=>"data1_1","field2"=>"data1_2","field3"=>"data1_3"];
$data["tableData"][] =["field1"=>"data2_1","field2"=>"data2_2","field3"=>"data2_3"];
$data["tableData"][] =["field1"=>"data3_1","field2"=>"data3_2","field3"=>"data3_3"];
$data["tableData"][] =["field1"=>"data4_1","field2"=>"data4_2","field3"=>"data4_3"];
$data["tableData"][] =["field1"=>"data5_1","field2"=>"data5_2","field3"=>"data5_3"];
	
echo json_encode($data);	



?>