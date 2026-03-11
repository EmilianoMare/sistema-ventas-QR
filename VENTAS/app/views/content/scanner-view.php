<div class="container is-fluid">
    <h1 class="title">Scanner</h1>
    <p class="subtitle">Usar la cámara del dispositivo para escanear códigos QR de etiquetas de Mercado Libre.</p>
</div>

<div class="container pb-6">
    <div class="box">
        <div class="columns">
            <div class="column is-half">
                <video id="video" playsinline style="width:100%;border:1px solid #ddd;border-radius:4px"></video>
                <canvas id="canvas" style="display:none"></canvas>
            </div>
            <div class="column is-half">
                <div id="result"></div>
                <div class="buttons mt-3">
                    <button id="btn-restart" class="button is-link">Reiniciar cámara</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    let scanning = false;
    let stream = null;

    async function startCamera(){
        try{
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream; await video.play(); scanning=true; requestAnimationFrame(tick);
        }catch(e){ document.getElementById('result').innerHTML = '<div class="notification is-danger">Acceso a cámara denegado o no disponible.</div>'; }
    }
    function stopCamera(){ scanning=false; if(stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; }}
    function tick(){ if(!scanning) return; if(video.readyState===video.HAVE_ENOUGH_DATA){ canvas.width=video.videoWidth; canvas.height=video.videoHeight; ctx.drawImage(video,0,0,canvas.width,canvas.height); const imageData=ctx.getImageData(0,0,canvas.width,canvas.height); const code=jsQR(imageData.data,imageData.width,imageData.height,{inversionAttempts:'attemptBoth'}); if(code){ stopCamera(); handleQRCode(code.data); return; } } requestAnimationFrame(tick); }

    async function handleQRCode(qrText){
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = '<div class="notification is-info">Procesando escaneo...</div>';
        try{
            const res = await fetch('<?php echo APP_URL; ?>app/ajax/scan_package.php',{
                method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({qr: qrText})
            });
            const json = await res.json();
            if(!res.ok) throw new Error(json.message || 'Error en servidor');
            const f = json.fields || {};
            let html = '';
            if(json.saved===false){ html += '<div class="notification is-warning">Escaneo duplicado — ya existe la entrada.</div>'; }
            html += '<div class="notification is-success">Paquete registrado correctamente.</div>';
            html += '<div class="content"><ul>'; html += '<li><strong>Shipment ID:</strong> ' + (json.shipment_id||'') + '</li>'; html += '<li><strong>Sale Number:</strong> ' + (f.order_id||'') + '</li>'; html += '<li><strong>Customer:</strong> ' + (f.customer_name||'') + '</li>'; html += '<li><strong>Neighborhood:</strong> ' + (f.neighborhood||'') + '</li>'; html += '<li><strong>City:</strong> ' + (f.city||'') + '</li>'; html += '<li><strong>Zip Code:</strong> ' + (f.zip_code||'') + '</li>'; html += '</ul></div>';
            resultDiv.innerHTML = html;
        }catch(err){ resultDiv.innerHTML = '<div class="notification is-danger">'+(err.message||'Error')+'</div>'; }
        setTimeout(()=>startCamera(),2000);
    }

    document.getElementById('btn-restart').addEventListener('click', ()=>{ if(!scanning) startCamera(); });
    startCamera();
</script>
