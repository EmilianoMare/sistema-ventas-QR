# MercadoLibre QR Scanner — Quick README

Usage (CLI test runner):

1. Run with mock data (no token required):

```bash
php test_runner.php
```

2. Run with real MercadoLibre API (set env var `MELI_ACCESS_TOKEN`):

Linux/macOS:
```bash
export MELI_ACCESS_TOKEN="YOUR_TOKEN"
php test_runner.php '{"id":"46489481645"}'
```

Windows (PowerShell):
```powershell
$env:MELI_ACCESS_TOKEN = 'YOUR_TOKEN'
php test_runner.php '{"id":"46489481645"}'
```

HTTP endpoint (local testing):

Start PHP built-in server from project root and POST JSON:

```bash
php -S 127.0.0.1:8000
# then POST to http://127.0.0.1:8000/api_endpoint.php with raw JSON body
```

Notes:
- The code reads `MELI_ACCESS_TOKEN` from environment variables for authenticated API calls.
- `test_runner.php` uses a mock response unless the token is set.
