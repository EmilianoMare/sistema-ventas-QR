# ✅ SISTEMA DE PAGOS - CUENTA CORRIENTE OPERATIVO

## 📋 ESTADO ACTUAL

Las tablas ya existen en la base de datos:
- ✓ `cuenta_corriente` (cc_id, cliente_id, saldo)
- ✓ `cuenta_corriente_movimiento` (ccm_id, cliente_id, tipo, monto, detalle, fecha)

---

## 🚀 PARA HACER FUNCIONAR EL SISTEMA

**No necesitas hacer nada más.** El sistema ya debería funcionar.

### Intenta registrar un pago:
1. Ve a **Cuentas Corrientes → Clientes**
2. Haz clic en botón **"Pagar"** 💵 de un cliente (que tenga deuda)
3. Llena el formulario:
   - Cliente (dropdown)
   - Monto a pagar
   - Fecha del pago
   - Método de pago (selecciona uno)
4. Haz clic en **"Registrar Pago"**
5. Confirma en el popup

---

## 📝 MEJORAS OPCIONALES

Si deseas agregar campos adicionales (método de pago, número de operación, usuario), ejecuta:

**Archivo:** `DB/mejorar_cuenta_corriente.sql`

**Por phpMyAdmin:**
1. Base de datos: `ventas`
2. Pestaña: SQL
3. Copia contenido de `mejorar_cuenta_corriente.sql`
4. Ejecuta

Esto agregará columnas opcionales sin romper lo existente.

---

## 🆘 SI NO FUNCIONA

### Verifica:
- [ ] ¿El cliente tiene deuda (saldo > 0)?
- [ ] ¿Abriste la consola del navegador (F12) para ver errores?
- [ ] ¿El dropdown de clientes muestra clientes con deuda?

### Ver error en consola (F12):
1. Presiona **F12** en el navegador
2. Ve a pestaña **"Console"**
3. Intenta registrar pago
4. ¿Qué error sale?

---

## 📊 ESTRUCTURA DE TABLAS QUE TIENES

```sql
-- Tabla 1: Cuentas
CREATE TABLE cuenta_corriente (
  cc_id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  saldo DECIMAL(30,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (cliente_id) REFERENCES cliente(cliente_id)
);

-- Tabla 2: Movimientos
CREATE TABLE cuenta_corriente_movimiento (
  ccm_id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  tipo ENUM('DEBE','HABER') NOT NULL,
  monto DECIMAL(30,2) NOT NULL,
  detalle VARCHAR(255),
  fecha DATETIME NOT NULL,
  FOREIGN KEY (cliente_id) REFERENCES cliente(cliente_id)
);
```

---

**Última actualización:** 12/02/2026
