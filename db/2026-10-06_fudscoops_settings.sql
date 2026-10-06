-- Cooperative settings that the treasurer can change from Treasurer/savings_settings.php
-- Run once on the fuded535_fudscoops database.

CREATE TABLE IF NOT EXISTS `fudscoops_settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `updated_by` varchar(45) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Minimum monthly savings (naira) for new applicants and savings updates
INSERT IGNORE INTO `fudscoops_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`)
VALUES ('min_monthly_savings', '2000', 'system', NOW());
