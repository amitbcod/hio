-- Add operator_approve_flag to operators table
-- This flag indicates whether the operator account has been verified/approved

ALTER TABLE operators
ADD COLUMN operator_approve_flag TINYINT(1) NOT NULL DEFAULT 0 AFTER account_status,
ADD INDEX idx_operator_approve_flag (operator_approve_flag);

-- Update existing records to have flag = 0 (not approved)
UPDATE operators SET operator_approve_flag = 0 WHERE operator_approve_flag IS NULL;
