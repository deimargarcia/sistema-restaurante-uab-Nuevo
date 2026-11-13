-- Permite volver a reservar una fecha después de cancelar la reserva anterior.
-- Antes de ejecutar, revisa que no existan duplicados activos por titular/fecha.
ALTER TABLE reservas
  DROP INDEX uq_reserva_titular,
  DROP INDEX uq_reserva_familiar,
  ADD COLUMN reserva_activa TINYINT
    GENERATED ALWAYS AS (IF(estado = 'CANCELADO', NULL, 1)) STORED,
  ADD UNIQUE KEY uq_reserva_titular (usuario_id, fecha_reserva, tipo_beneficiario, reserva_activa),
  ADD UNIQUE KEY uq_reserva_familiar (familiar_id, fecha_reserva, reserva_activa);
