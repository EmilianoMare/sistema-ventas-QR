<?php
require_once __DIR__ . '/meli_api.php';
require_once __DIR__ . '/classifier.php';

function parse_qr_json(string $qr_string)
{
    $data = json_decode($qr_string, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid QR JSON: ' . json_last_error_msg());
    }
    if (!is_array($data)) {
        throw new InvalidArgumentException('QR JSON did not decode to an object/array.');
    }
    return $data;
}

function extract_shipment_id(array $data)
{
    if (empty($data['id'])) {
        throw new InvalidArgumentException('Shipment id not found in QR data.');
    }
    return (string)$data['id'];
}

function display_result(array $info)
{
    // $info keys: shipment_id, address_line, neighborhood, zip_code, latitude, longitude, assigned_route
    $id = $info['shipment_id'] ?? 'N/A';
    $neighborhood = $info['neighborhood'] ?? 'N/A';
    $zip = $info['zip_code'] ?? 'N/A';
    $coords = isset($info['latitude'], $info['longitude']) ? ($info['latitude'] . ',' . $info['longitude']) : 'N/A';
    $route = $info['assigned_route'] ?? 'N/A';

    // Colored output per route (simple ANSI colors)
    $colors = [
        1 => "\033[1;32m", // green
        2 => "\033[1;34m", // blue
        3 => "\033[1;33m", // yellow
        4 => "\033[1;35m", // magenta
        'reset' => "\033[0m",
    ];

    $color = $colors[$route] ?? $colors['reset'];

    echo "SCAN OK\n";
    echo "Shipment ID: {$id}\n";
    echo "Address: " . ($info['address_line'] ?? 'N/A') . "\n";
    echo "Neighborhood: {$neighborhood}\n";
    echo "Zip Code: {$zip}\n";
    echo "Coordinates: {$coords}\n";
    echo "Assigned Route: {$color}{$route}{$colors['reset']}\n";
}
