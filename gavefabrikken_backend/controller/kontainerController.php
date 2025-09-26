<?php

class kontainerController extends baseController
{
    public function Index() {
        echo "index";
    }

    // Hent enkelt produkt baseret på kontainer ID
    public function getSingleProduct()
    {
        $_POST["kontainerID"] = 13363404;
        if (isset($_POST["kontainerID"])) {
            $kontainerID = $_POST["kontainerID"];
            $result = $this->getDataSingle($kontainerID);

            header('Content-Type: application/json');
            echo $result;
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'kontainerID er påkrævet']);
        }
    }

    // Hent produkt baseret på item nummer
    public function getProductByItemNumber()
    {
        if (isset($_POST["itemNr"])) {
            $itemNr = $_POST["itemNr"];
            $result = $this->getDataOnItemnr($itemNr);

            header('Content-Type: application/json');
            echo $result;
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'itemNr er påkrævet']);
        }
    }

    // Generisk curl funktion
    private function makeCurlRequest($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = GFConfig::KONTAINER_API_KEY;
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    // Funktion til at hente enkelt produkt data
    public function getDataSingle($kontainerID)
    {
        $url = GFConfig::KONTAINER_API_BASE_URL . '/' . $kontainerID;
        return $this->makeCurlRequest($url);
    }

    // Funktion til at hente data baseret på item nummer
    public function getDataOnItemnr($itemNr)
    {
        $url = GFConfig::KONTAINER_API_BASE_URL . '?filter[product_no][eq]=' . urlencode($itemNr) . '&filter[product_type][eq]=Product';
        return $this->makeCurlRequest($url);
    }
}
?>