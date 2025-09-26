<?php
//Report Controller er controller til Model ShopReport.
//Samt controller til alle andre rapporter
Class reportController Extends baseController {
    public function index() {
	}

	public function createShopReport() {
  		$report = ShopReport::createShopReport($_POST);
		response::success(make_json("report", $report));
	}
	public function readShopReport() {
		$report = ShopReport::readShopReport ($_POST['id']);
		response::success(make_json("report", $report));
	}
	public function updateShopReport() {
		$report = ShopReport::updateShopReport ($_POST);
		response::success(make_json("report", $report));
	}
	public function deleteShopReport() {
		$report = ShopReport::deleteShopReport ($_POST['id']);
		response::success(make_json("report", $report));
	}
	//Create Variations of readAll
	public function readAllShopReport() {
		$reports = ShopReport::all();
		$options = array();
		response::success(make_json("reports", $reports, $options));
	}

	//---------------------------------------------------------------------------------------
	// Custom Controller Actions
	//---------------------------------------------------------------------------------------
	public function getPartialDeliveryList(){
        $id = $_POST["id"];
        if(!is_numeric($id*1)){
            return;
        }
        $shop = Shop::find_by_sql("select * from shop where id=".$id." and partial_delivery = 1");
        if(sizeof($shop) == 0){
            echo json_encode(array("status" => 2));
            return;
        }
        $rs = ShopUser::find_by_sql("select distinct (delivery_print_date) from shop_user where shop_id =".$id." and delivery_print_date is not NULL");
        response::success(json_encode($rs));
    }
    public function setPartialDelivery()
    {
        $id = $_POST["id"];
        if(!is_numeric($id*1)){
            return;
        }
        $data = array("delivery_print_date"=>date("Y-m-d H:i:s"),"shutdown"=>1);
        $rs = ShopUser::find_by_sql("select id from shop_user where id in (  SELECT shopuser_id FROM `order` WHERE `shop_id` =".$id.") and shutdown = 0 " );
        print_r($rs);
        foreach ($rs as $shopUser){

            $shopuser = ShopUser::find($shopUser->id);
            $shopuser->update_attributes($data);
            $shopuser->save();
        }
        $dummy = array();
        response::success(make_json("result", $dummy));

    }



    public function genericReport()
	{
		$genericReport = new ValgshopRapport();
		$genericReport->run($_GET['id']);
		$genericReport->save("rapport");
	}

    public function userReport()
	{
		$userReport = new BrugerRapport();
		$userReport->run($_GET['shop_id']);
	}
    public function userReportTest()
	{
		$userReport = new BrugerRapport();
		$userReport->BrugerRapportTest(2296);
	}

    public function manglerGaveValgRapport()
	{
	    // raport over dem som ikke har valgt gave
	    $userReport = new manglerGaveValgRapport();
		$userReport->run($_GET['shop_id']);

	}
    public function manglerGaveAfhentningRapport()
	{
	    //Raport over dem som ikke har valgt gave
	    $userReport = new manglerGaveAfhentningRapport();
		$userReport->run($_GET['shop_id']);
	}
    public function qrlog()
	{
     include __SITE_PATH . '/component/qrRapport.php';

        $prlogRapport = new qrRapport;
        $prlogRapport->run($_GET['shop_id']);
	}

	public function gavekortReport()
	{
	    ini_set('memory_limit','2048M');
		$gavekortReport = new gavekortRapport();
		$gavekortReport->run();
	}

	public function gavekortLeveringRapport()
	{
		$gavekortLeveringReport = new gavekortLeveringRapport();
		$gavekortLeveringReport->run();
	}
    
    public function gavekortLeveringNORapport()
    {
        $gavekortLeveringReport = new gavekortLeveringRapport();
        $gavekortLeveringReport->runno();
    }

    public function gavekortLeveringTestRapport()
    {
        $gavekortLeveringReport = new gavekortLeveringRapport();
        $gavekortLeveringReport->runnewprivateDelivery(isset($_GET["lang"]) ? $_GET["lang"] : "");
    }

    public function gavekortLeveringSERapport()
    {
        $gavekortLeveringReport = new gavekortLeveringRapport();
        $gavekortLeveringReport->runse();
    }
    
	public function gavekortLeveringoversigtRapport()
	{
		$gavekortLeveringReport = new x();
		$gavekortLeveringReport->run();
	}
    
    public function invoiceReport()
	{
	    //Fajkturakladde til Navision (CSV)
		$invoiceReport = new invoicerapport();
		$invoiceReport->run();
    	System::connection()->commit();
	}



    // til de norske
    public function invoiceReport2()
	{

	    //Fajkturakladde til Navision (CSV)
		$invoiceReport = new invoicerapport2();
		$invoiceReport->run();
    	System::connection()->commit();
	}       // til de norske

    public function fragtRapport()
	{
	    //Fajkturakladde til Navision (CSV)
		$invoiceReport = new fragtRapport();
		$invoiceReport->run();
    	System::connection()->commit();
	}

     public function fragt2020Rapport()
    {
        $userReport = new fragtRapport2020();
        $userReport->run();
    }


    public function valg2020Rapport()
    {
        $userReport = new fragtRapport2020();
        $userReport->runGavevalg();
    }
}
?>