<?php

namespace GFUnit\navision\homerunner;

use \GFCommon\DB\HomeRunnerLog;

class HomerunnerClient {
    private $apiUrl = 'https://api.homerunner.com';
    private $basicAuthToken = 'ZGF0YUBkaXN0cmlidXRpb25wbHVzLmRrOnBzMm1xMzV1aXY0Nmpld3libGtoZzF4N2RyYzBmbzh6';

    private $lastLogID = 0;

    public function getLastLogID() {
        return $this->lastLogID;
    }

    /**
     * Makes a generic API call to the specified endpoint using the given HTTP method.
     *
     * This function supports GET, POST, and DELETE HTTP methods. It can send JSON data
     * in the request body for POST requests. It logs the request and response details
     * for auditing and debugging purposes.
     *
     * @param string $method The HTTP method to use for the request (e.g., 'GET', 'POST', 'DELETE').
     * @param string $endpoint The API endpoint to call, relative to the base API URL.
     * @param array|null $data Optional data to send with the request, primarily used for POST requests.
     *
     * @return array The response data from the API, decoded as an associative array.
     *
     * @throws Exception If the API call fails, the response is not valid JSON, or a curl error occurs.
     */
    public function callService($method, $endpoint, $data = null) {
        
        // Prepare url
        $url = $this->apiUrl . $endpoint;

        // Create and save log
        $hrLog = new HomerunnerLog();
        $hrLog->createLog($method, $url, json_encode($data), $endpoint, null, 14, 60);
        $this->lastLogID = $hrLog->getId();

        // Prepare curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if necessary
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); // Ensure HTTP version is set

        // Prepare headers
        $headers = [
            'Authorization: Basic ' . $this->basicAuthToken,
            'Content-Type: application/json'
        ];

        // If POST method and data is not null, set the POST fields
        if ($method === 'POST' && $data !== null) {
            if(is_array($data) || is_object($data)) {
                $jsonData = json_encode($data);
            } else if(is_string($data) && json_decode($data) != null) {
                $jsonData = $data;
            } else {
                throw new \Exception("Data is not valid JSON");
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

            $headers[] = 'Content-Length: ' . strlen($jsonData);
        }

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute curl
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for curl errors
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Curl error: " . $error);
        }

        curl_close($ch);


        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            $hrLog->updateResponse($httpCode, $error);
            throw new \Exception("Curl error: " . $error);
        }

        curl_close($ch);

        // Update log
        $hrLog->updateResponse($httpCode, $response);

        if ($httpCode >= 200 && $httpCode < 300) {
            $responseData = json_decode($response, true);
            if ($responseData === null) {
                throw new \Exception("Failed to process response, response is not valid JSON");
            } else {
                return $responseData;
            }
        } else {

            $responseData = json_decode($response, true);

            if ($responseData === null) {

            // determine if client or server error
            if ($httpCode >= 400 && $httpCode < 500) {
                $error = "Client error ".$httpCode.": ";
            } else {
                $error = "Server error ".$httpCode.": ";
            }

            // Check for json in response and fetch message and add to error
            $responseData = json_decode($response, true);
            if ($responseData !== null && isset($responseData['message'])) {
                $error .= $responseData['message'];
            } else {
                $error .= "No message provided";
            }

            throw new \Exception($error);

            }
            else {

                if ($httpCode >= 400) {
                    $error = "Client error ".$httpCode.": ".($responseData['message'] ?? "Unknown error");
                    throw new \Exception($error);
                } else if($httpCode < 500){
                    $error = "Server error ".$httpCode.": ".($responseData['message'] ?? "Unknown error");
                    throw new \Exception($error);
                }

                return $responseData;
            }
        }
    }

    /**
     * Retrieves all orders from the API with an optional limit on the number of results.
     *
     * This function constructs an API endpoint to fetch a list of orders, with the ability
     * to limit the number of orders returned. It utilizes the callService function to
     * handle the API request.
     *
     * @param int $limit The maximum number of orders to fetch. Defaults to 2.
     *
     * @return array The response data from the API, decoded as an associative array.
     *
     * @throws Exception If the API call fails or the response is not valid JSON.
     */
    public function getAllOrders($limit = 2) {
        $endpoint = "/wms/orders?limit=" . $limit;
        return $this->callService('GET', $endpoint);
    }


    /**
     * Fetches updated orders from the API based on the given status and date range.
     *
     * This function constructs an API endpoint to retrieve orders that have been updated
     * within a specified time frame and with a specific status. It accepts both DateTime
     * objects and Unix timestamps for the date parameters, converting them to the required
     * format for the API call.
     *
     * @param string $status The status of the orders to fetch (e.g., 'packed').
     * @param DateTime|int $from The start of the date range, either as a DateTime object or a Unix timestamp.
     * @param DateTime|int $to The end of the date range, either as a DateTime object or a Unix timestamp.
     *
     * @return array The response data from the API, decoded as an associative array.
     *
     * @throws Exception If the API call fails or the response is not valid JSON.
     */
    public function getUpdatedOrders($status, $from, $to) {

        // Check if $from is a DateTime object or Unix timestamp and convert to string
        if ($from instanceof DateTime) {
            $from = $from->format('Y-m-d H:i:s');
        } elseif (is_numeric($from)) {
            $from = date('Y-m-d H:i:s', $from);
        }

        // Check if $to is a DateTime object or Unix timestamp and convert to string
        if ($to instanceof DateTime) {
            $to = $to->format('Y-m-d H:i:s');
        } elseif (is_numeric($to)) {
            $to = date('Y-m-d H:i:s', $to);
        }

        // Encode the from and to dates
        $fromEncoded = urlencode($from);
        $toEncoded = urlencode($to);

        // Build the endpoint with parameters
        $endpoint = "/wms/orders/updated?status=" . urlencode($status) . "&from=" . $fromEncoded . "&to=" . $toEncoded;

        // Call the service
        return $this->callService('GET', $endpoint);
    }

    /**
     * Retrieves a single order from the API using the provided order ID.
     *
     * This function constructs an API endpoint to fetch the details of a specific order
     * identified by the order ID. It utilizes the callService function to handle the API request.
     *
     * @param string|int $orderId The ID of the order to retrieve.
     *
     * @return array The response data from the API, decoded as an associative array.
     *
     * @throws Exception If the API call fails or the response is not valid JSON.
     */
    public function getOrderById($orderId) {
        // Build the endpoint with the order ID
        $endpoint = "/wms/orders/" . urlencode($orderId);

        // Call the service
        return $this->callService('GET', $endpoint);
    }

    /**
     * Cancels a specific order in the API using the provided order ID.
     *
     * This function constructs an API endpoint to cancel an order identified by the order ID.
     * It utilizes the callService function to send a DELETE request to the API.
     *
     * @param string|int $orderId The ID of the order to cancel.
     *
     * @return array The response data from the API, decoded as an associative array.
     *
     * @throws Exception If the API call fails or the response is not valid JSON.
     */
    public function cancelOrder($orderId) {
        // Build the endpoint with the order ID
        $endpoint = "/wms/orders/" . urlencode($orderId);

        // Call the service with DELETE method
        return $this->callService('DELETE', $endpoint);
    }

    /**
     * Creates a new order in the API using the provided order data.
     *
     * This function sends a POST request to create an order or shipment at warehouses
     * integrated with Homerunner. You can specify a specific warehouse or allow the
     * system to automatically select one based on item numbers.
     *
     * @param array $orderData The data representing the order to create. This should
     *                         include all necessary attributes as per the API V3 - Create shipment structure.
     *
     * @return array The response data from the API, decoded as an associative array.
     *
     * @throws Exception If the API call fails or the response is not valid JSON.
     */
    public function createOrder($orderData) {

        // Endpoint for creating orders
        $endpoint = "/wms/orders";

        // Call the service with POST method and order data
        return $this->callService('POST', $endpoint, $orderData);
    }


}
