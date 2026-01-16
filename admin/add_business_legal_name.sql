-- Add business_legal_name to operators table

ALTER TABLE `operators` 
ADD COLUMN `business_legal_name` VARCHAR(255) NULL AFTER `full_name`;
