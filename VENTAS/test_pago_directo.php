<?php
// Archivo de prueba: c:\xampp\htdocs\VENTAS\test_pago_directo.php
// Accede a: http://localhost/VENTAS/test_pago_directo.php

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Pago - Cuenta Corriente</title>
    <link rel="stylesheet" href="app/views/css/bulma.min.css">
</head>
<body>
<div class="container is-fluid p-5">
    <h1 class="title">🧪 Test de Pago - Cuenta Corriente</h1>
    
    <div class="box">
        <h2 class="subtitle">Formulario de Prueba</h2>
        
        <form id="testForm">
            <div class="field">
                <label class="label">Cliente ID</label>
                <div class="control">
                    <input class="input" type="number" id="cliente_id" name="cliente_id" value="2" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Monto</label>
                <div class="control">
                    <input class="input" type="number" id="monto" name="monto" value="100.50" step="0.01" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Fecha</label>
                <div class="control">
                    <input class="input" type="date" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Método de Pago</label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="metodo_pago" required>
                            <option value="EFECTIVO">Efectivo</option>
                            <option value="CHEQUE">Cheque</option>
                            <option value="TRANSFERENCIA">Transferencia</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label">Detalle</label>
                <div class="control">
                    <textarea class="textarea" id="detalle" name="detalle" rows="3"></textarea>
                </div>
            </div>

            <div class="field is-grouped">
                <div class="control">
                    <button type="submit" class="button is-success">Enviar Pago</button>
                </div>
            </div>
        </form>
    </div>

    <div id="resulta" class="box" style="display:none; margin-top: 20px;">
        <h3 class="subtitle">Respuesta del Servidor:</h3>
        <pre id="respuesta" style="background: #f5f5f5; padding: 20px; border-radius: 4px;"></pre>
    </div>
</div>

<script>
document.getElementById('testForm').addEventListener('submit', function(e){
    e.preventDefault();

    const formData = new FormData();
    formData.append('modulo_cc', 'registrar_pago');
    formData.append('cliente_id', document.getElementById('cliente_id').value);
    formData.append('monto', document.getElementById('monto').value);
    formData.append('fecha_pago', document.getElementById('fecha_pago').value);
    formData.append('metodo_pago', document.querySelector('select[name="metodo_pago"]').value);
    formData.append('detalle', document.getElementById('detalle').value);
    formData.append('generar_comprobante', 0);

    console.log('Enviando datos...');

    fetch('app/ajax/cuentaCorrienteAjax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text();
    })
    .then(data => {
        console.log('Response text:', data);
        document.getElementById('resulta').style.display = 'block';
        try {
            const json = JSON.parse(data);
            document.getElementById('respuesta').textContent = JSON.stringify(json, null, 2);
        } catch(e) {
            document.getElementById('respuesta').textContent = 'ERROR PARSING JSON:\n' + data;
            console.error('Error parsing JSON:', e);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        document.getElementById('resulta').style.display = 'block';
        document.getElementById('respuesta').textContent = 'Error de conexión: ' + error.message;
    });
});
</script>
</body>
</html>
