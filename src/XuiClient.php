<?php

declare(strict_types=1);

final class XuiClient
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $cookieFile;

    public function __construct(string $baseUrl, string $username, string $password)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->username = $username;
        $this->password = $password;
        $this->cookieFile = sys_get_temp_dir() . '/xui_cookie_' . md5($baseUrl . $username) . '.txt';
    }

    public function login(): bool
    {
        $response = $this->request('POST', '/login', [
            'username' => $this->username,
            'password' => $this->password,
        ], false);

        return ($response['success'] ?? false) === true || ($response['obj'] ?? null) !== null;
    }

    public function listInbounds(): array
    {
        return $this->request('GET', '/panel/api/inbounds/list');
    }

    public function addClient(array $clientData): array
    {
        return $this->request('POST', '/panel/api/inbounds/addClient', $clientData);
    }

    public function getClientTraffics(string $email): array
    {
        return $this->request('GET', '/panel/api/inbounds/getClientTraffics/' . rawurlencode($email));
    }

    public function serverStatus(): array
    {
        return $this->request('GET', '/panel/api/server/status');
    }

    public function onlineClients(): array
    {
        return $this->request('POST', '/panel/api/inbounds/onlines', []);
    }

    private function request(string $method, string $path, ?array $payload = null, bool $json = true): array
    {
        $ch = curl_init();
        $url = $this->baseUrl . $path;

        $headers = ['Accept: application/json'];
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($payload !== null) {
            $body = $json ? json_encode($payload, JSON_THROW_ON_ERROR) : http_build_query($payload);
            if ($json) {
                $headers[] = 'Content-Type: application/json';
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'msg' => 'cURL error: ' . $err];
        }

        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'status' => $status, 'raw' => $raw];
        }

        $decoded['_status'] = $status;
        return $decoded;
    }
}
