<?php
/**
 * Khaviar Ibiza — Formulario de contacto
 * Recibe los datos del form y los envía por email al cliente.
 *
 * CONFIGURACIÓN: ajusta las dos variables de abajo antes de subir a Hostinger.
 */

// ── Destino del email ────────────────────────────────────
$EMAIL_DESTINO = 'info@khaviaribiza.com';
$EMAIL_ASUNTO  = 'Nueva consulta — Khaviar Ibiza';
// ────────────────────────────────────────────────────────

/* ── Solo aceptamos POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

/* ── Recoger y limpiar los campos ── */
function limpia($valor) {
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
}

$nombre   = limpia($_POST['nombre']   ?? '');
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$telefono = limpia($_POST['telefono'] ?? '');
$mensaje  = limpia($_POST['mensaje']  ?? '');
$rgpd     = isset($_POST['rgpd']) ? true : false;

/* ── Validaciones básicas ── */
$errores = [];

if (empty($nombre))                       $errores[] = 'El nombre es obligatorio.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
if (empty($mensaje))                      $errores[] = 'El mensaje es obligatorio.';
if (!$rgpd)                               $errores[] = 'Debes aceptar la política de privacidad.';

if (!empty($errores)) {
    header('Location: index.html?error=1');
    exit;
}

/* ── Construir el email ── */
$cuerpo = "
=================================================
  NUEVA CONSULTA — KHAVIAR IBIZA
=================================================

Nombre:    {$nombre}
Email:     {$email}
Teléfono:  " . ($telefono ?: '—') . "

Mensaje:
{$mensaje}

-------------------------------------------------
Enviado desde www.khaviaribiza.com
=================================================
";

/* ── Cabeceras del email ── */
$cabeceras  = "From: no-reply@khaviaribiza.com\r\n";
$cabeceras .= "Reply-To: {$email}\r\n";
$cabeceras .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";

/* ── Enviar ── */
$enviado = mail($EMAIL_DESTINO, $EMAIL_ASUNTO, $cuerpo, $cabeceras);

/* ── Redirigir según resultado ── */
if ($enviado) {
    header('Location: index.html?enviado=1');
} else {
    header('Location: index.html?error=1');
}
exit;
