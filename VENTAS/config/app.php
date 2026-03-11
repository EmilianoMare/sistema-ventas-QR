<?php

	const APP_URL="http://localhost/VENTAS/";
	const APP_NAME="Ventas Martin";
	const APP_SESSION_NAME="POS";

	/*----------  Tipos de documentos  ----------*/
	const DOCUMENTOS_USUARIOS=["DNI","CUIL", "CUIT"];


	/*----------  Tipos de unidades de productos  ----------*/
const PRODUCTO_UNIDAD = [
  "Unidad",
  "Caja",
  "Paquete",
  "Bolsa",
  "Saco",
  
  "Kit",
  "Juego",
  "Bidón",
  "Otro"
];

	/*----------  Configuración de moneda  ----------*/
	const MONEDA_SIMBOLO="$";
	const MONEDA_NOMBRE="ARS";
	const MONEDA_DECIMALES="2";
	const MONEDA_SEPARADOR_MILLAR=",";
	const MONEDA_SEPARADOR_DECIMAL=".";

	/*----------  Configuración de actualización automática de tipo de cambio (Beta)  ----------*/
	// Activar la actualización automática desde una API externa (requiere conexión a internet)
	const CAMBIO_API_ENABLED = false; // Desactivado: sistema en ARS solamente
	// URL por defecto de la API o fuente que devuelve tasas con base USD
	// Por defecto apuntamos a la página de Cotización Billetes del Banco Nación (se parsea HTML)
	const CAMBIO_API_URL = "https://www.bna.com.ar/Personas";
	// Tiempo de espera en segundos para la petición HTTP
	const CAMBIO_API_TIMEOUT = 5;
	// Tiempo en segundos que se considera válido el valor obtenido por API (TTL)
	const CAMBIO_API_TTL = 1800; // 30 minutos
	// Nombre de la fuente que se guardará en la BD cuando se obtenga desde la API
	const CAMBIO_API_FUENTE = 'API ExchangeRate';


	/*----------  Marcador de campos obligatorios (Font Awesome) ----------*/
	const CAMPO_OBLIGATORIO='&nbsp; <i class="fas fa-edit"></i> &nbsp;';

	/*----------  Zona horaria  ----------*/
	date_default_timezone_set("America/Argentina/Buenos_Aires");

/* ---------- Integraciones adicionales desactivadas ---------- */

/* ---------- Azure Document Intelligence (Document Recognizer) ---------- */
// Para usar OCR estructurado con Azure Document Intelligence configura estas constantes.
// Crea un recurso de "Document Intelligence" (Form Recognizer) en Azure y copia:
// - ENDPOINT: p.ej. https://<your-resource-name>.cognitiveservices.azure.com/
// - API KEY: clave de suscripción
// Luego reemplaza los valores vacíos abajo.
const AZURE_DI_ENDPOINT = '';
const AZURE_DI_API_KEY = '';
const AZURE_DI_API_VERSION = '2023-07-31';


	/*
		Configuración de zona horaria de tu país, para más información visita
		http://php.net/manual/es/function.date-default-timezone-set.php
		http://php.net/manual/es/timezones.php
	*/