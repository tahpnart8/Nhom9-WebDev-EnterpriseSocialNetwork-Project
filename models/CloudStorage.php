<?php
/**
 * CloudStorage — Service upload ảnh lên ImgBB Cloud.
 * API key đọc từ biến môi trường IMGBB_API_KEY, có fallback mặc định.
 */
class CloudStorage {
    private string $apiKey;

    public function __construct() {
        $this->apiKey = getenv('IMGBB_API_KEY') ?: '08bd48385edd61af756fc15040768c86';
    }

    public function uploadImage($filePath) {
        $ch = curl_init();
        
        $cFile = new CURLFile($filePath);
        $data = array(
            'key' => $this->apiKey,
            'image' => $cFile
        );
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['data']['url'])) {
            return $result['data']['url']; // Trả link ảnh hiển thị ra Web
        }
        
        return false;
    }

    public function uploadBase64Image($base64Data) {
        $ch = curl_init();
        
        // Cắt bỏ phần header type (vd: data:image/jpeg;base64,)
        if (strpos($base64Data, ',') !== false) {
            $base64Data = explode(',', $base64Data)[1];
        }
        
        $data = array(
            'key' => $this->apiKey,
            'image' => $base64Data
        );
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Base64 yêu cầu gửi dạng application/x-www-form-urlencoded
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['data']['url'])) {
            return $result['data']['url']; 
        }
        
        return false;
    }
}
?>
