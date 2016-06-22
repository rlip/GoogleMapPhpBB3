CREATE TABLE `phpbb_postal_code_location` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`postal_code` VARCHAR(6) NOT NULL,
`latitude` DECIMAL(10,7) NOT NULL,
`longitude` DECIMAL(10,7) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX `postal_code_unique` (`postal_code`),
INDEX `postal_code` (`postal_code`)
)
ENGINE=InnoDB;

/* plus pf_postal_code option */