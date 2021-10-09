<?php

namespace codesaur\Http\Client;

use Throwable;

class JSONClient extends Client
{
    public function get(string $uri, $payload = null, $headers = array())
    {
        return $this->request($uri, 'GET', $payload, $headers);
    }
    
    public function post(string $uri, $payload, $headers = array())
    {
        return $this->request($uri, 'POST', $payload, $headers);
    }

    public function request(string $uri, $method, $payload, array $headers)
    {
        try {
            $header = array('Content-Type: application/json');
            
            if (isset($payload)) {
                $data = json_encode($payload);
            } else {
                $data = $method != 'GET' ? '{}' : '';
            }
            
            foreach ($headers as $index => $field) {
                $header[] = "$index: $field";
            }
            
            $options = array(
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => $header
            );
            
            return json_decode(parent::request($uri, $method, $data, $options), true);
        } catch (Throwable $th) {
            return array('error' => array('code' => $th->getCode(), 'message' => $th->getMessage()));
        }
    }
}
