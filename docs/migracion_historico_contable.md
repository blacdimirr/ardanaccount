# Migración histórica contable (ADM)

## 1) Inventario de archivos y hojas (HistoricoContable)

| Mes carpeta | Archivo MATRIZ seleccionado | mtime | Duplicados/alternativos | Hoja pagos | Hoja OC | Hoja ingresos | Filas estimadas (pagos/OC/ingresos) |
| --- | --- | --- | --- | --- | --- | --- | --- |
| ABRIL 2025 | MATRIZ ACTUALIZADA 04-2025.xlsx | 2026-01-13 01:37:42 | - | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 179 / 175 / 58 |
| AGOSTO 2025 | MATRIZ ACTUALIZADA AGOSTO 2025 actualizada Angelita.xlsx | 2026-01-13 01:37:42 | Matriz POA CEAS Municipales y Provinciales Act 21072025 (1).xlsx | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 185 / 191 / 53 |
| DICIEMBRE 2025 | MATRIZ ACTUALIZADA DICIEMBRE 2025.xlsx | 2026-01-13 01:37:43 | - | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 307 / 147 / 54 |
| ENERO 2025 | MATRIZ PARA USO DE CONTABILIDAD  01-2025.xlsx | 2026-01-13 01:37:43 | - | RELACION CHEQUES EMITIDOS | RELACION ORDENES DE COMPRAS | INGRESOS SEGUN ORIGEN | 143 / 109 / 44 |
| FEBRERO 2025 | MATRIZ ACTUALIZADA 02-2025.xlsx | 2026-01-13 01:37:43 | - | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 185 / 138 / 53 |
| JULIO 2025 | MATRIZ ACTUALIZADA 07-2025.xlsx | 2026-01-13 01:37:43 | MATRIZ ACTUALIZADA 07-20251.xlsx | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 214 / 191 / 66 |
| JUNIO 2025 | MATRIZ ACTUALIZADA 06-2025.xlsx | 2026-01-13 01:37:43 | - | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 171 / 150 / 50 |
| MARZO 2025 | MATRIZ ACTUALIZADA 03-2025.xlsx | 2026-01-13 01:37:43 | - | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 226 / 144 / 63 |
| MAYO 2025 | MATRIZ ACTUALIZADA 05-2025.xlsx | 2026-01-13 01:37:43 | - | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 163 / 169 / 56 |
| NOVIEMBRE 2025 | MATRIZ ACTUALIZADA NOVIEMBRE 2025.xlsx | 2026-01-13 01:37:43 | MATRIZ ACTUALIZADA NOVIEMBRE 20251.xlsx | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 135 / 147 / 63 |
| OCTUBRE 2025 | MATRIZ ACTUALIZADA NOVIEMBRE 2025.xlsx | 2026-01-13 01:37:43 | - | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 135 / 147 / 63 |
| SEPTIEMBRE 2025 | MATRIZ ACTUALIZADA SEPTIEMBRE 2025.xlsx | 2026-01-13 01:37:43 | MATRIZ DE DEUDA MES DE SEPTIEMBRE 2025.xlsx | RELACION DE PAGOS EMITIDOS | RELACION ORDENES DE COMPRAS | INGR SEGUN ORIGEN | 169 / 169 / 59 |

**Notas clave:**
- En OCTUBRE 2025 el archivo seleccionado se llama “MATRIZ ACTUALIZADA NOVIEMBRE 2025.xlsx”; se recomienda confirmar si el archivo es correcto antes de migrar.
- La hoja de ingresos suele llamarse “INGR SEGUN ORIGEN” (sin “ESO”); el proceso de detección ya contempla variantes.

## 2) Perfilado de datos (enero y diciembre 2025)

### ENERO 2025

**Pagos emitidos (RELACION CHEQUES EMITIDOS)**
- Fila encabezado: 9.
- Columnas principales detectadas: FECHA, LIBRAMIENTO, TRANSFERENCIA, CHEQUE, BENEFICIARIO, NO.FACTURAS, VALOR DE FACTURA, VALOR PAGADO.
- Filas con datos: 69.
- Duplicados exactos: 0.
- Observaciones de calidad:
  - FECHA aparece como serial Excel (numérico) y texto.
  - CHEQUE y LIBRAMIENTO tienen alta proporción de nulos: hay pagos por transferencia y por libramiento (regla de validación requerida).
  - Concepto/No.Facturas contiene texto libre con referencias a órdenes de compra.

**Órdenes de compra (RELACION ORDENES DE COMPRAS)**
- Fila encabezado: 4.
- Columnas principales detectadas: Referencia del Proceso, Proceso de Compra, Modalidad, Monto, Estado del Procedimiento, Empresa Adjudicada, Fecha de Publicación.
- Filas con datos: 105.
- Duplicados exactos: 0.
- Observaciones:
  - Monto en formato numérico (sin separador de miles).
  - La hoja es un extracto de procesos de compra (más tipo “portal compras”), por lo que no siempre hay número OC en formato interno.

**Ingresos según origen (INGRESOS SEGUN ORIGEN)**
- Fila encabezado: 8.
- Columnas detectadas: origen, ARS, Fecha deposito, No. Documento referencia, valor de Transferencia o Cheque.
- Filas con datos: 39.
- Duplicados exactos: 0.
- Observaciones:
  - Origen presenta agrupaciones y subtotales; se deben filtrar filas “Sub-Total”.
  - Fechas en serial Excel.

### DICIEMBRE 2025

**Pagos emitidos (RELACION DE PAGOS EMITIDOS)**
- Fila encabezado: 10.
- Columnas principales detectadas: FECHA, LIBRAMIENTO, TRANSFERENCIA, CHEQUE, BENEFICIARIO, NO.FACTURAS, VALOR DE FACTURA, VALOR PAGADO.
- Filas con datos: 297.
- Duplicados exactos: 0.
- Observaciones:
  - Mucho volumen de pagos; se requiere chunking.
  - VALOR PAGADO con huecos: hay filas sin importe pagado (usar VALOR DE FACTURA como fallback si aplica).

**Órdenes de compra (RELACION ORDENES DE COMPRAS)**
- Fila encabezado: 9.
- Columnas detectadas: Fecha, # Orden de Compra o Servicios, # de Expediente, Beneficiario, Rubro, Valor, Status del proceso.
- Filas con datos: 97.
- Duplicados exactos: 0.
- Observaciones:
  - Existen columnas administrativas de proceso (expediente), útiles para referencias.

**Ingresos según origen (INGR SEGUN ORIGEN)**
- Fila encabezado: 8.
- Columnas detectadas: origen, ARS, Fecha deposito, No. Documento referencia, valor de Transferencia o Cheque.
- Filas con datos: 39.
- Duplicados exactos: 0.
- Observaciones:
  - La columna “ARS” parece indicar sub-origen (Fondo 100/otros).

## 3) Inspección de schema real (ardanaccount)

### Tablas candidatas en producción
- **Pagos emitidos** → `payments` (pagos generales, relación con `venders`, `bank_accounts`). 
- **Órdenes de compra** → `bills` + `bill_products` (documento de compra y detalle/importe).
- **Ingresos** → `revenues` (relación con `customers`, `bank_accounts`).
- **Suplidores/beneficiarios** → `venders`.
- **Bancos/cuentas** → `bank_accounts`.

### Campos obligatorios / restricciones relevantes
- `payments`: requiere `date`, `amount`, `description`; `account_id`, `vender_id` y `payment_method` son opcionales.
- `bills`: requiere `vender_id`, `bill_date`, `due_date`, `category_id`, `bill_id`, `order_number`.
- `bill_products`: requiere `bill_id`, `product_id`, `price`, `quantity`.
- `revenues`: requiere `date`, `amount`, `account_id`, `customer_id`, `category_id`, `payment_method`.

### Decisión contable (asientos/journal)
Se propone migrar **documentos operativos** (pagos/OC/ingresos) en sus tablas nativas y permitir que el sistema genere la contabilidad derivada (si aplica). Es el enfoque menos riesgoso y más consistente con el funcionamiento actual (no se insertan asientos manuales).

## 4) Mapeo Excel → BD (tablas)

### A) Pagos emitidos

| Columna Excel | Campo BD | Transformación | Obligatorio | Validaciones | Default |
| --- | --- | --- | --- | --- | --- |
| FECHA | payments.date | Excel serial/texto → YYYY-MM-DD | Sí | fecha válida | - |
| LIBRAMIENTO / CHEQUE / TRANSFERENCIA | payments.reference | elegir primero disponible | Sí | si no existe ninguno → ERROR | - |
| BENEFICIARIO/SUPLIDOR | payments.vender_id | match por nombre → crear si no existe | Sí | nombre no vacío | crear vendor |
| VALOR PAGADO / MONTO / VALOR FACTURA | payments.amount | normalizar decimal | Sí | monto > 0 | - |
| BANCO/CTA | payments.account_id | match bank_accounts por nombre | No | si no existe usar default | HISTORICO_BANK_ACCOUNT_ID |
| MÉTODO (por presencia de cheque/transferencia/libramiento) | payments.payment_method | map texto → int | No | - | 0 |
| NO.FACTURAS / CONCEPTO | payments.description | texto limpio | No | - | '' |
| #OC dentro de concepto | payments.description | extraer con regex (opcional) | No | - | - |

### B) Órdenes de compra

| Columna Excel | Campo BD | Transformación | Obligatorio | Validaciones | Default |
| --- | --- | --- | --- | --- | --- |
| Fecha | bills.bill_date / bills.due_date | Excel serial/texto → YYYY-MM-DD | Sí | fecha válida | - |
| # Orden de Compra o Servicios | bills.order_number / bills.bill_id | texto | Sí | no vacío | - |
| Beneficiario/Empresa adjudicada | bills.vender_id | match/create | Sí | nombre no vacío | crear vendor |
| Valor/Monto | bill_products.price | decimal | Sí | monto > 0 | - |
| Proceso de compra/Detalle | bill_products.description | texto limpio | No | - | “Migración OC” |
| Status | bills.status (no se usa) | No | - | - | 0 |
| Rubro / Expediente | referencia en description | No | - | - | - |

### C) Ingresos según origen

| Columna Excel | Campo BD | Transformación | Obligatorio | Validaciones | Default |
| --- | --- | --- | --- | --- | --- |
| Fecha deposito | revenues.date | Excel serial/texto → YYYY-MM-DD | Sí | fecha válida | - |
| valor de Transferencia o Cheque | revenues.amount | decimal | Sí | monto > 0 | - |
| origen / ARS | revenues.description | normalizar a ARS / Gobierno Central / Otros | Sí | origen no vacío | OTROS |
| No. Documento referencia | revenues.reference | texto | No | - | - |
| Banco/CTA | revenues.account_id | match bank_accounts por nombre | Sí | si no existe usar default | HISTORICO_BANK_ACCOUNT_ID |
| Cliente | revenues.customer_id | crear “HISTORICO INGRESOS” | Sí | - | config |

## 5) Diseño de staging + transformación + carga

### Staging (carga cruda)
Se crean tres tablas de staging con campos mínimos + normalizados:
- `staging_pagos_emitidos`
- `staging_ordenes_compra`
- `staging_ingresos_origen`

Campos comunes: `source_month_folder`, `source_file`, `source_sheet`, `source_row_number`, `raw_json`, `hash`, `status`, `error_message`, timestamps.

**Hash estable (idempotencia):**
- Pagos: `sha1(mes|fecha|referencia|suplidor|monto|metodo)`
- OC: `sha1(mes|fecha|numero_oc|suplidor|monto)`
- Ingresos: `sha1(mes|fecha|origen|referencia|monto)`

### Validación/Normalización
- Fechas: Excel serial → YYYY-MM-DD.
- Montos: limpiar símbolos, comas, espacios → decimal(18,2).
- Textos: trim + normalización básica.
- Errores:
  - Pagos: sin cheque/transferencia/libramiento → ERROR.
  - OC: sin número OC o monto → ERROR.
  - Ingresos: sin origen o monto → ERROR.

### Carga productiva
- Se crea un batch por mes en `migration_batches`.
- Cada registro importado guarda `migration_batch_id`, `staging_id` y `source_hash` (payments, bills, bill_products, revenues).
- Rollback: elimina solo registros con ese batch.

## 6) CLI y ejecución por lotes

### Guía paso a paso (principiantes): migración mes a mes

> **Objetivo:** ejecutar un mes a la vez, con dry-run primero, revisar errores y luego cargar a producción.

#### Paso 0) Preparación (solo una vez)
1. **Confirma las migraciones DB** (staging y trazabilidad) en un entorno controlado:
   ```bash
   php artisan migrate
   ```
2. **Configura valores por defecto** (opcional) en `.env`:
   - `HISTORICO_CREATED_BY=1`
   - `HISTORICO_CATEGORY_ID=1`
   - `HISTORICO_PRODUCT_ID=1`
   - `HISTORICO_CUSTOMER_NAME="HISTORICO INGRESOS"`
   - `HISTORICO_CUSTOMER_EMAIL="historico.ingresos@example.com"`
   - `HISTORICO_BANK_ACCOUNT_ID=0`

> Si no tienes valores claros, usa los defaults y ajusta después de validar un mes pequeño.

#### Paso 1) Verifica el archivo del mes
1. Abre la carpeta `HistoricoContable/<MES>` y confirma que exista un archivo que empiece con `MATRIZ`.
2. Si hay **más de un archivo**, el proceso selecciona el más reciente. Revisa la tabla de inventario (sección 1) para confirmar.

#### Paso 2) Ejecuta DRY-RUN del mes (staging + validación)
Ejemplo para enero 2025:
```bash
php artisan migrate:historico --month="ENERO 2025" --dry-run
```
También puedes usar formato numérico:
```bash
php artisan migrate:historico --month="01-2025" --dry-run
```

**¿Qué hace este paso?**
- Lee el Excel por chunks (sin agotar memoria).
- Inserta en tablas de staging con `status=NEW`.
- Valida registros y marca errores en staging.
- Genera CSV de errores si aplica.

#### Paso 3) Revisa errores de staging
1. Verifica los archivos de error:
   ```
   storage/app/historico_contable/errores_<MES>_<tipo>.csv
   ```
2. Corrige el Excel si hay errores graves (ej. falta de referencias críticas).
3. Repite el dry-run si se realizaron cambios.

#### Paso 4) Ejecuta la carga real del mes
Cuando el dry-run esté limpio o aceptable:
```bash
php artisan migrate:historico --month="ENERO 2025"
```

#### Paso 4.1) Genera los asientos contables del mes
```bash
php artisan migrate:historico:asientos --month="ENERO 2025"
```

#### Paso 5) Verifica el resultado del batch
1. Revisa el log del mes:
   ```
   storage/logs/historico_contable_ENERO_2025.log
   ```
2. En DB, confirma que el batch tenga `status=SUCCESS`:
   ```sql
   SELECT * FROM migration_batches WHERE month_folder = 'ENERO 2025' ORDER BY id DESC LIMIT 1;
   ```

#### Paso 6) Rollback (si algo salió mal)
```bash
php artisan migrate:historico:rollback --month="ENERO 2025"
```
Esto elimina **solo** lo insertado por ese batch.

#### Paso 7) Repetir mes a mes
Repite el flujo **DRY-RUN → revisión → carga real** para cada mes.

---

### Comandos de referencia (Laravel Artisan)

```bash
php artisan migrate:historico --month="01-2025" --dry-run
php artisan migrate:historico --month="ENERO 2025"
php artisan migrate:historico --all
php artisan migrate:historico:rollback --month="01-2025"
```

Opciones clave:
- `--chunk=500`: procesa por chunks.
- `--created-by=1`, `--category-id=1`, `--product-id=1`.

Logs y reportes:
- Logs por mes: `storage/logs/historico_contable_<MES>.log`
- Errores por mes: `storage/app/historico_contable/errores_<MES>_<tipo>.csv`

## 7) QA / Checklist por mes

### Pre-carga
1. Confirmar archivo MATRIZ del mes.
2. Confirmar detección de hojas (pagos/OC/ingresos).
3. Reportar #filas por hoja.
4. Calcular total montos por hoja y guardar baseline.

### Staging
5. Ejecutar dry-run: staging con status NEW.
6. Verificar `source_row_number` y `raw_json`.
7. Validar duplicados por hash (sin duplicados en staging).

### Validación
8. Ejecutar validación y medir % errores.
9. Revisar manualmente 10 errores.
10. Validar conversión de fechas (10 filas aleatorias).
11. Validar montos (10 filas aleatorias).

### Carga productiva
12. Ejecutar import real (sin dry-run).
13. Confirmar batch status SUCCESS.
14. Verificar conteos (pagos/OC/ingresos).
15. Reconciliar totales (BD vs Excel menos errores).

### Validación en UI
16. Abrir 5 pagos importados.
17. Abrir 5 OC importadas.
18. Abrir 5 ingresos importados.
19. Ejecutar reportes contables y confirmar históricos.

### Auditoría y reversión
20. Rollback en entorno de prueba.
21. Reimportar el mismo mes para confirmar idempotencia.

## 8) Riesgos y mitigaciones

| Riesgo | Mitigación |
| --- | --- |
| Archivo incorrecto en carpeta (ej. OCTUBRE con archivo de NOVIEMBRE) | Validación manual previa + checksum + aprobación |
| Fechas serial Excel | Conversión robusta en servicio |
| Montos con formatos inconsistentes | Normalización y validación, fallback |
| OC sin número interno | Log y status ERROR, revisión manual |
| Ingresos con subtotales | Filtrar filas con “Sub-Total” en validación |

## 9) Diccionario contable (asientos históricos)

### Objetivo
Generar **asientos contables** (journal entries + transaction_lines) desde los documentos migrados para que los reportes financieros (Balance, Estado de Resultados, Trial Balance, Flujo de Efectivo) reflejen el histórico.

### Tabla de reglas
Se agregó la tabla `historico_accounting_rules` para mapear conceptos/origen a cuentas contables (por código):

| Columna | Descripción |
| --- | --- |
| document_type | `payment`, `bill`, `income` |
| match_field | `concepto`, `detalle`, `origen` |
| match_type | `contains`, `exact`, `regex` |
| match_value | texto/regex a comparar |
| debit_account_code | cuenta DEBE (código contable) |
| credit_account_code | cuenta HABER (código contable) |

### Reglas por defecto (config/historico_contable.php)
- **Ingresos ARS** → Debe: 110102 (Bancos) / Haber: 410201 (Contribuciones Sociales)
- **Ingresos Gobierno** → Debe: 110102 / Haber: 410401 (Transferencias Corrientes)
- **Otros ingresos** → Debe: 110102 / Haber: 410298 (Otros ingresos no tributarios)
- **Pagos/OC sin regla** → Debe: 510102 (Bienes y Servicios) / Haber: 110102 (pagos) o 2103020001 (CxP) para OC

> Puedes ajustar estas cuentas en `.env` con las variables `HISTORICO_*_ACCOUNT_CODE`.

### Comando para generar asientos
Después de importar documentos:

```bash
php artisan migrate:historico:asientos --month="ENERO 2025"
```

Opcionalmente por batch:
```bash
php artisan migrate:historico:asientos --batch-id=123
```

### Qué se genera
- `journal_entries` y `journal_items` con trazabilidad (`migration_batch_id`, `source_type`, `source_id`, `source_hash`)
- `transaction_lines` para que los reportes financieros reflejen el histórico

## 10) Generación de asientos desde hoja `CUENTA T VS` (ejecución oficial)

Esta ejecución quedó estandarizada con las siguientes reglas fijas:

- **Hoja origen:** `CUENTA T VS`.
- **Cuenta banco (crédito por VALOR):** `1101010001`.
- **Retenciones por pagar (crédito por RETENCION):** `210306`.
- **Fecha del asiento:** se deriva del nombre del archivo Matriz (por ejemplo `01-2025` => `2025-01-31`).
- **Ubicación del archivo Matriz:** `HistoricoContable/<MES>` (ejemplo: `HistoricoContable/ENERO 2025`).

### Flujo recomendado mes a mes

1. Ejecuta la migración del mes (staging/validación/importación):

```bash
php artisan migrate:historico --month="ENERO 2025"
```

2. Genera asientos del mismo mes leyendo automáticamente la Matriz de la carpeta del mes:

```bash
php artisan migrate:historico:asientos --month="ENERO 2025"
```

> El comando localiza el último batch del mes, busca el archivo `MATRIZ*.xlsx` dentro de `HistoricoContable/<MES>`, toma la hoja `CUENTA T VS` y crea los asientos.

### Ejecución por `batch-id`

Si deseas ejecutar por lote específico:

```bash
php artisan migrate:historico:asientos --batch-id=123
```

En este modo, el comando toma `month_folder` desde `migration_batches` para ubicar el archivo Matriz del mes correspondiente.

### Verificación rápida posterior

- Revisar en la salida del comando el campo `cuenta_t_vs` (cantidad de asientos creados).
- Validar en `journal_entries`, `journal_items` y `transaction_lines` por `migration_batch_id`.
- Confirmar cuadratura: suma débitos = suma créditos por asiento.
