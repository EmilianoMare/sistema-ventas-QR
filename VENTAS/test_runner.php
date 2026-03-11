<?php
// CLI test runner for the MercadoLibre scanner/classifier.
require_once __DIR__ . '/scanner.php';

$sample_qr = $argv[1] ?? '{"id":"46489481645","sender_id":120289637,"hash_code":"...","security_digit":"0"}';

try {
    $qr = parse_qr_json($sample_qr);
    $shipment_id = extract_shipment_id($qr);

    $access_token = getenv('MELI_ACCESS_TOKEN');
    if (!empty($access_token)) {
        // Real API call
        $shipment = fetch_shipment_data($shipment_id, $access_token);
    } else {
        // Mock response for offline testing
        $shipment = [
            'receiver_address' => [
                'city' => ['name' => 'CABA'],
                'neighborhood' => ['name' => 'Monserrat'],
                'zip_code' => '1095',
            ],
        ];
        fwrite(STDOUT, "MELI_ACCESS_TOKEN not set — using mock response\n");
    }

    $receiver = $shipment['receiver_address'] ?? [];
    $neighborhood = $receiver['neighborhood']['name'] ?? ($receiver['neighborhood'] ?? null);
    $zip_code = $receiver['zip_code'] ?? null;

    $box = classify_zone($neighborhood);

    display_result($shipment_id, $neighborhood, $zip_code, $box);

} catch (Exception $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
