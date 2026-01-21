# Migración histórico contable + libro banco

## Tablas reales (core) usadas

**Documentos históricos**
- **Pagos emitidos:** `payments` (staging: `staging_pagos_emitidos`).
- **Órdenes de compra (OC):** `bills` + `bill_products` (staging: `staging_ordenes_compra`).
- **Ingresos según origen:** `revenues` (staging: `staging_ingresos_origen`).

**Banco / conciliación bancaria**
- **Movimientos bancarios:** `movimientos_bancarios` (core) alimenta conciliación bancaria.
- **Cuentas recaudadoras:** `cuentas_recaudadoras`.
- **Staging banco:** `staging_libro_banco` (auxiliar para migración).

## Descubrimiento automático de archivos (por mes)

**Heurística**
- Se escanean archivos `*.xls|*.xlsx|*.xlsm` en cada carpeta mensual bajo `./HistoricoContable/`.
- El candidato debe:
  - Contener en el nombre alguno de: `Concili`, `Conciliacion`, `Banco`, `Libro`.
  - Tener una hoja llamada exactamente `LIBRO BANCO`.
- Si hay múltiples candidatos, se elige el más reciente por `modified_time` y se reportan los demás.

**Matriz principal**
- Se detecta por nombre que inicia con `MATRIZ` (case-insensitive).

## Perfilado de “LIBRO BANCO” (ejemplo Enero 2025)

**Archivo:** `HistoricoContable/ENERO 2025/Conciliacion Mes de Enero 2025.xls`

- **Fila de encabezados detectada:** fila 6.
- **Columnas reales:** `FECHA`, `CHEQUE/TRANS`, `DESCRIPCION`, `DEBITO`, `CREDITO`, `BALANCE`.
- **Tipos inferidos:**
  - `FECHA`: fecha (serial Excel o texto `dd/mm/yyyy`).
  - `CHEQUE/TRANS`: texto/numérico (referencia).
  - `DESCRIPCION`: texto.
  - `DEBITO`, `CREDITO`, `BALANCE`: numérico con separador de miles.
- **Calidad (enero 2025):**
  - Filas con fecha inválida: 10 (ej. filas de totales/texto).
  - Montos no numéricos: 9 (incluye valores `-`).
  - Débito y crédito simultáneos: 0.
- **Totales (enero 2025):**
  - Total débitos: 4,995,295.98
  - Total créditos: 6,539,617.65
  - Saldo inicial: 2,244,994.14
  - Saldo final: 500,388.24

## Mapeo “LIBRO BANCO” → schema real

**Destino:** `movimientos_bancarios`
- `cuenta_recaudadora_id`: desde `historico_contable.defaults.cuenta_recaudadora_id`.
- `fecha`: `txn_date`.
- `monto`: `debit` si > 0, en caso contrario `credit`.
- `descripcion`: `description`.
- `referencia`: `reference`.
- `origen_archivo`: nombre del archivo fuente.
- `estado_conciliacion`: `pendiente`.
- `migration_batch_id`, `staging_id`, `source_hash`: trazabilidad y rollback.

## Staging “LIBRO BANCO” (staging_libro_banco)

Campos principales:
- `migration_batch_id`, `source_month_folder`, `source_file`, `source_sheet`, `source_row_number`, `raw_json`, `hash`, `status`, `error_message`.
- `txn_date`, `description`, `reference`, `debit`, `credit`, `balance`, `cuenta_recaudadora_id`.

**Hash sugerido**
```
sha1(month|txn_date|reference|debit|credit|balance|description_normalized|cuenta_recaudadora_id)
```

## Reglas de validación / normalización

- **Fechas:** soporta serial Excel y formatos `dd/mm/yyyy`.
- **Montos:** limpia símbolos (`$`, `RD$`), separadores de miles y negativos con paréntesis.
- **Descripción:** trim + normalización de espacios.
- **Referencia:** extrae número embebido (ej. `CHEQUE 001234`).

**Errores (status = ERROR):**
- fecha nula
- débito y crédito ambos > 0
- débito y crédito ambos = 0
- monto inválido (si el valor no parsea)

## Carga final del libro banco

- Inserción idempotente en `movimientos_bancarios` por `source_hash`.
- Se respeta `migration_batch_id` para rollback por mes.

## Diccionario de reglas contables (clasificación)

**Tabla recomendada:** `contable_rules_map`

Campos mínimos:
- `document_type` (`payment|oc|income`)
- `match_field` (`origen|concepto|rubro|suplidor|regex_text`)
- `match_value` (texto o regex)
- `debit_account_id`, `credit_account_id`
- `priority`, `description`

**Reglas ejemplo (lineamientos):**
- Ingresos ARS / Gobierno / Otros:
  - **Debe:** Banco
  - **Haber:** Ingresos (ARS / Gobierno / Otros)
- Pagos:
  - **Debe:** Gasto o CxP
  - **Haber:** Banco
- OC (devengo):
  - **Debe:** Gasto
  - **Haber:** CxP Proveedores

## Estrategia de asientos históricos (por mes)

**Recomendación**
1. OC → Devengo contra CxP (si el detalle lo permite).
2. Pago → Aplica a CxP si se enlaza, o gasto directo si no.

**Proceso**
- Seleccionar `migration_batch_id` (mes).
- Extraer documentos del mes.
- Aplicar `contable_rules_map`.
- Generar `journal_entries` + `journal_items`.
- Guardar `batch_id`, documento origen y `hash` para idempotencia.
- Rollback por batch: eliminar asientos del batch.

## CLI / Comandos

**Migración documentos (existente):**
- `php artisan migrate:historico --month=01-2025`
- `php artisan migrate:historico --all --dry-run`

**Nuevo libro banco:**
- `php artisan migrate:libro-banco --month=01-2025`
- `php artisan migrate:libro-banco --all --dry-run`

**Rollback:**
- `php artisan migrate:rollback --month=01-2025`

## Checklist QA

### Feature A: Carga “LIBRO BANCO”
1. Detecta archivo correcto por mes y sheet “LIBRO BANCO”.
2. `staging_libro_banco` se llena con `source_row_number` y `raw_json`.
3. Se identifican y reportan errores (fecha nula, montos inválidos).
4. Idempotencia: re-ejecutar no duplica (`hash` único).
5. Totales:
   - `SUM(debit)` y `SUM(credit)` coinciden con el Excel (menos filas en ERROR).
6. Si existe balance:
   - balance final coincide con Excel.
7. UI/Reporte:
   - conciliación bancaria muestra movimientos del libro banco importados.

### Feature B: Diccionario de reglas contables
1. Insertar 5 reglas de ejemplo (ARS/Gobierno/Otros, medicamentos, servicios).
2. Probar match exacto y match regex.
3. Verificar prioridad de reglas (si hay múltiples matches).

### Feature C: Generación de asientos por mes (payments/oc/income)
1. Ejecutar dry-run y confirmar conteos estimados.
2. Ejecutar real y confirmar:
   - Trial Balance: Debe = Haber.
3. Totales por mes:
   - Total ingresos (documentos) = total crédito a cuentas de ingresos.
   - Total pagos (documentos) = total crédito a banco/caja.
4. Si OC devenga:
   - Total OC devengadas = total crédito a CxP.
   - Pagos aplicados reducen CxP.
5. Muestreo:
   - Seleccionar 10 documentos aleatorios:
     documento → asiento → cuentas correctas.

### Feature D: Conciliación bancaria contra libro banco
1. Reglas de matching:
   - Por referencia (cheque/transferencia/deposito)
   - Por fecha ± 2 días y monto exacto (fallback)
2. Generar 3 estados:
   - Matched
   - Unmatched en libro banco (movimiento sin documento)
   - Unmatched en documentos (documento sin movimiento bancario)
3. Validación:
   - Para el mes, #matched y totales matched deben ser razonables.
4. Export:
   - Exportar conciliación (matched/unmatched) a Excel.
