-- Add missing fields to operator_collaboration_agreements table

ALTER TABLE `operator_collaboration_agreements` 
ADD COLUMN `end_date` DATE NULL AFTER `start_date`,
ADD COLUMN `renewal_date` DATE NULL AFTER `end_date`,
ADD COLUMN `marketing_contribution` DECIMAL(5,2) DEFAULT 0 AFTER `commission_value`,
ADD COLUMN `responsibilities` VARCHAR(255) DEFAULT 'Standard Terms & Conditions Apply' AFTER `marketing_contribution`,
ADD COLUMN `status` VARCHAR(50) DEFAULT 'Active' AFTER `responsibilities`;
