<?php
class CloudStorage {
    // API Key miễn phí từ hệ thống ImgBB (Nền tảng Cloud chuyên lưu trữ ảnh)
    private $apiKey = "08bd48385edd61af756fc15040768c86"; 

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
}
?>
