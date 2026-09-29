-- ============================================================
-- TECNOMEDIC — Módulo de Mensajería Unificada (Fase H / Mensajes)
-- ============================================================

-- 1. Tabla de Caché / Registro de Emails (IMAP/SMTP)
CREATE TABLE IF NOT EXISTS tm_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cuenta VARCHAR(50) NOT NULL DEFAULT 'contacto', -- 'contacto' o 'noreply'
    uid_imap INT NULL,                               -- UID del servidor IMAP Ferozo
    folder VARCHAR(50) NOT NULL DEFAULT 'INBOX',     -- INBOX, SENT, DRAFTS, TRASH
    remitente_nombre VARCHAR(150) NULL,
    remitente_email VARCHAR(150) NOT NULL,
    destinatario VARCHAR(150) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    cuerpo_txt LONGTEXT NULL,
    cuerpo_html LONGTEXT NULL,
    tiene_adjuntos TINYINT(1) NOT NULL DEFAULT 0,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    destacado TINYINT(1) NOT NULL DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cuenta_folder (cuenta, folder),
    INDEX idx_leido (leido),
    INDEX idx_uid (cuenta, uid_imap)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Sesiones / Conversaciones de WhatsApp (Twilio)
CREATE TABLE IF NOT EXISTS tm_wa_chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telefono VARCHAR(30) NOT NULL UNIQUE,          -- ej: +5493794123456
    nombre_contacto VARCHAR(120) NULL,
    ultimo_mensaje TEXT NULL,
    ultimo_mensaje_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    mensajes_sin_leer INT NOT NULL DEFAULT 0,
    INDEX idx_ultimo_at (ultimo_mensaje_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Mensajes Individuales de WhatsApp
CREATE TABLE IF NOT EXISTS tm_wa_mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    direccion ENUM('in', 'out') NOT NULL,          -- 'in' (recibido), 'out' (enviado)
    mensaje TEXT NOT NULL,
    sid_twilio VARCHAR(100) NULL,                   -- MessageSid de Twilio
    estado ENUM('sent', 'delivered', 'read', 'failed') DEFAULT 'sent',
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES tm_wa_chats(id) ON DELETE CASCADE,
    INDEX idx_chat_creado (chat_id, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
