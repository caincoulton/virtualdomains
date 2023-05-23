ALTER TABLE `#__virtualdomain` 
CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL ,
CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL ;
