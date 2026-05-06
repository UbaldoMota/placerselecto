# Guía de Integración — SMS SaaS API

> **Para Claude (u otro asistente):** este documento es un playbook accionable. Si estás integrando envío de SMS en una aplicación del usuario, lee primero la sección 1 (contexto) y la sección 4 (patrón de implementación). El resto es referencia.

---

## 1. Contexto en 30 segundos

El usuario tiene una **plataforma SMS SaaS propia** desplegada en `https://test.alitter-soluciones.com` que él mismo administra y consume desde sus otras aplicaciones. La plataforma:

- Recibe peticiones REST con una **API Key Bearer** por aplicación cliente
- Envía el SMS a través de **Twilio** (cuenta trial al momento de escribir esto)
- Registra cada mensaje en SQL Server con costo, segmentos, estado y referencia
- Expone dashboard administrativo en la raíz del dominio para gestionar apps, keys, métricas

**No hay terceros consumiendo esta API.** El usuario es el único cliente; cada una de sus aplicaciones (CRM, ERP, sistemas de pedidos, etc.) es una "Aplicación" registrada con su propia API Key.

**Implicación práctica:**
- El usuario crea una nueva API Key cuando lo necesita (no hay onboarding de clientes)
- El backend que llame esta API debe tratar la key como un secreto cualquiera (env var)
- No hace falta CORS, no hace falta captcha, no hace falta endpoints sin auth

---

## 2. Datos del servicio

| Dato | Valor |
|------|-------|
| Base URL (test/prod actual) | `https://test.alitter-soluciones.com` |
| Endpoint principal | `POST /api/sms/enviar` |
| Esquema de auth | Bearer token (API Key) |
| Formato de la key | `sms_{prefix8}_{secret}` |
| Health endpoints | `GET /api/health` (rápido), `GET /api/health/full` (BD + Twilio) |
| Dashboard admin | `https://test.alitter-soluciones.com/` |
| Rate limit por aplicación | 60 req/min |
| Idempotency window | 5 minutos |
| Tamaño máximo de mensaje | 1600 caracteres |

**⚠ Restricción crítica de la cuenta Twilio (trial):** solo se puede enviar al número **`+525618160217`**. Cualquier otro destino devuelve error 21608. Antes de probar con un número distinto hay que verificarlo en console.twilio.com o cambiar a cuenta Twilio paga.

---

## 3. Cómo crear una nueva API Key

Cuando el usuario quiera integrar una **nueva aplicación**:

1. Login en `https://test.alitter-soluciones.com/` con credenciales admin
2. Menú → **Aplicaciones** → **Nueva**
3. Nombrarla con algo identificable (ej: `CRM Producción`, `App Pedidos`, `Landing Marketing`)
4. Generar API Key — **se muestra una sola vez**, copiarla a un `.env` del proyecto cliente inmediatamente
5. Si se pierde: revocarla y generar una nueva. No es recuperable.

**Regla:** una API Key distinta por cada aplicación del usuario. Nunca compartir. Esto permite revocar individualmente, ver consumo segmentado y poner límites distintos por proyecto.

---

## 4. Patrón de implementación recomendado

Cuando integres SMS en una app del usuario, sigue este flujo exacto:

### 4.1 Configuración

Agregar a `.env` (o equivalente) del proyecto cliente:

```
SMS_BASE_URL=https://test.alitter-soluciones.com
SMS_API_KEY=sms_XxXxXxXx_XxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXx
```

**Nunca** poner la key en código fuente, frontend, apps móviles compiladas o repos públicos.

### 4.2 Cliente reutilizable (no llamadas inline)

Crear **un solo cliente** centralizado en el proyecto (ej: `SmsClient.cs`, `sms.ts`, `sms_client.py`). Razones:
- Un solo lugar para rotar la key o cambiar la URL
- Aplicar Idempotency-Key, retries y logging consistentemente
- Tipar las respuestas una vez

### 4.3 Llamada desde lógica de negocio

```
await sms.enviarAsync(numero, mensaje, referencia: "orden-1234");
```

Sin más. Toda la complejidad (auth, retries, logging) vive dentro del cliente.

### 4.4 Reglas obligatorias del cliente

1. **Header `Authorization: Bearer <key>`** — desde env var
2. **Header `Content-Type: application/json`**
3. **Header `Idempotency-Key: <uuid v4>`** generado por intento lógico (no por retry HTTP)
4. **Timeout HTTP** de 30 segundos
5. **Retry** solo en `429`, `500`, `502`, `503`, `504` y errores de red. Backoff exponencial 1s → 2s → 4s, máximo 3 intentos
6. **No retry** en `400`, `401`, `403`, `422`
7. **Logging:** registrar `mensajeId`, `requestId`, `sidProveedor`, `costoTotal` por cada envío exitoso. Registrar `requestId` y `errorCode` por cada fallo
8. **Validar formato E.164** antes de llamar (`^\+[1-9]\d{6,14}$`)

---

## 5. Endpoint: `POST /api/sms/enviar`

**Headers:**
```
Authorization: Bearer sms_AbCd1234_xyz...
Content-Type: application/json
Idempotency-Key: 7f3e2c1a-9d8b-4e6f-a5c7-1b2d3e4f5a6b
```

**Body:**
```json
{
  "destino": "+525618160217",
  "mensaje": "Tu código es 123456",
  "referencia": "login-otp-u789"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `destino` | string | Sí | Formato E.164 (`+` + país + número, sin espacios) |
| `mensaje` | string | Sí | Texto del SMS, máximo 1600 caracteres |
| `referencia` | string | No | ID interno tuyo (orden, ticket, userId). Útil para correlacionar en logs |

**Respuesta 200 OK:**
```json
{
  "success": true,
  "message": "SMS enviado correctamente.",
  "data": {
    "mensajeId": 1001,
    "estado": "Enviado",
    "sidProveedor": "SMb5a00dc7c8d81002c7b9e3e4a539f926",
    "destino": "+525618160217",
    "segmentos": 2,
    "costoTotal": 0.090000,
    "moneda": "USD",
    "fechaEnvio": "2026-05-01T19:46:30Z"
  },
  "requestId": "715660ab01ef4300b7dd1a577a08a0f4"
}
```

**Respuesta de error:**
```json
{
  "success": false,
  "message": "Número de destino inválido (formato incorrecto)",
  "errorCode": "21211",
  "requestId": "715660ab01ef4300b7dd1a577a08a0f4"
}
```

---

## 6. Códigos HTTP y errores

| HTTP | Significado | Acción |
|------|-------------|--------|
| 200 | OK | Continuar |
| 400 | Datos inválidos (E.164, mensaje vacío, etc.) | NO retry. Loggear y propagar |
| 401 | API Key faltante o inválida | NO retry. Revisar env var |
| 403 | Aplicación inactiva o key revocada | NO retry. Avisar al usuario |
| 422 | Límite diario/mensual excedido | NO retry. Esperar a mañana o subir el límite |
| 429 | Rate limit (60 req/min por app) | Retry con backoff exponencial |
| 500/502/503/504 | Error de servidor | Retry con backoff |

**Códigos de proveedor (Twilio) más frecuentes:**

| `errorCode` | Causa |
|-------------|-------|
| `21211` | Número destino inválido |
| `21408` | Permiso denegado para esa región |
| `21610` | Destinatario optó por STOP |
| `21614` | Número móvil inválido |
| **`21608`** | **Número no verificado en cuenta trial Twilio** (caso muy frecuente en este servicio) |
| `30003-30008` | Errores de operador (apagado, bloqueado, filtrado) |
| `20429` | Rate limit de Twilio (no de la API) |

---

## 7. Implementaciones listas para copiar

### 7.1 C# / .NET (stack principal del usuario)

**`Sms/SmsClient.cs`:**
```csharp
using System.Net.Http.Headers;
using System.Net.Http.Json;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;

public class SmsOptions
{
    public string BaseUrl { get; set; } = "";
    public string ApiKey { get; set; } = "";
}

public record SmsResult(
    long MensajeId, string Estado, string SidProveedor, string Destino,
    int Segmentos, decimal CostoTotal, string Moneda, DateTime FechaEnvio);

public class SmsException : Exception
{
    public string? ErrorCode { get; }
    public string? RequestId { get; }
    public int HttpStatus { get; }
    public SmsException(int status, string message, string? code, string? reqId)
        : base(message) { HttpStatus = status; ErrorCode = code; RequestId = reqId; }
}

public class SmsClient
{
    private readonly HttpClient _http;
    private readonly ILogger<SmsClient> _log;
    private readonly string _apiKey;

    public SmsClient(HttpClient http, IOptions<SmsOptions> opts, ILogger<SmsClient> log)
    {
        _http = http;
        _log = log;
        _apiKey = opts.Value.ApiKey;
        _http.BaseAddress = new Uri(opts.Value.BaseUrl);
        _http.Timeout = TimeSpan.FromSeconds(30);
    }

    public async Task<SmsResult> EnviarAsync(string destino, string mensaje, string? referencia = null, CancellationToken ct = default)
    {
        if (!System.Text.RegularExpressions.Regex.IsMatch(destino, @"^\+[1-9]\d{6,14}$"))
            throw new ArgumentException("destino no está en formato E.164", nameof(destino));

        var idempotencyKey = Guid.NewGuid().ToString();
        var delays = new[] { 1000, 2000, 4000 };

        for (int attempt = 0; attempt <= delays.Length; attempt++)
        {
            using var req = new HttpRequestMessage(HttpMethod.Post, "api/sms/enviar")
            {
                Content = JsonContent.Create(new { destino, mensaje, referencia })
            };
            req.Headers.Authorization = new AuthenticationHeaderValue("Bearer", _apiKey);
            req.Headers.Add("Idempotency-Key", idempotencyKey);

            HttpResponseMessage resp;
            try { resp = await _http.SendAsync(req, ct); }
            catch (HttpRequestException) when (attempt < delays.Length)
            {
                await Task.Delay(delays[attempt], ct);
                continue;
            }

            var payload = await resp.Content.ReadFromJsonAsync<ApiResponse>(cancellationToken: ct);

            if (resp.IsSuccessStatusCode && payload?.Success == true)
            {
                _log.LogInformation("SMS enviado mensajeId={Id} sid={Sid} costo={Cost} ref={Ref}",
                    payload.Data!.MensajeId, payload.Data.SidProveedor, payload.Data.CostoTotal, referencia);
                return payload.Data;
            }

            bool retryable = (int)resp.StatusCode is 429 or 500 or 502 or 503 or 504;
            if (retryable && attempt < delays.Length)
            {
                await Task.Delay(delays[attempt], ct);
                continue;
            }

            throw new SmsException((int)resp.StatusCode,
                payload?.Message ?? "Error desconocido", payload?.ErrorCode, payload?.RequestId);
        }

        throw new SmsException(0, "Sin respuesta tras retries", null, null);
    }

    private record ApiResponse(bool Success, string Message, SmsResult? Data, string? ErrorCode, string? RequestId);
}
```

**Registro en `Program.cs` / `Startup.cs`:**
```csharp
builder.Services.Configure<SmsOptions>(builder.Configuration.GetSection("Sms"));
builder.Services.AddHttpClient<SmsClient>();
```

**`appsettings.json`** (en producción usar variables de entorno o secret manager):
```json
{
  "Sms": {
    "BaseUrl": "https://test.alitter-soluciones.com",
    "ApiKey": ""
  }
}
```

**Uso:**
```csharp
public class PedidoService
{
    private readonly SmsClient _sms;
    public PedidoService(SmsClient sms) { _sms = sms; }

    public async Task ConfirmarPedidoAsync(Pedido p)
    {
        // ...lógica del pedido
        try
        {
            await _sms.EnviarAsync(
                destino:    p.Cliente.Telefono,
                mensaje:    $"Tu pedido #{p.Id} fue confirmado. Total: ${p.Total}",
                referencia: $"pedido-{p.Id}");
        }
        catch (SmsException ex)
        {
            // Log y continúa — el pedido se confirmó, el SMS es secundario
            _log.LogWarning(ex, "SMS de confirmación falló para pedido {Id}", p.Id);
        }
    }
}
```

### 7.2 Node.js / TypeScript

```typescript
import { randomUUID } from 'crypto';

export interface SmsResult {
  mensajeId: number;
  estado: string;
  sidProveedor: string;
  destino: string;
  segmentos: number;
  costoTotal: number;
  moneda: string;
  fechaEnvio: string;
}

export class SmsError extends Error {
  constructor(public httpStatus: number, message: string, public errorCode?: string, public requestId?: string) {
    super(message);
  }
}

const E164 = /^\+[1-9]\d{6,14}$/;
const RETRYABLE = new Set([429, 500, 502, 503, 504]);
const DELAYS_MS = [1000, 2000, 4000];

export class SmsClient {
  constructor(private baseUrl: string, private apiKey: string) {}

  async enviar(destino: string, mensaje: string, referencia?: string): Promise<SmsResult> {
    if (!E164.test(destino)) throw new Error('destino no E.164');

    const idempotencyKey = randomUUID();

    for (let attempt = 0; attempt <= DELAYS_MS.length; attempt++) {
      let resp: Response;
      try {
        resp = await fetch(`${this.baseUrl}/api/sms/enviar`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json',
            'Idempotency-Key': idempotencyKey,
          },
          body: JSON.stringify({ destino, mensaje, referencia }),
          signal: AbortSignal.timeout(30000),
        });
      } catch (e) {
        if (attempt < DELAYS_MS.length) {
          await new Promise(r => setTimeout(r, DELAYS_MS[attempt]));
          continue;
        }
        throw new SmsError(0, `Network error: ${(e as Error).message}`);
      }

      const payload = await resp.json();

      if (resp.ok && payload.success) {
        console.info('SMS enviado', { mensajeId: payload.data.mensajeId, sid: payload.data.sidProveedor, ref: referencia });
        return payload.data;
      }

      if (RETRYABLE.has(resp.status) && attempt < DELAYS_MS.length) {
        await new Promise(r => setTimeout(r, DELAYS_MS[attempt]));
        continue;
      }

      throw new SmsError(resp.status, payload.message ?? 'error', payload.errorCode, payload.requestId);
    }

    throw new SmsError(0, 'Sin respuesta tras retries');
  }
}

// Singleton
export const sms = new SmsClient(
  process.env.SMS_BASE_URL!,
  process.env.SMS_API_KEY!,
);
```

**Uso:**
```typescript
import { sms, SmsError } from './sms';

try {
  const r = await sms.enviar('+525618160217', 'Hola', 'test-001');
  console.log('OK', r.mensajeId);
} catch (e) {
  if (e instanceof SmsError) console.error(e.httpStatus, e.errorCode, e.message);
}
```

### 7.3 Python

```python
import os
import time
import uuid
import re
import logging
import requests

E164 = re.compile(r"^\+[1-9]\d{6,14}$")
RETRYABLE = {429, 500, 502, 503, 504}
DELAYS = [1, 2, 4]
log = logging.getLogger(__name__)

class SmsError(Exception):
    def __init__(self, status, message, code=None, request_id=None):
        super().__init__(message)
        self.status, self.code, self.request_id = status, code, request_id

class SmsClient:
    def __init__(self, base_url: str, api_key: str):
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key

    def enviar(self, destino: str, mensaje: str, referencia: str | None = None) -> dict:
        if not E164.match(destino):
            raise ValueError("destino no E.164")

        idem = str(uuid.uuid4())
        headers = {
            'Authorization': f'Bearer {self.api_key}',
            'Content-Type':  'application/json',
            'Idempotency-Key': idem,
        }
        body = {'destino': destino, 'mensaje': mensaje, 'referencia': referencia}

        for attempt in range(len(DELAYS) + 1):
            try:
                r = requests.post(f'{self.base_url}/api/sms/enviar',
                                  headers=headers, json=body, timeout=30)
            except requests.RequestException as e:
                if attempt < len(DELAYS):
                    time.sleep(DELAYS[attempt]); continue
                raise SmsError(0, f'network: {e}') from e

            data = r.json()
            if r.ok and data.get('success'):
                log.info('SMS ok id=%s sid=%s ref=%s',
                         data['data']['mensajeId'], data['data']['sidProveedor'], referencia)
                return data['data']

            if r.status_code in RETRYABLE and attempt < len(DELAYS):
                time.sleep(DELAYS[attempt]); continue

            raise SmsError(r.status_code, data.get('message', 'error'),
                           data.get('errorCode'), data.get('requestId'))

        raise SmsError(0, 'sin respuesta tras retries')

# Singleton
sms = SmsClient(os.environ['SMS_BASE_URL'], os.environ['SMS_API_KEY'])
```

### 7.4 PHP

```php
<?php
class SmsException extends RuntimeException {
    public function __construct(public int $httpStatus, string $message,
                                public ?string $errorCode = null, public ?string $requestId = null) {
        parent::__construct($message);
    }
}

class SmsClient {
    private const RETRYABLE = [429, 500, 502, 503, 504];
    private const DELAYS_MS = [1000, 2000, 4000];

    public function __construct(private string $baseUrl, private string $apiKey) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function enviar(string $destino, string $mensaje, ?string $referencia = null): array {
        if (!preg_match('/^\+[1-9]\d{6,14}$/', $destino))
            throw new InvalidArgumentException('destino no E.164');

        $idem = bin2hex(random_bytes(16));
        $payload = json_encode(compact('destino', 'mensaje', 'referencia'));

        for ($attempt = 0; $attempt <= count(self::DELAYS_MS); $attempt++) {
            $ch = curl_init("{$this->baseUrl}/api/sms/enviar");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Bearer {$this->apiKey}",
                    'Content-Type: application/json',
                    "Idempotency-Key: {$idem}",
                ],
            ]);
            $body   = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err    = curl_error($ch);
            curl_close($ch);

            if ($body === false) {
                if ($attempt < count(self::DELAYS_MS)) { usleep(self::DELAYS_MS[$attempt] * 1000); continue; }
                throw new SmsException(0, "network: {$err}");
            }

            $data = json_decode($body, true);
            if ($status === 200 && ($data['success'] ?? false)) {
                error_log("SMS ok id={$data['data']['mensajeId']} ref={$referencia}");
                return $data['data'];
            }

            if (in_array($status, self::RETRYABLE, true) && $attempt < count(self::DELAYS_MS)) {
                usleep(self::DELAYS_MS[$attempt] * 1000); continue;
            }

            throw new SmsException($status, $data['message'] ?? 'error',
                                   $data['errorCode'] ?? null, $data['requestId'] ?? null);
        }
        throw new SmsException(0, 'sin respuesta tras retries');
    }
}

// Uso:
$sms = new SmsClient(getenv('SMS_BASE_URL'), getenv('SMS_API_KEY'));
$r = $sms->enviar('+525618160217', 'Hola', 'test-001');
```

### 7.5 cURL (debug rápido / Postman)

```bash
curl -X POST "https://test.alitter-soluciones.com/api/sms/enviar" \
  -H "Authorization: Bearer sms_XxXxXxXx_..." \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: $(uuidgen)" \
  -d '{"destino":"+525618160217","mensaje":"test","referencia":"curl-1"}'
```

---

## 8. Patrones comunes

### 8.1 OTP / código de verificación

```
mensaje = $"Tu código de verificación es {codigo}. Vence en 5 minutos."
referencia = $"otp-{userId}-{Guid.NewGuid()}"
```

Genera código de 6 dígitos. Guárdalo hasheado en BD con expiración. Mismo idempotencyKey si reintentas en menos de 5 min.

### 8.2 Notificación transaccional (pedido, ticket, pago)

```
mensaje = $"Pedido #{id} confirmado. Total: ${total}. Sigue tu envío en {urlCorta}"
referencia = $"pedido-{id}"
```

Hacer el envío **fuera de la transacción de BD** (post-commit). Si el SMS falla, no debe revertir el pedido — loggear el fallo y, opcionalmente, reintentarlo desde un job en background.

### 8.3 Alerta operativa al admin

```
mensaje = $"[{sistema}] {nivel}: {detalle}"
referencia = $"alerta-{sistema}-{timestamp}"
```

Usar una API Key dedicada para alertas (con rate limit más alto si hace falta).

---

## 9. Reglas que el cliente debe cumplir siempre

- [ ] API Key en variable de entorno, nunca en código fuente
- [ ] Una API Key distinta por aplicación cliente
- [ ] Validar E.164 antes de llamar
- [ ] `Idempotency-Key` único por intento lógico, mismo valor en retries HTTP
- [ ] Timeout HTTP 30s
- [ ] Retry solo en 429/5xx con backoff exponencial 1s/2s/4s
- [ ] Loggear `mensajeId`, `requestId`, `sidProveedor`, `costoTotal` en éxito; `requestId` y `errorCode` en fallo
- [ ] Tratar fallos de SMS como **no críticos** en flujos transaccionales (no revertir la operación principal)
- [ ] Llamadas siempre desde backend. Nunca exponer la key en frontend, app móvil o repo público

---

## 10. Troubleshooting

| Síntoma | Probable causa | Verificación |
|---------|----------------|--------------|
| `401 Unauthorized` | Key vacía, mal escrita, o sin prefijo `Bearer ` | Verificar header completo: `Authorization: Bearer sms_...` |
| `400` con `errorCode: 21608` | Destino no verificado en cuenta Twilio trial | Usar `+525618160217` o cambiar a Twilio paga |
| `400` con `errorCode: 21211` | Destino no es E.164 | Asegurar `+` + país, sin espacios ni guiones |
| `429` repetido | Rate limit (60/min por app) | Bajar volumen o pedir aumento del límite |
| `422` | Cupo diario/mensual alcanzado | Subir límite en dashboard o esperar |
| `500` con BD desconectada | SQL Server GoDaddy caído o credencial mala | Probar `GET /api/health/full` |
| Timeout sin respuesta | TLS, DNS, o app caída | Probar `GET /api/health` |
| SMS aceptado pero no llega al teléfono | Operador filtrando, número STOP, o cuenta Twilio sin saldo | Buscar el `sidProveedor` en console.twilio.com |

**Si necesitas reportar un problema al usuario o al admin:** incluir `requestId` y `mensajeId`.

---

## 11. Recordatorios críticos para Claude

1. **Twilio sigue en trial.** Si el usuario dice "el SMS no llegó" y el destino no es `+525618160217`, ese es el problema más probable. No empieces a debuggear código hasta confirmar.
2. **No exponer la API Key.** Si el usuario está integrando en frontend (React, Vue, Angular, app móvil), **detenerse y avisar**. Esa key debe ir en backend. Si no hay backend, plantear las opciones de la sección "endpoint público" (captcha, dominio restringido, etc.) antes de implementar.
3. **El SMS es secundario en flujos transaccionales.** Nunca dentro de una transacción de BD ni bloqueando una compra/login. Siempre con try/catch que no propague el error al flujo principal.
4. **Una key por app, siempre.** Si el usuario quiere "reusar la misma key" en dos proyectos, recordarle por qué eso es mala idea (no se puede revocar individualmente, métricas mezcladas, blast radius mayor).
5. **El stack del usuario es .NET 4.8 + SQL Server.** Probablemente la mayoría de las integraciones que pida también van a ser en C#. La implementación de la sección 7.1 debe ser tu default.
