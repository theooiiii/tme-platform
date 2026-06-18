USE tme_platform;

ALTER TABLE chat_messages
    ADD COLUMN IF NOT EXISTS attachment_path VARCHAR(255) NULL AFTER message,
    ADD COLUMN IF NOT EXISTS attachment_name VARCHAR(180) NULL AFTER attachment_path,
    ADD COLUMN IF NOT EXISTS attachment_type VARCHAR(120) NULL AFTER attachment_name,
    ADD COLUMN IF NOT EXISTS attachment_size BIGINT UNSIGNED NULL AFTER attachment_type;

CREATE INDEX IF NOT EXISTS chat_messages_attachment_index ON chat_messages (attachment_path);
