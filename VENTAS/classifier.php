<?php

function normalize_neighborhood(string $s): string
{
    $s = trim(mb_strtolower($s, 'UTF-8'));
    $trans = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    if ($trans !== false) {
        $s = $trans;
    }
    $s = preg_replace('/[^a-z0-9\s]/', '', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return $s;
}

function classify_zone(?string $neighborhood): int
{
    if (empty($neighborhood)) {
        return 6; // Other
    }
    $n = normalize_neighborhood($neighborhood);
    // Deprecated: simple numeric zones. Use classify_route() instead when possible.
    if (strpos($n, 'monserrat') !== false) {
        return 1;
    }
    if (strpos($n, 'san telmo') !== false || strpos($n, 'santelmo') !== false) {
        return 2;
    }
    if (strpos($n, 'palermo') !== false) {
        return 3;
    }
    if (strpos($n, 'recoleta') !== false) {
        return 4;
    }
    if (strpos($n, 'caballito') !== false) {
        return 5;
    }

    return 6; // Other
}

function load_routes_config(): ?array
{
    $path = __DIR__ . '/routes.json';
    if (!is_file($path)) {
        return null;
    }
    $raw = @file_get_contents($path);
    if ($raw === false) {
        return null;
    }
    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return null;
    }
    return $decoded;
}

function classify_route(?float $latitude, ?float $longitude, ?string $neighborhood): int
{
    // Load dynamic routes config if present. Expected format: { "1": ["monserrat","san telmo"], "2": ["palermo","recoleta"], ... }
    $config = load_routes_config();

    $n = $neighborhood ? normalize_neighborhood($neighborhood) : '';

    if ($config && is_array($config)) {
        foreach ($config as $routeNum => $keywords) {
            if (!is_array($keywords)) {
                continue;
            }
            foreach ($keywords as $kw) {
                $kwn = normalize_neighborhood((string)$kw);
                if ($kwn !== '' && strpos($n, $kwn) !== false) {
                    return (int)$routeNum;
                }
            }
        }
        // If config exists but no neighborhood match, assign to 'other' route key if present
        if (isset($config['other'])) {
            return (int)$config['other'];
        }
    }

    // Default hard-coded mapping
    if (!empty($n)) {
        if (strpos($n, 'monserrat') !== false || strpos($n, 'san telmo') !== false || strpos($n, 'santelmo') !== false) {
            return 1;
        }
        if (strpos($n, 'palermo') !== false || strpos($n, 'recoleta') !== false) {
            return 2;
        }
        if (strpos($n, 'caballito') !== false || strpos($n, 'almagro') !== false) {
            return 3;
        }
    }

    return 4; // Other areas
}
