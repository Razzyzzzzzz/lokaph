-- Batch 1: business verification, product approval, audit logs.
-- Import after 001_schema.sql. Existing products stay approved; new owner products start as pending.
USE lokalph;

ALTER TABLE products
  ADD COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER available,
  ADD COLUMN review_note VARCHAR(255) NULL AFTER status,
  ADD INDEX idx_products_status (status);

CREATE TABLE verification_requests(
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  doc_type ENUM('dti','sec','barangay_permit','mayor_permit','other') NOT NULL,
  document_path VARCHAR(255) NOT NULL,
  note VARCHAR(500) NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  review_note VARCHAR(255) NULL,
  reviewed_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL,
  INDEX(status), INDEX(business_id),
  FOREIGN KEY(business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL);

CREATE TABLE audit_logs(
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(60) NOT NULL,
  entity_type VARCHAR(30) NULL,
  entity_id INT NULL,
  details VARCHAR(500) NULL,
  ip VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(created_at), INDEX(action), INDEX(entity_type, entity_id),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL);
