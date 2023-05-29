CREATE TABLE `itemAttributes` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `itemId` BIGINT NULL,
    `attribute_enc` VARCHAR(255) NOT NULL,
    `value_enc` VARCHAR(255) NOT NULL,
    `iv` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `userId` (`userId`),
    INDEX `itemId` (`itemId`)
) ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;