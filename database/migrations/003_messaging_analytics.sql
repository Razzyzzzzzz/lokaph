-- Batch 2: threaded messaging and analytics. Import after 002_trust_tools.sql.
USE lokalph;

ALTER TABLE inquiries
  ADD COLUMN last_message_at TIMESTAMP NULL AFTER created_at,
  ADD COLUMN unread_owner INT NOT NULL DEFAULT 0,
  ADD COLUMN unread_customer INT NOT NULL DEFAULT 0,
  ADD INDEX idx_inq_user (user_id, last_message_at),
  ADD INDEX idx_inq_business (business_id, last_message_at);

CREATE TABLE inquiry_messages(
  id INT AUTO_INCREMENT PRIMARY KEY,
  inquiry_id INT NOT NULL,
  sender_id INT NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(inquiry_id, id), INDEX(sender_id, created_at),
  FOREIGN KEY(inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE,
  FOREIGN KEY(sender_id) REFERENCES users(id) ON DELETE CASCADE);

-- Existing inquiries become the first message of a thread.
UPDATE inquiries SET last_message_at = created_at, unread_owner = IF(is_read = 0, 1, 0);
INSERT INTO inquiry_messages(inquiry_id, sender_id, message, created_at) SELECT id, user_id, message, created_at FROM inquiries;

CREATE TABLE business_daily_stats(
  business_id INT NOT NULL,
  day DATE NOT NULL,
  profile_views INT NOT NULL DEFAULT 0,
  product_views INT NOT NULL DEFAULT 0,
  direction_clicks INT NOT NULL DEFAULT 0,
  call_clicks INT NOT NULL DEFAULT 0,
  PRIMARY KEY(business_id, day), INDEX(day),
  FOREIGN KEY(business_id) REFERENCES businesses(id) ON DELETE CASCADE);
