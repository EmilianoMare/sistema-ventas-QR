<?php require __DIR__ . '/config/app.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Diagnóstico PWA - <?php echo APP_NAME; ?></title>
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:1rem}button{margin:6px;padding:8px}</style>
</head>
<body>
  <h1>Diagnóstico PWA — <?php echo APP_NAME; ?></h1>
  <p><strong>APP_URL:</strong> <?php echo APP_URL; ?></p>
  <section id="manifest">
    <h2>Manifest</h2>
    <pre id="manifestJson">Cargando...</pre>
  </section>

  <section id="sw">
    <h2>Service Worker</h2>
    <p id="swStatus">Comprobando...</p>
    <button id="btnUnregister">Unregister SW</button>
    <button id="btnSkipWaiting">Enviar SKIP_WAITING</button>
  </section>

  <section id="caches">
    <h2>Caches</h2>
    <div id="cacheList">Cargando...</div>
    <button id="btnClearCaches">Borrar todas las caches</button>
  </section>

  <script>
  async function loadManifest(){
    try{
      const res = await fetch('<?php echo APP_URL; ?>manifest.json');
      const j = await res.json();
      document.getElementById('manifestJson').textContent = JSON.stringify(j, null, 2);
    }catch(e){ document.getElementById('manifestJson').textContent = 'Error: '+e; }
  }

  async function updateSwStatus(){
    if (!('serviceWorker' in navigator)) return document.getElementById('swStatus').textContent = 'ServiceWorker no soportado';
    const reg = await navigator.serviceWorker.getRegistration();
    if (!reg) return document.getElementById('swStatus').textContent = 'No hay Service Worker registrado';
    const state = reg.installing ? 'installing' : reg.waiting ? 'waiting' : reg.active ? 'active' : 'unknown';
    document.getElementById('swStatus').textContent = `Registrado (scope: ${reg.scope}) — estado: ${state}`;
  }

  async function listCaches(){
    if (!('caches' in window)) return document.getElementById('cacheList').textContent = 'Cache API no soportada';
    const keys = await caches.keys();
    if (!keys.length) return document.getElementById('cacheList').textContent = 'No hay caches';
    const container = document.createElement('div');
    for (const k of keys){
      const el = document.createElement('div');
      el.style.marginBottom = '8px';
      el.innerHTML = `<strong>${k}</strong>`;
      const list = document.createElement('ul');
      const cache = await caches.open(k);
      const requests = await cache.keys();
      for (const r of requests){
        const li = document.createElement('li'); li.textContent = r.url; list.appendChild(li);
      }
      el.appendChild(list);
      container.appendChild(el);
    }
    const cacheList = document.getElementById('cacheList'); cacheList.innerHTML=''; cacheList.appendChild(container);
  }

  document.getElementById('btnUnregister').addEventListener('click', async ()=>{
    const reg = await navigator.serviceWorker.getRegistration();
    if (!reg) return alert('No hay registro');
    await reg.unregister(); alert('Service Worker unregistered'); updateSwStatus();
  });

  document.getElementById('btnSkipWaiting').addEventListener('click', async ()=>{
    const regs = await navigator.serviceWorker.getRegistrations();
    for (const r of regs){ if (r.waiting) r.waiting.postMessage({type:'SKIP_WAITING'}); }
    alert('Mensajes SKIP_WAITING enviados a los SWs waiting (si hay).');
  });

  document.getElementById('btnClearCaches').addEventListener('click', async ()=>{
    const keys = await caches.keys();
    for (const k of keys) await caches.delete(k);
    alert('Caches eliminadas'); listCaches();
  });

  loadManifest(); updateSwStatus(); listCaches();
  </script>
</body>
</html>
