<?php
function fetchData(string $urlPath): ?ApiResponse {
    $url = "https://nsi-api.goedkoop-treinkaartje.nl/api" . $urlPath;
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return null;
    }

    $body = wp_remote_retrieve_body($response);
    $headers = wp_remote_retrieve_headers($response);
    $headersArray = iterator_to_array($headers);
    
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return new ApiResponse($data, $headersArray);
}

class ApiHeaders {
    private array $headers;

    public function __construct(array $headers) {
        $this->headers = $headers;
    }

    public function get(string $name): ?string {
        $lower = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $lower) {
                return $value;
            }
        }
        return null;
    }
}

class ApiResponse {
    public array|object $data;
    public ApiHeaders $headers;

    public function __construct(array $data, array $headers) {
        $this->data = $data;
        $this->headers = new ApiHeaders($headers);
    }
}
