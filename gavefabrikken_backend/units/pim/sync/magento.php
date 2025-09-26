<?php

namespace GFUnit\pim\sync;
use GFBiz\units\UnitController;

define('BEARER', 'Authorization: Bearer b54w4c03eriqqn6y1v8rtwyoqcqairpn');
define('DEMO_URL', 'http://shopgavefabrikke.dev.magepartner.net/rest');



class Magento extends UnitController
{
    private $MagentoAPI;
    public function __construct()
    {
        parent::__construct(__FILE__);
        $this->MagentoAPI = new MagentoAPI;
    }
    public function syncCategories(){
        echo "syncCategories";
        // get id from PIM
        // get id from Magento
        // compare lists
        // id to delete
        // id to edit
        // id to create


    }




    public function test(){
        
        // hente cat
        /*-----------------------
        //$res = $this->MagentoAPI->getApi(DEMO_URL.'/store1/V1/categories');
        */

        // opret cat
        /*-----------------------
        $body = json_encode(array(
            "category" => array(
                "parent_id" => 175,
                "name" => "ulrich",
                "is_active" => true,
                "position" => 1,
                "level"=> 2
            )
        ));
        $res = $this->MagentoAPI->postApi(DEMO_URL.'/store1/V1/categories',$body);
        */

        // edit cat
        /*-----------------------
        $body = json_encode(array(
            "category" => array(
                "name" => "ulrich_2024"
            )
        ));
        $id = 606;
        try {
            $res = $this->MagentoAPI->putApi(DEMO_URL.'/store1/V1/categories/'.$id,$body);
            print_R($res);
        } catch (Exception $e) {
            echo "Caught exception: " . $e->getMessage();
        }
        */

        // edit cat
        /*-----------------------
        $id = 606;
        try {
            $res = $this->MagentoAPI->deleteApi(DEMO_URL.'/store1/V1/categories/'.$id);
            print_R($res);
        } catch (Exception $e) {
            echo "Caught exception: " . $e->getMessage();
        }
        */







    }









}

class MagentoAPI
{
    public function mtest(){
        echo "mtest";
    }
    public function getApi($url){

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array( BEARER ),
        ));
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }
        curl_close($curl);
        return $response;
    }
    public function postApi($url,$body){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array( 'Content-Type: application/json', BEARER ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }
        curl_close($curl);
        return $response;
    }
    public function putApi($url,$body){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array( 'Content-Type: application/json', BEARER ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }
        curl_close($curl);
        return $response;
    }
    public function deleteApi($url){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array( 'Content-Type: application/json', BEARER ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }
        curl_close($curl);
        return $response;
    }



}


