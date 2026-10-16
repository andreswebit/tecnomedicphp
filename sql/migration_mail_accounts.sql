-- ============================================================
-- TECNOMEDIC — Cuentas de Email para Mensajería Unificada
-- ============================================================
-- Crea tabla tm_mail_accounts y si no existen, inserta
-- compatibilidad con las cuentas históricas:
--   - contacto
--   - noreply
--
-- Nota: en esta implementación inicial no hay cifrado.
-- En local/dev se guardan passwords en texto plano.
-- ============================================================

CREATE TABLE IF NOT EXISTS tm_mail_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,

  account_key VARCHAR(50) NOT NULL UNIQUE,          -- ej: contacto, noreply, u_otras...
  display_name VARCHAR(150) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,

  -- IMAP
  imap_host VARCHAR(255) NOT NULL,
  imap_port INT NOT NULL DEFAULT 993,
  imap_username VARCHAR(255) NOT NULL,
  imap_password_enc TEXT NULL,

  -- SMTP
  smtp_host VARCHAR(255) NOT NULL,
  smtp_port INT NOT NULL DEFAULT 465,
  smtp_username VARCHAR(255) NOT NULL,
  smtp_password_enc TEXT NULL,

  -- SMTP identidad
  smtp_from_email VARCHAR(255) NOT NULL,
  smtp_from_name VARCHAR(150) NULL,

  -- Reply-To opcional
  smtp_reply_to_email VARCHAR(255) NULL,
  smtp_reply_to_name VARCHAR(150) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Semilla: contacto / noreply (derivados del .env global)
-- ============================================================
-- Como SQL no conoce .env, estas inserciones se dejan
-- en modo "plantilla" usando valores por defecto razonables.
-- Luego, Admin > Mensajes permite completar/editar.
-- ============================================================

-- contacto
INSERT INTO tm_mail_accounts
  (account_key, display_name, active,
   imap_host, imap_port, imap_username, imap_password_enc,
   smtp_host, smtp_port, smtp_username, smtp_password_enc,
   smtp_from_email, smtp_from_name,
   smtp_reply_to_email, smtp_reply_to_name)
SELECT
  'contacto',
  'Contacto',
  1,
  COALESCE((SELECT 'c1452348.ferozo.com'), 'c1452348.ferozo.com'),
  993,
  COALESCE((SELECT 'contacto@tecnomedic.com.ar'), 'contacto@tecnomedic.com.ar'),
  NULL,
  COALESCE((SELECT 'c1452348.ferozo.com'), 'c1452348.ferozo.com'),
  465,
  COALESCE((SELECT 'noreply@tecnomedic.com.ar'), 'noreply@tecnomedic.com.ar'),
  NULL,
  COALESCE((SELECT 'noreply@tecnomedic.com.ar'), 'noreply@tecnomedic.com.ar'),
  'TECNOMEDIC',
  COALESCE((SELECT 'contacto@tecnomedic.com.ar'), 'contacto@tecnomedic.com.ar'),
  'TECNOMEDIC'
WHERE NOT EXISTS (SELECT 1 FROM tm_mail_accounts WHERE account_key = 'contacto');

-- noreply
INSERT INTO tm_mail_accounts
  (account_key, display_name, active,
   imap_host, imap_port, imap_username, imap_password_enc,
   smtp_host, smtp_port, smtp_username, smtp_password_enc,
   smtp_from_email, smtp_from_name,
   smtp_reply_to_email, smtp_reply_to_name)
SELECT
  'noreply',
  'No Reply',
  1,
  COALESCE((SELECT 'c1452348.ferozo.com'), 'c1452348.ferozo.com'),
  993,
  COALESCE((SELECT 'noreply@tecnomedic.com.ar'), 'noreply@tecnomedic.com.ar'),
  NULL,
  COALESCE((SELECT 'c1452348.ferozo.com'), 'c1452348.ferozo.com'),
  465,
  COALESCE((SELECT 'noreply@tecnomedic.com.ar'), 'noreply@tecnomedic.com.ar'),
  NULL,
  COALESCE((SELECT 'noreply@tecnomedic.com.ar'), 'noreply@tecnomedic.com.ar'),
  'TECNOMEDIC',
  NULL,
  NULL
WHERE NOT EXISTS (SELECT 1 FROM tm_mail_accounts WHERE account_key = 'noreply');
