<?php
class DriveStorage {
    private $folderId = "1boTtYbFHUU57XDZgz0X8anp3fGA1w99x";
    private $credentialFile = __DIR__ . "/../config/drive_credentials.json";

    // Hàm tạo Access Token bằng JSON Web Token (JWT) + OpenSSL + cURL (Không dùng Composer)
    private function getAccessToken() {
        if (!file_exists($this->credentialFile)) {
            return false;
        }
        $credentials = json_decode(file_get_contents($this->credentialFile), true);
        
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'aud' => $credentials['token_uri'],
            'exp' => time() + 3600,
            'iat' => time()
        ]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $credentials['private_key'], 'sha256WithRSAEncryption');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        // Gọi Google OAuth Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $credentials['token_uri']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Cần thiết trong XAMPP
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        return $data['access_token'] ?? false;
    }

    public function uploadFile($filePath, $mimeType, $originalName) {
        $token = $this->getAccessToken();
        if (!$token) return false;

        $metadata = json_encode([
            'name' => $originalName,
            'parents' => [$this->folderId]
        ]);

        $boundary = '-------314159265358979323846';
        $body = "--" . $boundary . "\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= $metadata . "\r\n";
        $body .= "--" . $boundary . "\r\n";
        $body .= "Content-Type: " . $mimeType . "\r\n\r\n";
        $body .= file_get_contents($filePath) . "\r\n";
        $body .= "--" . $boundary . "--\r\n";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,webViewLink,webContentLink');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: multipart/related; boundary=' . $boundary,
            'Content-Length: ' . strlen($body)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        // Mở khóa file thành Public URL (Mọi người có link đều có thể xem ảnh)
        if (isset($result['id'])) {
            $this->setPublicPermission($result['id'], $token);
        }
        
        return $result;
    }
    
    private function setPublicPermission($fileId, $token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/drive/v3/files/' . $fileId . '/permissions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'type' => 'anyone',
            'role' => 'reader'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }
}
?>
