<?php

namespace App\Eden\Managements\Services;

/**
 * Verifsiret Public API
 *
 * @package     API v0.1
 * @author      ATAFOTO.studio
 * @link        http://www.verif-siret.com/api/
 *
 */

/**
 * 
 * API renvoi d'informations sur une entreprise via le numéro de SIRET
 * 
 */
class Siret_api_service {

    # Verifsiret API version
    var $version = 'v0.1';
    
    # Mode debug ? 0 : none; 1 : errors only; 2 : all
    var $debug = 0;

    # Edit with your API keys
    var $apiKey = '51803accb719196ff9ef833c69916a5b';
    var $secretKey = '82d1146ed6a5b8a46132d224b90a777f'; 
	
    # Constructor function
    public function __construct($apiKey = false, $secretKey = false) {
        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
        if ($secretKey) {
            $this->secretKey = $secretKey;
        }
        
        $this->apiUrl = 'https://www.numero-de-siret.com/api';
    }


    public function __call($resource, $args) {
        # Parameters array
        $params  = (sizeof($args) > 0) ? $args[0] : array();

        # Request method, GET by default
        if (isset($params["method"])) {
            $request = strtoupper($params["method"]);
            unset($params['method']);
        }
        else
            $request = 'GET';

        # Request ID, empty by default
        $id      = isset($params["ID"]) ? $params["ID"] : '';

        if ($id == '')
        {
            # Request Unique field, empty by default
            $unique  = isset($params["unique"]) ? $params["unique"] : '';
            unset($params["unique"]);
            # Make request
            $result = $this->sendRequest($resource, $params, $request, $unique);
        }
        else
        {
            # Make request
            $result = $this->sendRequest($resource, $params, $request, $id);
        }

        # Return result
        $return = ($result === true) ? $this->_response : false;
        if ($this->debug == 2 || ($this->debug == 1 && $return == false)) {
            $this->debug();
        }

        return $return;
    }
	
    public function requestUrlBuilder($resource, $params = array(), $request, $id) {
       
        $this->call_url = $this->apiUrl . '/' . $resource;
        
        if (($request == "GET") && (count($params) > 0)) {
            $this->call_url .= '?';
        }

        foreach ($params as $key => $value) {
            if ($request == "GET")
            {
                $query_string[$key] = $key . '=' . $value;
                $this->call_url .= $query_string[$key] . '&';
            }
        }

        if ($request == "GET" && count($params) > 0) {
            $this->call_url = substr($this->call_url, 0, -1);
        }

        if ($request == "VIEW" || $request == "DELETE" || $request == "PUT") {
            if ($id != '') {
                $this->call_url .= '/' . $id;
            }
        }

        return $this->call_url;
    }



    public function sendRequest($resource = false, $params = array(), $request = "GET", $id = '') {
        # Method
        $this->_method  = $resource;
        $this->_request = $request;

        # Build request URL
        $url = $this->requestUrlBuilder($resource, $params, $request, $id);
		
        # Set up and execute the curl process
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl_handle, CURLOPT_USERPWD, $this->apiKey . ':' . $this->secretKey);

        $this->_request_post = false;

        if (($request == 'POST') || ($request == 'PUT')):
           curl_setopt($curl_handle, CURLOPT_POST, 1);
                  
           curl_setopt($curl_handle, CURLOPT_POSTFIELDS, ($params));
           /*curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
                 'Content-Type: multipart/form-data'
           ));*/
            
            $this->_request_post = $params;
        endif;

        if ($request == 'DELETE') {
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        if ($request == 'PUT') {
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "PUT");
        }

        $buffer = curl_exec($curl_handle);
        
        # Response code
        $this->_response_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

        # Close curl process
        curl_close($curl_handle);
        

        # Return response
        $this->_response = json_decode($buffer);
        
   		return true;
   		
    }


	public function execute($siret) {
		
		$retour = array();

		$retour['success'] = $this->sendRequest('siret', ['siret' => $siret]);
		if ($retour['success'] === true)
			$retour['retour'] = $this->_response->array_return[0] ;
		
		return $retour;
	}
}

