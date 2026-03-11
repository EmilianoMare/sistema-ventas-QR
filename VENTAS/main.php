<?php
require_once __DIR__ . '/extractor.php';

// Summary/report helper: read CSV and aggregate by neighborhood
function summarize_logistics_csv(string $file = null): array
{
    $file = $file ?? (__DIR__ . '/logs/logistics.csv');
    $counts = [];
    $total = 0;
    if (!is_file($file) || !is_readable($file)) {
        return ['counts' => $counts, 'total' => $total];
    }
    if (($fp = fopen($file, 'r')) === false) {
        return ['counts' => $counts, 'total' => $total];
    }
    // header
    $hdr = fgetcsv($fp);
    while (($row = fgetcsv($fp)) !== false) {
        $neighborhood = $row[5] ?? 'Unknown';
        $neighborhood = $neighborhood === '' ? 'Unknown' : $neighborhood;
        if (!isset($counts[$neighborhood])) {
            $counts[$neighborhood] = 0;
        }
        $counts[$neighborhood]++;
        $total++;
    }
    fclose($fp);
    arsort($counts);
    return ['counts' => $counts, 'total' => $total];
}

function load_payment_rates(string $path = null): array
{
    $path = $path ?? (__DIR__ . '/payment_rates.json');
    if (!is_file($path) || !is_readable($path)) {
        return [];
    }
    $raw = @file_get_contents($path);
    if ($raw === false) {
        return [];
    }
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return [];
    }
    // normalize keys to lower-case trimmed for matching
    $out = [];
    foreach ($data as $k => $v) {
        $out[trim(mb_strtolower($k, 'UTF-8'))] = (int)$v;
    }
    return $out;
}

// Acquire QR JSON input: CLI argument, STDIN, or HTTP body
function read_input_qr(): ?string
{
    if (PHP_SAPI === 'cli') {
        global $argv;
        if (!empty($argv[1])) {
            return $argv[1];
        }
        $stdin = trim(stream_get_contents(STDIN));
        return $stdin ?: null;
    }

    // For web usage, accept raw POST body or 'qr' POST field
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        return $raw;
    }
    return $_POST['qr'] ?? null;
}

// If invoked as: php main.php summary
if (PHP_SAPI === 'cli' && isset($argv) && !empty($argv[1]) && $argv[1] === 'summary') {
    // parse optional args: --rates=path and --help
    $ratesPath = null;
    $showHelp = false;
    foreach ($argv as $i => $arg) {
        if ($i === 0 || $i === 1) continue;
        if ($arg === '--help' || $arg === '-h') {
            $showHelp = true;
            break;
        }
        if (strpos($arg, '--rates=') === 0) {
            $ratesPath = substr($arg, strlen('--rates='));
        }
    }

    if ($showHelp) {
        echo "Usage:\n";
        echo "  php main.php summary\n";
        echo "  php main.php summary --rates=payment_rates.json\n";
        echo "\nSummarize logistics counts by neighborhood. Optionally provide a JSON rates file for payment calculations.\n";
        echo "If provided, the rates file should be a JSON object mapping neighborhood names to integer rates (example file: payment_rates.json).\n";
        echo "Zones not present in the rates file default to a rate of 0.\n";
        echo "Example rates file content:\n";
        echo "{\n  \"Monserrat\": 1200,\n  \"San Telmo\": 1200,\n  \"Palermo\": 1300\n}\n";
        exit(0);
    }

    $report = summarize_logistics_csv();
    echo "## Logistics Summary by Zone\n\n";
    if (empty($report['counts'])) {
        echo "No data found in logs/logistics.csv\n";
        exit(0);
    }
    // Print counts
    $maxNameLen = 0;
    foreach ($report['counts'] as $n => $_) {
        $len = mb_strlen($n, 'UTF-8');
        if ($len > $maxNameLen) $maxNameLen = $len;
    }
    foreach ($report['counts'] as $n => $c) {
        printf("%s : %d\n", str_pad($n, $maxNameLen, ' '), $c);
    }
    echo "\nTotal Packages: " . $report['total'] . "\n";

    // Payment calculation if payment_rates.json exists or --rates provided
    $rates = [];
    if ($ratesPath !== null) {
        if (!is_file($ratesPath) || !is_readable($ratesPath)) {
            fwrite(STDERR, "Warning: rates file '{$ratesPath}' not found or unreadable. Skipping payment calculations.\n");
            $rates = [];
        } else {
            $rates = load_payment_rates($ratesPath);
        }
    } else {
        $rates = load_payment_rates();
    }

    if (!empty($rates)) {
        echo "\nZone        Packages   Rate   Total\n";
        $grand = 0;
        foreach ($report['counts'] as $n => $c) {
            $key = mb_strtolower(trim($n), 'UTF-8');
            $rate = $rates[$key] ?? 0;
            $total = $c * $rate;
            $grand += $total;
            printf("%-12s %-9d %-6s %-d\n", $n, $c, $rate, $total);
        }
        echo "\nGrand Total: {$grand}\n";
    }
    exit(0);
}

try {
    $qr_string = read_input_qr();
    if ($qr_string === null) {
        throw new InvalidArgumentException('No QR input provided. Pass JSON as first CLI arg or via stdin/post.');
    }

    $qr_data = parse_qr($qr_string);
    $shipment_id = extract_shipment_id($qr_data);

    $access_token = getenv('MELI_ACCESS_TOKEN');
    if (empty($access_token)) {
        throw new RuntimeException('Missing environment variable MELI_ACCESS_TOKEN');
    }

    // Fetch shipment data (uses cache if available)
    $shipment = null;
    try {
        $shipment = fetch_shipment_data($shipment_id, $access_token, true);
    } catch (Exception $ex) {
        fwrite(STDERR, 'Error fetching shipment data: ' . $ex->getMessage() . "\n");
        exit(1);
    }

    $fields = extract_logistics_fields($shipment ?? []);

    // Format date if available
    $dateStr = $fields['date_created'] ?? null;
    $displayDate = null;
    if (!empty($dateStr)) {
        try {
            $dt = new DateTime($dateStr);
            $displayDate = $dt->format('Y-m-d');
        } catch (Exception $e) {
            $displayDate = $dateStr;
        }
    }

    // Display result
    echo "Shipment ID: {$shipment_id}\n";
    echo "Sale Number: " . ($fields['order_id'] ?? 'N/A') . "\n";
    echo "Date: " . ($displayDate ?? 'N/A') . "\n";
    echo "Customer: " . ($fields['customer_name'] ?? 'N/A') . "\n";
    echo "Neighborhood: " . ($fields['neighborhood'] ?? 'N/A') . "\n";
    echo "City: " . ($fields['city'] ?? 'N/A') . "\n";
    echo "Zip Code: " . ($fields['zip_code'] ?? 'N/A') . "\n";

    // Save to CSV; prevent duplicates
    $saved = save_to_csv($shipment_id, $fields);
    if ($saved) {
        echo "\nSaved to logs/logistics.csv\n";
    } else {
        echo "\nEntry already exists in logs/logistics.csv (skipped)\n";
    }

} catch (Exception $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
