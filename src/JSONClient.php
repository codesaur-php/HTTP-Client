<?php

namespace codesaur\Http\Client;

class JSONClient extends Client
{
    public function get(string $uri, array $payload = [], array $headers = []): array
    {
        return $this->request($uri, 'GET', $payload, $headers);
    }
    
    public function post(string $uri, array $payload, array $headers = []): array
    {
        return $this->request($uri, 'POST', $payload, $headers);
    }

    public function request(string $uri, string $method, array $payload, array $headers): array
    {
        try {
            $header = ['Content-Type: application/json'];
            
            if (empty($payload)) {
                $data = $method != 'GET' ? '{}' : '';
            } else {
                $data = json_encode($payload);
            }
            
            foreach ($headers as $index => $field) {
                $header[] = "$index: $field";
            }
            
            $options = [
                \CURLOPT_SSL_VERIFYHOST => false,
                \CURLOPT_SSL_VERIFYPEER => false,
                \CURLOPT_HTTPHEADER     => $header
            ];
            
            return json_decode(parent::request($uri, $method, $data, $options), true);
        } catch (\Throwable $th) {
            return ['error' => ['code' => $th->getCode(), 'message' => $th->getMessage()]];
        }
    }
}
