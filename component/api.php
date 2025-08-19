<?php

      $url = "http://backendnftestdev.phct-130.cust.powerhosting.dk/rest/all/V1/products/attributes/year/options";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
           "Accept: application/json",
           "Authorization: Bearer b9scu4oa9f2p8r68li081k09czmge1lp",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($curl);
        curl_close($curl);
        var_dump($resp);