<?php

namespace App\Services;

class ApiService
{
    private $baseUrl;
    private $timeout;
    private $token;

    public function __construct()
    {
        $this->baseUrl = getenv('API_BASE_URL') ?: 'http://localhost:8080/api';
        $this->timeout = getenv('API_TIMEOUT') ?: 30;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function get($endpoint, $params = [])
    {
        $url = $this->baseUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->request('GET', $url);
    }

    public function post($endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        return $this->request('POST', $url, $data);
    }


    public function put($endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        return $this->request('PUT', $url, $data);
    }

    public function delete($endpoint)
    {
        $url = $this->baseUrl . $endpoint;
        return $this->request('DELETE', $url);
    }

    private function request($method, $url, $data = null)
    {
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \Exception("Erreur API: " . $error);
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = isset($result['message']) ? $result['message'] : 'Erreur inconnue';
            throw new \Exception("Erreur API ($httpCode): " . $errorMessage);
        }

        return $result;
    }


    public function login($email, $password)
    {
        return $this->post('/auth/login', [
            'email' => $email,
            'mot_de_passe' => $password
        ]);
    }

    public function register($data)
    {
        return $this->post('/auth/register', $data);
    }

    public function getAnnonces($params = [])
    {
        return $this->get('/annonces', $params);
    }

    public function createAnnonce($data)
    {
        return $this->post('/annonces', $data);
    }

    public function getFormations($params = [])
    {
        return $this->get('/formations', $params);
    }

    public function getEvenements($params = [])
    {
        return $this->get('/evenements', $params);
    }

    public function getUpcyclingScore($userId)
    {
        return $this->get("/users/{$userId}/score");
    }

    public function getConteneurs($params = [])
    {
        return $this->get('/conteneurs', $params);
    }

    public function requestConteneurCode($data)
    {
        return $this->post('/conteneurs/request-code', $data);
    }
}