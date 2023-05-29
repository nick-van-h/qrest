ALTER TABLE `test` CHANGE `value` `value` VARCHAR(max) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `test` ADD `test` BOOLEAN NULL AFTER `value`;
ALTER TABLE `test` DROP `test`;
DROP TABLE `test`;