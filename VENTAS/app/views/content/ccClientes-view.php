<div class="container is-fluid mb-6">
    <h1 class="title">Clientes - Cuenta Corriente</h1>
</div>

<div class="container pb-6 pt-6">

    <!-- Buscador por Nombre / Apellido -->
    <div class="field">
        <div class="control has-icons-left">
            <input id="cc-client-search-input" class="input" type="search" placeholder="Buscar cliente por nombre o apellido" aria-label="Buscar cliente por nombre o apellido">
            <span class="icon is-left"><i class="fas fa-search"></i></span>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive"><table class="table is-bordered is-striped is-fullwidth">

            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Saldo</th>
                    <th class="has-text-centered">Movimientos</th>
                    <th class="has-text-centered">Descargar Resumen</th>
                    <th class="has-text-centered">Pagar</th>
                </tr>
            </thead>

            <tbody>
                <?php

    use app\controllers\clientController;

    $insCliente = new clientController();

    $clientes = $insCliente->listarClientesCuentaCorriente();

    while($row=$clientes->fetch()){
        $saldo = isset($row['saldo']) ? floatval($row['saldo']) : 0.00;
        echo '
        <tr>
            <td>'.$row['cliente_nombre'].' '.$row['cliente_apellido'].'</td>
            <td>'.$row['cliente_telefono'].'</td>
            <td>
                <strong>ARS:</strong> '.MONEDA_SIMBOLO.number_format($saldo,2).'
            </td>

            <td class="has-text-centered">
                <a href="'.APP_URL.'ccMovimientos/'.$row['cliente_id'].'/" class="button is-info is-small">
                    <i class="fas fa-eye"></i>
                </a>
            </td>

            <td class="has-text-centered">
                <a href="'.APP_URL.'app/pdf/resumen_cuenta.php?cliente_id='.$row['cliente_id'].'" class="button is-warning is-small" target="_blank">
                    <i class="fas fa-download"></i>
                </a>
            </td>

            <td class="has-text-centered">
                <a href="'.APP_URL.'ccPago/'.$row['cliente_id'].'/" class="button is-success is-small">
                    <i class="fas fa-dollar-sign"></i>
                </a>
            </td>
        </tr>';
    }
?>
            </tbody>

        </table></div>
    </div>
</div>

<script>
(function(){
    const input = document.getElementById('cc-client-search-input');
    const tbody = document.querySelector('.table-container table tbody');
    if(!input || !tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr'));
    const noResultRow = document.createElement('tr');
    noResultRow.className = 'cc-no-results has-text-centered';
    noResultRow.innerHTML = '<td colspan="6">No se encontraron clientes</td>';

    const normalize = s => String(s||'').trim().toLowerCase();

    const filter = () => {
        const q = normalize(input.value);
        let visible = 0;

        rows.forEach(row => {
            // skip rows that are not data rows
            const nameCell = row.querySelector('td');
            if(!nameCell) return;
            const nameText = normalize(nameCell.textContent);

            if(q === '' || nameText.indexOf(q) !== -1){
                row.style.display = '';
                visible++;
            }else{
                row.style.display = 'none';
            }
        });

        const existing = tbody.querySelector('.cc-no-results');
        if(visible === 0){
            if(!existing) tbody.appendChild(noResultRow);
        }else{
            if(existing) existing.remove();
        }
    };

    let t;
    input.addEventListener('input', () => { clearTimeout(t); t = setTimeout(filter, 180); });
    input.addEventListener('keydown', (e) => { if(e.key === 'Escape'){ input.value=''; filter(); } });
})();
</script>