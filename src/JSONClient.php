<?php

namespace codesaur\Http\Client;

class JSONClient
{
    public function get(string $uri, array $payload = [], array $headers = []): array
    {
        return $this->request($uri, 'GET', $payload, $headers);
    }
    
    public function post(string $uri, array $payload, array $headers = []): array
    {
        return $this->request($uri, 'POST', $payload, $headers);
    }
    
    public function put(string $uri, array $payload, array $headers = []): array
    {
        return $this->request($uri, 'PUT', $payload, $headers);
    }
    
    public function delete(string $uri, array $payload, array $headers = []): array
    {
        return $this->request($uri, 'DELETE', $payload, $headers);
    }

    public function request(string $uri, string $method, array $payload, array $headers): array
    {
        try {
            $header = ['Content-Type: application/json'];

            foreach ($headers as $index => $field) {
                $header[] = "$index: $field";
            }
            
            $options = [
                \CURLOPT_SSL_VERIFYHOST => false,
                \CURLOPT_SSL_VERIFYPEER => false,
                \CURLOPT_HTTPHEADER     => $header
            ];
            
            $data = empty($payload) ? (\strtoupper($method) == 'GET' ? '' : '{}') :
                (\json_encode($payload) ?: throw new \Exception(__CLASS__ . ': Error encoding request payload!'));
            
            return \json_decode(
                (new Client())->request($uri, $method, $data, $options), true)
                ?? throw new \Exception(__CLASS__ . ": [$uri] Response json cannot be decoded!");
        } catch (\Throwable $th) {
            return ['error' => ['code' => $th->getCode(), 'message' => $th->getMessage()]];
        }
    }
}
