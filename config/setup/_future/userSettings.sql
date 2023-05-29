CREATE TABLE `userSettings` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `userId` BIGINT NOT NULL,
    `setting` VARCHAR(255) NOT NULL,
    `value_enc` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    INDEX `userId` (`userId`),
) ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;