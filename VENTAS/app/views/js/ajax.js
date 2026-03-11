/* Enviar formularios via AJAX */
const formularios_ajax=document.querySelectorAll(".FormularioAjax");

formularios_ajax.forEach(formularios => {

    formularios.addEventListener("submit",function(e){
        
        e.preventDefault();

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Quieres realizar la acción solicitada",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, realizar',
            cancelButtonText: 'No, cancelar'
        }).then((result) => {
            if (result.isConfirmed){

                let data = new FormData(this);
                let method=this.getAttribute("method");
                let action=this.getAttribute("action");

                let encabezados= new Headers();

                let config={
                    method: method,
                    headers: encabezados,
                    mode: 'cors',
                    credentials: 'same-origin',
                    cache: 'no-cache',
                    body: data
                };

                fetch(action,config)
                .then(async respuesta => {
                    const ct = respuesta.headers.get('content-type') || '';
                    // Si el servidor respondió JSON declarado, parsear como JSON
                    if (ct.includes('application/json')){
                        try{
                            const json = await respuesta.json();
                            return alertas_ajax(json);
                        }catch(err){
                            const text = await respuesta.text();
                            console.debug('AJAX invalid JSON for', action, 'status', respuesta.status, 'len', text.length);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error en respuesta JSON del servidor',
                                html: '<div style="text-align:left;max-height:60vh;overflow:auto;white-space:pre-wrap;">'+text.replace(/</g,'&lt;')+'</div>',
                                confirmButtonText: 'Aceptar'
                            });
                            return null;
                        }
                    }

                    // Si no es JSON, tratar como texto/HTML
                    const text = await respuesta.text();
                    console.debug('AJAX response for', action, 'status', respuesta.status, 'len', text.length);
                    if (text.length > 500) console.debug('AJAX response snippet:', text.slice(0,500));
                    try{
                        // Intentar parsear por si el servidor devolvió JSON sin cabecera correcta
                        const maybeJson = JSON.parse(text);
                        return alertas_ajax(maybeJson);
                    }catch(err){
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en respuesta del servidor',
                            html: '<div style="text-align:left;max-height:60vh;overflow:auto;white-space:pre-wrap;">'+text.replace(/</g,'&lt;')+'</div>',
                            confirmButtonText: 'Aceptar'
                        });
                        return null;
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: error.message || 'Ocurrió un error en la petición',
                        confirmButtonText: 'Aceptar'
                    });
                });
            }
        });

    });

});

// Apply data-pattern attributes safely after DOM loaded
document.addEventListener('DOMContentLoaded', function(){
    try{
        const inputs = document.querySelectorAll('input[data-pattern]');
        inputs.forEach(i => {
            const p = i.getAttribute('data-pattern');
            if (!p) return;
            try{
                new RegExp(p);
                i.setAttribute('pattern', p);
            }catch(e1){
                try{ new RegExp(p, 'u'); i.setAttribute('pattern', p); }catch(e2){
                    console.warn('Invalid input pattern skipped for', i.name, p, e2.message);
                }
            }
        });
    }catch(e){ console.warn('Pattern applier error', e); }
});



function alertas_ajax(alerta){
    if(alerta.tipo=="simple"){

        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        });

    }else if(alerta.tipo=="recargar"){

        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if(result.isConfirmed){
                location.reload();
            }
        });

    }else if(alerta.tipo=="limpiar"){

        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if(result.isConfirmed){
                document.querySelector(".FormularioAjax").reset();
            }
        });

    }else if(alerta.tipo=="redireccionar"){
        window.location.href=alerta.url;
    }
}



/* Boton cerrar sesion */
let btn_exit=document.querySelectorAll(".btn-exit");

btn_exit.forEach(exitSystem => {
    exitSystem.addEventListener("click", function(e){

        e.preventDefault();
        
        Swal.fire({
            title: '¿Quieres salir del sistema?',
            text: "La sesión actual se cerrará y saldrás del sistema",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, salir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                let url=this.getAttribute("href");
                window.location.href=url;
            }
        });

    });
});