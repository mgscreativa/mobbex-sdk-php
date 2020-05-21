<?php
/**
 * Mobbex Integration Library
 *
 * Access Mobbex for payments integration
 *
 * @author MGS Creativa
 * @link http://www.mgscreativa.com
 * @copyright Copyright (C) 2020 MGS Creativa - All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 */

class MB
{
    const version = "0.1.0";

    private $api_key;
    private $access_token;

    function __construct()
    {
        $i = func_num_args();

        if ($i != 2) {
            throw new MobbexException("Invalid arguments. Use API KEY and ACCESS TOKEN");
        }

        if ($i == 2) {
            $this->api_key = func_get_arg(0);
            $this->access_token = func_get_arg(1);
        }
    }

    /**
     * Create a mobbex checkout
     * @param array $checkout_data
     * @return array(json)
     */
    public function mobbex_checkout($checkout_data)
    {
        $request = array(
            "uri" => "/p/checkout",
            "headers" => array(
                "api_key" => $this->api_key,
                "access_token" => $this->access_token,
            ),
            "data" => $checkout_data
        );

        $checkout_result = MBRestClient::post($request);
        return $checkout_result;
    }

    /**
     * Get information for specific transaction id
     * @param string $id
     * @return array(json)
     */
    public function get_transaction_status($id)
    {
        $request = array(
            "uri" => "/2.0/transactions/coupon/status",
            "headers" => array(
                "api_key" => $this->api_key,
                "access_token" => $this->access_token,
                "content-type" => "multipart/form-data",
                "cache-control" => "no-cache",
            ),
            "data" => array("id" => $id),
        );

        $transaction_status_result = MBRestClient::post($request);
        return $transaction_status_result;
    }

    /* Generic resource call methods */

    /**
     * Generic resource get
     * @param request
     * @param params (deprecated)
     * @param authenticate = true (deprecated)
     */
    public function get($request, $params = null, $authenticate = true)
    {
        if (is_string($request)) {
            $request = array(
                "uri" => $request,
                "params" => $params,
                "authenticate" => $authenticate
            );
        }

        $request["params"] = isset ($request["params"]) && is_array($request["params"]) ? $request["params"] : array();

        if (!isset ($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->get_access_token();
        }

        $result = MBRestClient::get($request);
        return $result;
    }

    /**
     * Generic resource post
     * @param request
     * @param data (deprecated)
     * @param params (deprecated)
     */
    public function post($request, $data = null, $params = null)
    {
        if (is_string($request)) {
            $request = array(
                "uri" => $request,
                "data" => $data,
                "params" => $params
            );
        }

        $request["params"] = isset ($request["params"]) && is_array($request["params"]) ? $request["params"] : array();

        if (!isset ($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->get_access_token();
        }

        $result = MBRestClient::post($request);
        return $result;
    }

    /**
     * Generic resource put
     * @param request
     * @param data (deprecated)
     * @param params (deprecated)
     */
    public function put($request, $data = null, $params = null)
    {
        if (is_string($request)) {
            $request = array(
                "uri" => $request,
                "data" => $data,
                "params" => $params
            );
        }

        $request["params"] = isset ($request["params"]) && is_array($request["params"]) ? $request["params"] : array();

        if (!isset ($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->get_access_token();
        }

        $result = MBRestClient::put($request);
        return $result;
    }

    /**
     * Generic resource delete
     * @param request
     * @param data (deprecated)
     * @param params (deprecated)
     */
    public function delete($request, $params = null)
    {
        if (is_string($request)) {
            $request = array(
                "uri" => $request,
                "params" => $params
            );
        }

        $request["params"] = isset ($request["params"]) && is_array($request["params"]) ? $request["params"] : array();

        if (!isset ($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->get_access_token();
        }

        $result = MBRestClient::delete($request);
        return $result;
    }

}

/* **************************************************************************************** */

/**
 * Mobbex cURL RestClient
 */
class MBRestClient
{
    const API_BASE_URL = "https://api.mobbex.com";

    private static function build_request($request)
    {
        if (!extension_loaded("curl")) {
            throw new MobbexException("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
        }

        if (!isset($request["method"])) {
            throw new MobbexException("No HTTP METHOD specified");
        }

        if (!isset($request["uri"])) {
            throw new MobbexException("No URI specified");
        }

        // Set headers
        $headers = array("accept: application/json");
        $json_content = true;
        $form_content = false;
        $default_content_type = true;

        if (isset($request["headers"]) && is_array($request["headers"])) {
            foreach ($request["headers"] as $h => $v) {
                $h = strtolower($h);

                switch ($h) {
                    case "content-type":
                        $v = strtolower($v);
                        $default_content_type = false;
                        $json_content = $v == "application/json";
                        $form_content = $v == "application/x-www-form-urlencoded" ||  $v == "multipart/form-data";
                        array_push($headers, "content-type: " . $v);

                        break;
                    case "api_key":
                        array_push($headers, "x-api-key: " . $v);

                        break;
                    case "access_token":
                        array_push($headers, "x-access-token: " . $v);

                        break;
                    default:
                        $v = strtolower($v);
                        array_push($headers, $h . ": " . $v);
                }
            }
        }
        if ($default_content_type) {
            array_push($headers, "content-type: application/json");
        }

        // Build $connect
        $connect = curl_init();

        curl_setopt($connect, CURLOPT_USERAGENT, "Mobbex PHP SDK /v" . MB::version);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($connect, CURLOPT_CAINFO, dirname(__FILE__) . "/cacert.pem");
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $request["method"]);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

        // Set parameters and url
        if (isset ($request["params"]) && is_array($request["params"]) && count($request["params"]) > 0) {
            $request["uri"] .= (strpos($request["uri"], "?") === false) ? "?" : "&";
            $request["uri"] .= self::build_query($request["params"]);
        }
        curl_setopt($connect, CURLOPT_URL, self::API_BASE_URL . $request["uri"]);

        // Set data
        if (isset($request["data"])) {
            if ($json_content) {
                if (gettype($request["data"]) == "string") {
                    json_decode($request["data"], true);
                } else {
                    $request["data"] = json_encode($request["data"]);
                }

                if (function_exists('json_last_error')) {
                    $json_error = json_last_error();
                    if ($json_error != JSON_ERROR_NONE) {
                        throw new MobbexException("JSON Error [{$json_error}] - Data: " . $request["data"]);
                    }
                }
            } else if ($form_content) {
                $request["data"] = self::build_query($request["data"]);
            }

            curl_setopt($connect, CURLOPT_POSTFIELDS, $request["data"]);
        }

        return $connect;
    }

    private static function exec($request)
    {
        $connect = self::build_request($request);

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($api_result === FALSE) {
            throw new MobbexException (curl_error($connect));
        }

        $response = array(
            "status" => $api_http_code,
            "response" => json_decode($api_result, true)
        );

        if ($response['status'] >= 400) {
            $message = $response['response']['error']. " - " . $response['response']['code'];

            throw new MobbexException ($message, $response['status']);
        }

        curl_close($connect);

        return $response;
    }

    private static function build_query($params)
    {
        if (function_exists("http_build_query")) {
            return http_build_query($params, "", "&");
        } else {
            foreach ($params as $name => $value) {
                $elements[] = "{$name}=" . urlencode($value);
            }

            return implode("&", $elements);
        }
    }

    public static function get($request)
    {
        $request["method"] = "GET";

        return self::exec($request);
    }

    public static function post($request)
    {
        $request["method"] = "POST";

        return self::exec($request);
    }

    public static function put($request)
    {
        $request["method"] = "PUT";

        return self::exec($request);
    }

    public static function delete($request)
    {
        $request["method"] = "DELETE";

        return self::exec($request);
    }
}

class MobbexException extends Exception
{
    public function __construct($message, $code = 500, Exception $previous = null)
    {
        // Default code 500
        parent::__construct($message, $code, $previous);
    }
}
