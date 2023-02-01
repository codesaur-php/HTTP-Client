<?php

namespace codesaur\Http\Client;

class Client
{
    public function request(string $uri, string $method = 'GET', string $data = '', array $options = []): string
    {
        $ch = \curl_init();

        \curl_setopt_array($ch, [
            \CURLOPT_URL => $uri,
            \CURLOPT_RETURNTRANSFER => 1,
            \CURLOPT_CUSTOMREQUEST => $method,
            \CURLOPT_USERAGENT => \get_class($this) . ' cURL Request'
        ]);

        if (!empty($data)) {
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, $data);
            $options[\CURLOPT_HTTPHEADER][] = 'Content-Length: ' . \strlen($data);
        }

        foreach ($options as $option => $value) {
            \curl_setopt($ch, $option, $value);
        }
        $response = \curl_exec($ch);

        if ($response === false) {
            $code = \curl_errno($ch);
            $message = \curl_error($ch);
            
            \curl_close($ch);
            
            throw new \Exception($message, $code);
        }
        
        \curl_close($ch);

        return $response;
    }
}
