CREATE TABLE `items` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `userId` BIGINT NOT NULL,
    `collectionUid` VARCHAR(255) NOT NULL,
    `uid` VARCHAR(255) NOT NULL,
    `sortOrder` INT NOT NULL,
    `name_enc` VARCHAR(255) NOT NULL,
    `checked_enc` VARCHAR(255) NULL,
    `note_enc` BLOB,
    `iv` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `userId` (`userId`),
    INDEX `collectionUid` (`collectionUid`),
    INDEX `uid` (`uid`),
    INDEX `sortOrder` (`sortOrder`)
) ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;