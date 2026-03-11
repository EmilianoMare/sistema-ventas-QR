<?php
require_once __DIR__ . '/meli_api.php';

function parse_qr(string $qr_string): array
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

function extract_shipment_id(array $data): string
{
    if (empty($data['id'])) {
        throw new InvalidArgumentException('Shipment id not found in QR data.');
    }
    return (string)$data['id'];
}

function extract_logistics_fields(array $shipment): array
{
    // Fields to extract:
    // order_id, date_created, receiver_address.receiver_name, receiver_address.neighborhood.name,
    // receiver_address.city.name, receiver_address.zip_code

    $order_id = $shipment['order_id'] ?? null;
    $date_created = $shipment['date_created'] ?? null;

    $receiver = $shipment['receiver_address'] ?? [];
    $customer_name = $receiver['receiver_name'] ?? null;

    // neighborhood may be object or string
    $neighborhood = null;
    if (isset($receiver['neighborhood'])) {
        if (is_array($receiver['neighborhood'])) {
            $neighborhood = $receiver['neighborhood']['name'] ?? null;
        } else {
            $neighborhood = $receiver['neighborhood'];
        }
    }

    $city = is_array($receiver['city'] ?? null) ? ($receiver['city']['name'] ?? null) : ($receiver['city'] ?? null);
    $zip_code = $receiver['zip_code'] ?? null;

    return [
        'order_id' => $order_id,
        'date_created' => $date_created,
        'customer_name' => $customer_name,
        'neighborhood' => $neighborhood,
        'city' => $city,
        'zip_code' => $zip_code,
    ];
}

function save_to_csv(string $shipment_id, array $fields, string $dir = null): bool
{
    $dir = $dir ?? (__DIR__ . '/logs');
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . '/logistics.csv';

    $exists = is_file($file);

    // Check duplicates: scan file for shipment_id in second column
    if ($exists) {
        if (($fp = fopen($file, 'r')) !== false) {
            // skip header
            $hdr = fgetcsv($fp);
            while (($row = fgetcsv($fp)) !== false) {
                if (isset($row[1]) && (string)$row[1] === (string)$shipment_id) {
                    fclose($fp);
                    return false; // duplicate
                }
            }
            fclose($fp);
        }
    }

    $fp = fopen($file, 'a');
    if ($fp === false) {
        return false;
    }

    if (!$exists) {
        fputcsv($fp, ['timestamp', 'shipment_id', 'order_id', 'date_created', 'customer_name', 'neighborhood', 'city', 'zip_code']);
    }

    $timestamp = (new DateTime())->format(DATE_ATOM);
    $row = [
        $timestamp,
        $shipment_id,
        $fields['order_id'] ?? null,
        $fields['date_created'] ?? null,
        $fields['customer_name'] ?? null,
        $fields['neighborhood'] ?? null,
        $fields['city'] ?? null,
        $fields['zip_code'] ?? null,
    ];
    fputcsv($fp, $row);
    fclose($fp);
    return true;
}
