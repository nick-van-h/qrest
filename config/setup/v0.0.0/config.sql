CREATE TABLE `config` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `setting` VARCHAR(255) NOT NULL,
    `value_str` VARCHAR(255) NULL,
    `value_int` INT NULL,
    `type` VARCHAR(3) NOT NULL COMMENT 'str/int',
    PRIMARY KEY (`id`),
    UNIQUE `setting` (`setting`)
) ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;