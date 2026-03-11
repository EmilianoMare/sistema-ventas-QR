<div class="container is-fluid">
    <h1 class="title">Reportes</h1>
    <h2 class="subtitle">Ventas - Productos y Clientes</h2>
</div>

<div class="container pb-6">
    <div class="columns">
        <div class="column">
            <div class="box">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <h3 class="subtitle">Productos más vendidos</h3>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <div class="buttons has-addons">
                                <button id="prod-weekly" class="button is-small is-link is-light">Semanal</button>
                                <button id="prod-monthly" class="button is-small">Mensual</button>
                            </div>
                        </div>
                    </div>
                </div>
                <canvas id="chartProducts" style="max-height:360px; height:360px; width:100%;"></canvas>
            </div>
        </div>

        <div class="column">
            <div class="box">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <h3 class="subtitle">Clientes que más compran</h3>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <div class="buttons has-addons">
                                <button id="cli-weekly" class="button is-small is-link is-light">Semanal</button>
                                <button id="cli-monthly" class="button is-small">Mensual</button>
                            </div>
                        </div>
                    </div>
                </div>
                <canvas id="chartClients" style="max-height:360px; height:360px; width:100%;"></canvas>
            </div>
        </div>
    </div>
</div>
 
<div class="container pb-6">
    <div class="box">
        <div class="columns is-mobile is-multiline">
            <div class="column is-3">
                <label class="label">Período</label>
                <div class="select is-fullwidth">
                    <select id="filter-period">
                        <option value="day">Día</option>
                        <option value="weekly" selected>Semana</option>
                        <option value="monthly">Mes</option>
                    </select>
                </div>
            </div>
            <div class="column is-3">
                <label class="label">Producto</label>
                <div class="select is-fullwidth">
                    <select id="filter-product"><option value="0">Todos</option></select>
                </div>
            </div>
            <div class="column is-3">
                <label class="label">Categoría</label>
                <div class="select is-fullwidth">
                    <select id="filter-category"><option value="0">Todas</option></select>
                </div>
            </div>
            <div class="column is-3">
                <label class="label">Cliente</label>
                <div class="select is-fullwidth">
                    <select id="filter-client"><option value="0">Todos</option></select>
                </div>
            </div>
            <div class="column is-12 pt-4">
                <button id="btn-refresh" class="button is-link">Actualizar</button>
            </div>
        </div>
    </div>

    <div class="columns">
        <div class="column is-half">
            <div class="box">
                <h4 class="subtitle">Totales</h4>
                <canvas id="chartTotals" style="max-height:300px; height:300px; width:100%;"></canvas>
            </div>
        </div>
        <div class="column is-half">
            <div class="box">
                <h4 class="subtitle">Ventas por Categoría (Torta)</h4>
                <canvas id="chartCategories" style="max-height:300px; height:300px; width:100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="columns">
        <div class="column is-half">
            <div class="box">
                <h4 class="subtitle">Total por Cliente (Top)</h4>
                <canvas id="chartTotalClients" style="max-height:300px; height:300px; width:100%;"></canvas>
            </div>
        </div>
        <div class="column is-half">
            <div class="box">
                <h4 class="subtitle">Productos próximos a agotarse</h4>
                <div class="table-responsive"><table class="table is-fullwidth is-striped" id="table-lowstock"><thead><tr><th>Producto</th><th>Stock</th><th>Precio</th></tr></thead><tbody></tbody></table></div>
            </div>
        </div>
    </div>

    <div class="box">
        <h4 class="subtitle">Historial de ventas por cliente</h4>
        <div class="table-responsive"><table class="table is-fullwidth" id="table-client-history"><thead><tr><th>Fecha</th><th>Código</th><th>Total</th></tr></thead><tbody></tbody></table></div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const postJSON = (url, data)=> fetch(url, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams(data)}).then(r=>r.json());

    window.reportCharts = window.reportCharts || {};

    function destroyChartIfExists(id){
        const existing = window.reportCharts[id];
        if(existing){
            try{ existing.destroy(); }catch(e){}
            window.reportCharts[id]=null;
        }
    }

    function colorsFor(n){
        const base = [
            'rgba(54, 162, 235, 0.8)','rgba(255, 99, 132, 0.8)','rgba(255, 205, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)','rgba(153, 102, 255, 0.8)','rgba(255, 159, 64, 0.8)',
            'rgba(201, 203, 207, 0.8)','rgba(99, 255, 132, 0.8)','rgba(132, 99, 255, 0.8)','rgba(255, 99, 220, 0.8)'
        ];
        const out = [];
        for(let i=0;i<n;i++) out.push(base[i%base.length]);
        return out;
    }

    function renderChart(canvasId, labels, values, label){
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext('2d');
        // clear canvas content
        ctx.clearRect(0,0,canvas.width,canvas.height);
        destroyChartIfExists(canvasId);
        const bg = colorsFor(values.length);
        window.reportCharts[canvasId] = new Chart(ctx, {
            type: 'pie',
            data: { labels: labels, datasets: [{ label: label, data: values, backgroundColor: bg, borderColor: '#fff', borderWidth: 1 }] },
            options: { responsive:true, maintainAspectRatio:true, aspectRatio:1, animation:{duration:500}, plugins:{legend:{position:'right'}} }
        });
    }

    async function loadProducts(period){
        const data = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'topProducts', period});
        const labels = data.map(d=>d.label);
        const values = data.map(d=>Number(d.value));
        renderChart('chartProducts', labels, values, period=='weekly'?'Unidades vendidas (7 días)':'Unidades vendidas (Mes)');
    }

    async function loadClients(period){
        const data = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'topClients', period});
        const labels = data.map(d=>d.label);
        const values = data.map(d=>Number(d.value));
        renderChart('chartClients', labels, values, period=='weekly'?'Importe ventas (7 días)':'Importe ventas (Mes)');
    }

    // Use onclick to avoid adding multiple listeners on re-render
    document.getElementById('prod-weekly').onclick = function(){ loadProducts('weekly'); this.classList.add('is-link'); document.getElementById('prod-monthly').classList.remove('is-link'); };
    document.getElementById('prod-monthly').onclick = function(){ loadProducts('monthly'); this.classList.add('is-link'); document.getElementById('prod-weekly').classList.remove('is-link'); };
    document.getElementById('cli-weekly').onclick = function(){ loadClients('weekly'); this.classList.add('is-link'); document.getElementById('cli-monthly').classList.remove('is-link'); };
    document.getElementById('cli-monthly').onclick = function(){ loadClients('monthly'); this.classList.add('is-link'); document.getElementById('cli-weekly').classList.remove('is-link'); };

    // additional loaders and filters
    async function loadTotals(period){
        const data = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'totals', period});
        const labels = data.map(d=>d.label);
        const values = data.map(d=>Number(d.value));
        destroyChartIfExists('chartTotals');
        const ctx = document.getElementById('chartTotals').getContext('2d');
        window.reportCharts['chartTotals'] = new Chart(ctx, {type:'line', data:{labels:labels,datasets:[{label:'Total ventas',data:values,backgroundColor:'rgba(54,162,235,0.2)',borderColor:'rgba(54,162,235,1)',fill:true}]}, options:{responsive:true,maintainAspectRatio:false}});
    }

    async function loadCategories(period, category=0){
        const data = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'salesByCategory', period, category});
        const labels = data.map(d=>d.label);
        const values = data.map(d=>Number(d.value));
        renderChart('chartCategories', labels, values, 'Ventas por categoría');
    }

    async function loadTotalPerClient(period){
        const data = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'totalPerClient', period});
        const labels = data.map(d=>d.label);
        const values = data.map(d=>Number(d.value));
        renderChart('chartTotalClients', labels, values, 'Total por cliente');
    }

    async function loadLowStock(threshold=5){
        const data = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'lowStock', threshold});
        const tbody = document.querySelector('#table-lowstock tbody'); tbody.innerHTML='';
        data.forEach(r=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${r.nombre}</td><td>${r.stock}</td><td>${r.precio}</td>`; tbody.appendChild(tr); });
    }

    async function loadClientHistory(client, period){
        if(!client || client==0){ document.querySelector('#table-client-history tbody').innerHTML=''; return; }
        const data = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'clientHistory', client, period});
        const tbody = document.querySelector('#table-client-history tbody'); tbody.innerHTML='';
        data.forEach(r=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${r.fecha}</td><td>${r.codigo}</td><td>${r.total}</td>`; tbody.appendChild(tr); });
    }

    async function loadSelects(){
        const prods = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'listProducts'});
        const prodSel = document.getElementById('filter-product'); prods.forEach(p=>{ const o=document.createElement('option'); o.value=p.id; o.text=p.text; prodSel.appendChild(o); });
        const cats = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'listCategories'});
        const catSel = document.getElementById('filter-category'); cats.forEach(c=>{ const o=document.createElement('option'); o.value=c.id; o.text=c.text; catSel.appendChild(o); });
        const clients = await postJSON(`<?php echo APP_URL; ?>app/ajax/reporteAjax.php`, {modulo_reporte:'listClients'});
        const cliSel = document.getElementById('filter-client'); clients.forEach(c=>{ const o=document.createElement('option'); o.value=c.id; o.text=c.text; cliSel.appendChild(o); });
    }

    document.getElementById('btn-refresh').onclick = function(){
        const period = document.getElementById('filter-period').value;
        const product = document.getElementById('filter-product').value;
        const category = document.getElementById('filter-category').value;
        const client = document.getElementById('filter-client').value;
        loadProducts(period);
        loadClients(period);
        loadTotals(period);
        loadCategories(period, category);
        loadTotalPerClient(period);
        loadLowStock(5);
        loadClientHistory(client, period);
    };

    // initial load: fill selects then refresh
    loadSelects().then(()=>{ document.getElementById('btn-refresh').click(); });

    // load defaults once (kept for backward compatibility)
    loadProducts('weekly');
    loadClients('weekly');
</script>
