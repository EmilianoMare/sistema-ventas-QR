<?php
// AJAX endpoint to process a scanned QR JSON and register logistics entry
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../extractor.php';

$raw = file_get_contents('php://input');
$data = null;
if ($raw === false || $raw === '') {
    // fallback to form field
    $qr = $_POST['qr'] ?? null;
} else {
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $qr = $decoded['qr'] ?? null;
    } else {
        $qr = $_POST['qr'] ?? null;
    }
}

if (empty($qr)) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing qr payload']);
    exit;
}

try {
    $qr_arr = parse_qr($qr);
    $shipment_id = extract_shipment_id($qr_arr);

    // Resolve MELI access token: prefer environment variable, then
    // development fallback from config.php. Production must use env vars.
    $access_token = getenv('MELI_ACCESS_TOKEN');
    if (empty($access_token)) {
        $cfgPath = __DIR__ . '/../../config.php';
        if (is_file($cfgPath)) {
            $cfg = include $cfgPath;
            if (is_array($cfg) && !empty($cfg['meli_access_token'])) {
                $access_token = $cfg['meli_access_token'];
            }
        }
    }
    if (empty($access_token)) {
        http_response_code(500);
        echo json_encode(['message' => 'Server misconfiguration: missing MELI_ACCESS_TOKEN']);
        exit;
    }

    // fetch shipment data (uses existing cache)
    $shipment = fetch_shipment_data($shipment_id, $access_token, true);

    $fields = extract_logistics_fields($shipment ?? []);

    $saved = save_to_csv($shipment_id, $fields);

    echo json_encode([
        'success' => true,
        'shipment_id' => $shipment_id,
        'fields' => $fields,
        'saved' => $saved,
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
    exit;
}
