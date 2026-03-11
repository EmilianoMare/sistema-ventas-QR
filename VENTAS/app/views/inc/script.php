<script src="<?php echo APP_URL; ?>app/views/js/ajax.js" ></script>
<script src="<?php echo APP_URL; ?>app/views/js/main.js" ></script>
<!-- overflow-debug removed -->
<?php /* PWA: register service worker */ ?>
<script>
if ('serviceWorker' in navigator) {
	window.addEventListener('load', function() {
		navigator.serviceWorker.register('<?php echo APP_URL; ?>service-worker.js')
			.then(function(reg){ console.log('ServiceWorker registrado:', reg.scope); })
			.catch(function(err){ console.log('ServiceWorker fallo:', err); });
	});
}
</script>
<script>
// PWA install prompt handling
let deferredPrompt;
function createInstallButton(){
	if (document.getElementById('pwa-install-btn')) return;
	const btn = document.createElement('button');
	btn.id = 'pwa-install-btn';
	btn.textContent = 'Instalar app';
	btn.style.position = 'fixed';
	btn.style.right = '16px';
	btn.style.bottom = '16px';
	btn.style.zIndex = '9999';
	btn.style.padding = '10px 14px';
	btn.style.background = '#3273dc';
	btn.style.color = '#fff';
	btn.style.border = 'none';
	btn.style.borderRadius = '6px';
	btn.style.cursor = 'pointer';
	btn.addEventListener('click', async () => {
		if (!deferredPrompt) return;
		deferredPrompt.prompt();
		const choice = await deferredPrompt.userChoice;
		if (choice.outcome === 'accepted') console.log('Usuario instaló la PWA');
		deferredPrompt = null;
		btn.remove();
	});
	document.body.appendChild(btn);
}

window.addEventListener('beforeinstallprompt', (e) => {
	e.preventDefault();
	deferredPrompt = e;
	createInstallButton();
});

// Listen for updates from service worker and show a small prompt
navigator.serviceWorker && navigator.serviceWorker.addEventListener('controllerchange', function(){
	console.log('Service worker controller changed');
});
</script>