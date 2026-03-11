<?php
// Simple HTTP endpoint to receive QR JSON and return classification as JSON
require_once __DIR__ . '/scanner.php';

header('Content-Type: application/json; charset=utf-8');
// Allow basic CORS for local testing
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST allowed']);
    exit;
}

$raw = file_get_contents('php://input');
if (empty($raw)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty request body']);
    exit;
}

try {
    $qr = parse_qr_json($raw);
    $shipment_id = extract_shipment_id($qr);

    $access_token = getenv('MELI_ACCESS_TOKEN');
    if (empty($access_token)) {
        http_response_code(500);
        echo json_encode(['error' => 'Server misconfiguration: missing MELI_ACCESS_TOKEN']);
        exit;
    }

    $shipment = fetch_shipment_data($shipment_id, $access_token);
    $receiver = $shipment['receiver_address'] ?? [];
    $neighborhood = $receiver['neighborhood']['name'] ?? ($receiver['neighborhood'] ?? null);
    $zip_code = $receiver['zip_code'] ?? null;

    if (empty($neighborhood) && empty($zip_code)) {
        http_response_code(502);
        echo json_encode(['error' => 'Missing address data from MercadoLibre response']);
        exit;
    }

    $box = classify_zone($neighborhood);

    echo json_encode([
        'shipment_id' => $shipment_id,
        'neighborhood' => $neighborhood,
        'zip_code' => $zip_code,
        'assigned_box' => $box,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
