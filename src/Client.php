<?php

namespace codesaur\Http\Client;

use Exception;

class Client
{
    public function request(string $uri, string $method, string $data, array $options)
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $uri,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERAGENT => get_class($this) . ' cURL Request'
        ));

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $options[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . strlen($data);
        }

        foreach ($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
        $response = curl_exec($ch);

        if ($response === FALSE) {
            $code = curl_errno($ch);
            $message = curl_error($ch);
            
            curl_close($ch);
            
            throw new Exception($message, $code);
        }
        
        curl_close($ch);

        return $response;
    }
}
