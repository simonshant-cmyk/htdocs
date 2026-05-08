ALTER TABLE organization
  ADD COLUMN rejection_reason TEXT NULL DEFAULT NULL
  AFTER status_id;
