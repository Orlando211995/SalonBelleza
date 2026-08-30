# Configuración de Email.js para Formulario de Contacto

## Paso 1: Crear cuenta en Email.js

1. Ve a https://www.emailjs.com
2. Haz clic en **Sign Up** (arriba a la derecha)
3. Registrate con Google, GitHub o correo
4. Confirma tu dirección de correo

## Paso 2: Conectar tu servicio de correo (Gmail recomendado)

1. En el dashboard, ve a **Email Services** (izquierda)
2. Haz clic en **Add Service**
3. Selecciona **Gmail**
4. Haz clic en **Connect with Gmail**
5. Autoriza el acceso
6. Haz clic en **Create Service**
7. Copia el **Service ID** (algo como `service_abc123xyz`)

## Paso 3: Crear una plantilla de correo

1. Ve a **Email Templates** (izquierda)
2. Haz clic en **Create New Template**
3. Rellena así:

```
Template Name: contacto_formulario

Email to: {{to_email}}
Subject: Nuevo mensaje de contacto - {{subject}}

Content (Cuerpo):
---

Nombre: {{from_name}}
Teléfono: {{phone}}
Correo: {{from_email}}
Asunto: {{subject}}

Mensaje:
{{message}}

---
```

4. Haz clic en **Save** abajo
5. Copia el **Template ID** (algo como `template_abc123xyz`)

## Paso 4: Obtener la Public Key

1. Ve a **Account** (izquierda) → **API Keys**
2. Copia la **Public Key** (algo como `abc123xyz_def456`)

## Paso 5: Actualizar el código en contacto.php

Abre `SalonBelleza/contacto.php` y busca estas líneas al final del archivo:

```javascript
const EMAILJS_PUBLIC_KEY = 'TU_PUBLIC_KEY_AQUI';
const EMAILJS_SERVICE_ID = 'TU_SERVICE_ID_AQUI';
const EMAILJS_TEMPLATE_ID = 'TU_TEMPLATE_ID_AQUI';
```

Reemplaza con tus credenciales:

```javascript
const EMAILJS_PUBLIC_KEY = 'abc123xyz_def456'; // Tu Public Key
const EMAILJS_SERVICE_ID = 'service_abc123xyz'; // Tu Service ID
const EMAILJS_TEMPLATE_ID = 'template_abc123xyz'; // Tu Template ID
```

Y también encuentra esta línea:

```javascript
to_email: 'tututuseñoriodelcorreo@gmail.com', // Aquí va tu correo para recibir
```

Y reemplaza con tu correo real:

```javascript
to_email: 'tucorreo@gmail.com',
```

## Paso 6: Probar

1. Ve a http://127.0.0.1:8000/SalonBelleza/contacto.php
2. Completa el formulario
3. Haz clic en "Enviar mensaje"
4. Revisa tu bandeja de entrada (puede tardar unos segundos)

## Límites de Email.js (Plan Gratis)

- 200 emails por mes
- Perfecto para un sitio pequeño
- Si necesitas más, puedes mejorar a plan de pago

## Solución de problemas

### "Error de cors" o "Credenciales inválidas"
- Verifica que copiaste correctamente Public Key, Service ID y Template ID
- No uses espacios en blanco

### El email no llega
- Revisa spam/correo no deseado
- En Email.js, ve a **Email Services** → tu servicio → revisa logs

### "Email service unavailable"
- El servicio está caído (raro)
- Intenta en 5 minutos

## Nota sobre seguridad

La Public Key se ve en el código fuente (es pública), pero Email.js la protege adecuadamente.
Si quieres máxima seguridad, puedes hacer un endpoint PHP que envíe por servidor (más complejo).

---

¿Necesitas ayuda? Pregúntame cualquier cosa.
