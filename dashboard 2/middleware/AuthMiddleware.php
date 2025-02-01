<?php
class AuthMiddleware {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function validateApiKey() {
        $headers = getallheaders();
        $apiKey = isset($headers['X-API-Key']) ? $headers['X-API-Key'] : null;

        if (!$apiKey) {
            http_response_code(401);
            echo json_encode(array("message" => "Missing API key"));
            return false;
        }

        $query = "SELECT id FROM api_keys WHERE api_key = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$apiKey]);

        if ($stmt->rowCount() == 0) {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid API key"));
            return false;
        }

        return true;
    }
}
?> 