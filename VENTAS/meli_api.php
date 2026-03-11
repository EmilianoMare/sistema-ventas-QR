<?php
// Attempt to resolve the MELI access token.
// This is a development-friendly resolver: it prefers the environment
// variable `MELI_ACCESS_TOKEN` but will load `config.php` in the project
// root as a fallback. Production should always provide the env var.
function resolve_meli_access_token(?string $access_token = null): ?string
{
    if (!empty($access_token)) {
        return $access_token;
    }
    $env = getenv('MELI_ACCESS_TOKEN');
    if (!empty($env)) {
        return $env;
    }
    $cfgPath = __DIR__ . '/config.php';
    if (is_file($cfgPath)) {
        $cfg = include $cfgPath;
        if (is_array($cfg) && !empty($cfg['meli_access_token'])) {
            return $cfg['meli_access_token'];
        }
    }
    return null;
}

function get_cache_path_for_shipment(string $shipment_id): string
{
    $dir = __DIR__ . '/cache/shipments';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $shipment_id) . '.json';
}

function fetch_shipment_data(string $shipment_id, ?string $access_token = null, bool $use_cache = true)
{
    $cacheFile = get_cache_path_for_shipment($shipment_id);

    // Resolve access token if caller didn't provide one.
    $access_token = resolve_meli_access_token($access_token);
    if (empty($access_token)) {
        throw new RuntimeException('Server misconfiguration: missing MELI_ACCESS_TOKEN');
    }

    if ($use_cache && is_file($cacheFile)) {
        $cached = @file_get_contents($cacheFile);
        if ($cached !== false) {
            $decoded = json_decode($cached, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
    }

    $url = 'https://api.mercadolibre.com/shipments/' . urlencode($shipment_id);

    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Accept: application/json',
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $body = curl_exec($ch);
    if ($body === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('cURL error while calling MercadoLibre API: ' . $err);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        $msg = "MercadoLibre API returned HTTP {$httpCode}";
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // Include decoded body for easier debugging (full JSON response)
            $msg .= ': ' . json_encode($decoded, JSON_UNESCAPED_UNICODE);
        } else {
            // Include raw body when not valid JSON
            $msg .= ': ' . $body;
        }
        throw new RuntimeException($msg);
    }

    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Failed to decode MercadoLibre API response: ' . json_last_error_msg());
    }

    // Store in cache
    @file_put_contents($cacheFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);

    return $data;
}
