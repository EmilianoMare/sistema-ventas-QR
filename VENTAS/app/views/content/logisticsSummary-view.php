<?php
    // Read CSV and aggregate by neighborhood
    $file = __DIR__ . '/../../../logs/logistics.csv';
    $counts = [];
    $total = 0;
    if(is_file($file) && is_readable($file)){
        if(($fp = fopen($file,'r'))!==false){
            $hdr = fgetcsv($fp);
            while(($row = fgetcsv($fp))!==false){
                $neigh = $row[5] ?? 'Unknown';
                $neigh = $neigh === '' ? 'Unknown' : $neigh;
                if(!isset($counts[$neigh])) $counts[$neigh]=0;
                $counts[$neigh]++;
                $total++;
            }
            fclose($fp);
        }
    }
    // load rates
    $ratesPath = __DIR__ . '/../../../payment_rates.json';
    $rates = [];
    if(is_file($ratesPath) && is_readable($ratesPath)){
        $raw = @file_get_contents($ratesPath);
        $decoded = json_decode($raw,true);
        if(json_last_error()===JSON_ERROR_NONE && is_array($decoded)){
            foreach($decoded as $k=>$v) $rates[mb_strtolower(trim($k),'UTF-8')] = (int)$v;
        }
    }
    arsort($counts);
?>

<div class="container is-fluid">
    <h1 class="title">Logistics Summary</h1>
</div>

<div class="container pb-6">
    <?php if(empty($counts)): ?>
        <div class="notification is-info">No hay datos en logs/logistics.csv</div>
    <?php else: ?>
        <table class="table is-fullwidth is-striped">
            <thead>
                <tr><th>Zone</th><th>Packages</th><th>Rate</th><th>Total</th></tr>
            </thead>
            <tbody>
            <?php $grand=0; foreach($counts as $zone=>$c): $key = mb_strtolower(trim($zone),'UTF-8'); $rate = $rates[$key] ?? 0; $t = $c * $rate; $grand += $t; ?>
                <tr>
                    <td><?= htmlspecialchars($zone) ?></td>
                    <td><?= $c ?></td>
                    <td><?= $rate ?></td>
                    <td><?= $t ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total Packages:</strong> <?= $total ?></p>
        <p><strong>Grand Total:</strong> <?= $grand ?></p>
    <?php endif; ?>

    <div class="mt-4">
        <a class="button is-link" href="<?php echo APP_URL; ?>scanner/">Scan Package</a>
    </div>
</div>
