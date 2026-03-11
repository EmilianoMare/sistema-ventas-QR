# Módulo de Tipo de Cambio (Deshabilitado)

Este documento describía la implementación del sistema de tipo de cambio USD ↔ ARS. En la versión actual del proyecto, el módulo de conversión fue deshabilitado y la aplicación opera exclusivamente en pesos argentinos (ARS).

Si necesitas restaurar la funcionalidad dual, revisa estos puntos:

- `config/app.php` — activar `CAMBIO_API_ENABLED` y configurar constantes relacionadas.
- Restaurar `app/controllers/cambioController.php` y `app/ajax/cambioAjax.php` desde el control de versiones.
- Restaurar la vista `app/views/content/cambio-view.php` y los enlaces de menú en `app/views/inc/navlateral.php`.

Nota: los ejemplos y guías sobre conversión fueron actualizados para reflejar este cambio.
