-- =============================================
-- RESTAURANTE INSTITUCIONAL "RUMBO AL CIELO"
-- Unión Boliviana — Schema v1.0
-- Compatible con MySQL 8 / MariaDB / phpMyAdmin
-- =============================================

CREATE DATABASE IF NOT EXISTS restaurante_rumbo_al_cielo_nuevo
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurante_rumbo_al_cielo_nuevo;

-- -----------------------------------------------
-- 1. USUARIOS / EMPLEADOS
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre           VARCHAR(150)  NOT NULL,
  apellido         VARCHAR(100)  NULL DEFAULT NULL,
  ci               VARCHAR(20)   NULL DEFAULT NULL,
  departamento     VARCHAR(100)  NULL DEFAULT NULL,
  celular          VARCHAR(20)   NULL DEFAULT NULL,
  correo           VARCHAR(150)  NOT NULL UNIQUE,
  contrasena       VARCHAR(255)  NOT NULL,
  tipo_cuenta      ENUM('ADMIN','PENSIONADO','NO_PENSIONADO') NOT NULL,
  codigo_empleado  VARCHAR(50)   NULL DEFAULT NULL UNIQUE,
  activo           TINYINT(1)    NOT NULL DEFAULT 1,
  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_ci (ci)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- 2. FAMILIARES (máx. 3 activos por titular pensionado)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS familiares (
  id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empleado_titular_id  INT UNSIGNED NOT NULL,
  nombre               VARCHAR(150) NOT NULL,
  ci                   VARCHAR(20)  NULL DEFAULT NULL,
  parentesco           ENUM('PADRE','MADRE','HIJO','HIJA','CONYUGE') NULL DEFAULT NULL,
  estado               ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  created_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empleado_titular_id) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_titular (empleado_titular_id),
  UNIQUE KEY uq_familiares_ci (ci)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- 2.1. PENSIONAMIENTOS MENSUALES
-- Cada afiliación genera reservas automáticas de lunes a jueves.
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS pensionamientos_mensuales (
  id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id           INT UNSIGNED NOT NULL,
  familiar_id          INT UNSIGNED NULL DEFAULT NULL,
  anio                 SMALLINT UNSIGNED NOT NULL,
  mes                  TINYINT UNSIGNED NOT NULL,
  registrado_por_id    INT UNSIGNED NOT NULL,
  beneficiario_tipo    ENUM('TITULAR','FAMILIAR') GENERATED ALWAYS AS
                         (IF(familiar_id IS NULL, 'TITULAR', 'FAMILIAR')) STORED,
  beneficiario_id      INT UNSIGNED GENERATED ALWAYS AS
                         (IFNULL(familiar_id, usuario_id)) STORED,
  created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (familiar_id) REFERENCES familiares(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (registrado_por_id) REFERENCES usuarios(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  UNIQUE KEY uq_pensionamiento_beneficiario_mes (beneficiario_tipo, beneficiario_id, anio, mes),
  INDEX idx_pensionamiento_titular_mes (usuario_id, anio, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- 3. CALENDARIO OPERATIVO
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS calendario_operativo (
  id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fecha                 DATE         NOT NULL UNIQUE,
  es_feriado            TINYINT(1)   NOT NULL DEFAULT 0,
  descripcion_feriado   VARCHAR(200) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- 3.1. PARÁMETROS DEL SISTEMA
-- Una sola fila con las reglas vigentes del restaurante.
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS configuracion_sistema (
  id                    TINYINT UNSIGNED PRIMARY KEY,
  tarifa_pensionado     DECIMAL(8,2) NOT NULL DEFAULT 15.00,
  tarifa_esporadico     DECIMAL(8,2) NOT NULL DEFAULT 18.00,
  minimo_almuerzos_mes  TINYINT UNSIGNED NOT NULL DEFAULT 10,
  hora_limite           TIME NOT NULL DEFAULT '09:00:00',
  actualizado_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracion_sistema (id, tarifa_pensionado, tarifa_esporadico, minimo_almuerzos_mes, hora_limite)
VALUES (1, 15.00, 18.00, 10, '09:00:00');

-- -----------------------------------------------
-- 4. MENÚS
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS menus (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fecha            DATE         NOT NULL UNIQUE,
  sopa             VARCHAR(150) NOT NULL,
  plato_principal  VARCHAR(150) NOT NULL,
  postre           VARCHAR(150) NOT NULL,
  descripcion_menu TEXT         NOT NULL,
  imagen_path      VARCHAR(255) NULL DEFAULT NULL,
  publicado        TINYINT(1)   NOT NULL DEFAULT 1,
  created_by       INT UNSIGNED NOT NULL,
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- 5. RESERVAS
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS reservas (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id        INT UNSIGNED  NULL DEFAULT NULL,
  familiar_id       INT UNSIGNED  NULL DEFAULT NULL,
  tipo_beneficiario ENUM('TITULAR','FAMILIAR') NOT NULL,
  fecha_reserva     DATE          NOT NULL,
  estado            ENUM('RESERVADO','CANCELADO','PENDIENTE_ENTREGA','ENTREGADO','COBRADO_INASISTENCIA') NOT NULL DEFAULT 'RESERVADO',
  modalidad         ENUM('PENSIONADO','ESPORADICO') NOT NULL DEFAULT 'ESPORADICO',
  reserva_activa    TINYINT GENERATED ALWAYS AS (IF(estado = 'CANCELADO', NULL, 1)) STORED,
  fecha_creacion    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_cancelacion DATETIME      NULL DEFAULT NULL,
  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE SET NULL,
  FOREIGN KEY (familiar_id) REFERENCES familiares(id) ON DELETE SET NULL,
  UNIQUE KEY uq_reserva_titular  (usuario_id,  fecha_reserva, tipo_beneficiario, reserva_activa),
  UNIQUE KEY uq_reserva_familiar (familiar_id, fecha_reserva, reserva_activa),
  INDEX idx_fecha (fecha_reserva)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- 6. REPORTES MENSUALES (tabla de cierre)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS reportes_mensuales (
  id                       INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  anio                     YEAR          NOT NULL,
  mes                      TINYINT       NOT NULL,
  codigo_empleado          VARCHAR(50)   NOT NULL,
  nombre_empleado          VARCHAR(150)  NOT NULL,
  almuerzos_titular        INT UNSIGNED  NOT NULL DEFAULT 0,
  monto_titular            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  detalle_familiares       JSON          NULL DEFAULT NULL,
  almuerzos_familiares     INT UNSIGNED  NOT NULL DEFAULT 0,
  penalizacion_familiares  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_descuento          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  generado_en              DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reporte (anio, mes, codigo_empleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No se crea una cuenta admin con contraseña compartida.
-- Después de importar, crear el administrador con:
-- php backend/scripts/bootstrap_admin.php
