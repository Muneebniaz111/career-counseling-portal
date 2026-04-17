-- Contact System Database Migration
-- This script updates the contact_messages table to support the new contact system with admin replies

-- Step 1: Add missing columns to contact_messages table if they don't exist
ALTER TABLE contact_messages 
ADD COLUMN IF NOT EXISTS user_id INT AFTER id,
ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'open' AFTER phone,
ADD FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Step 2: Create contact_replies table if it doesn't exist
CREATE TABLE IF NOT EXISTS contact_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    admin_id INT NOT NULL,
    reply_message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contact_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Step 3: Create contact_notifications table if it doesn't exist
CREATE TABLE IF NOT EXISTS contact_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contact_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Step 4: Verify the structure
-- Contact Messages Table Structure:
-- +-----------+-------------+------+-----+---------+----------------+
-- | Field     | Type        | Null | Key | Default | Extra          |
-- +-----------+-------------+------+-----+---------+----------------+
-- | id        | int         | NO   | PRI | NULL    | auto_increment |
-- | user_id   | int         | YES  | MUL | NULL    |                |
-- | name      | varchar(100)| NO   |     | NULL    |                |
-- | email     | varchar(100)| NO   |     | NULL    |                |
-- | subject   | varchar(255)| YES  |     | NULL    |                |
-- | message   | text        | NO   |     | NULL    |                |
-- | phone     | varchar(15) | YES  |     | NULL    |                |
-- | status    | varchar(20) | YES  |     | open    |                |
-- | created_at| timestamp   | YES  |     | NULL    |                |
-- +-----------+-------------+------+-----+---------+----------------+

-- Contact Replies Table Structure:
-- +---------------+----------+------+-----+---------+----------------+
-- | Field         | Type     | Null | Key | Default | Extra          |
-- +---------------+----------+------+-----+---------+----------------+
-- | id            | int      | NO   | PRI | NULL    | auto_increment |
-- | contact_id    | int      | NO   | MUL | NULL    |                |
-- | admin_id      | int      | NO   | MUL | NULL    |                |
-- | reply_message | text     | NO   |     | NULL    |                |
-- | created_at    | timestamp| YES  |     | NULL    |                |
-- +---------------+----------+------+-----+---------+----------------+

-- Contact Notifications Table Structure:
-- +------------+----------+------+-----+---------+----------------+
-- | Field      | Type     | Null | Key | Default | Extra          |
-- +------------+----------+------+-----+---------+----------------+
-- | id         | int      | NO   | PRI | NULL    | auto_increment |
-- | contact_id | int      | NO   | MUL | NULL    |                |
-- | user_id    | int      | NO   | MUL | NULL    |                |
-- | is_read    | tinyint  | YES  |     | 0       |                |
-- | created_at | timestamp| YES  |     | NULL    |                |
-- +------------+----------+------+-----+---------+----------------+

-- Migration completed!
-- The contact system now supports:
-- ✓ User tracking (user_id)
-- ✓ Message status tracking (open/replied)
-- ✓ Admin replies (contact_replies)
-- ✓ User notifications (contact_notifications)