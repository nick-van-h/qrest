CREATE TABLE `collections` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `parentCollectionId` BIGINT NOT NULL,
    `childCollectionId` BIGINT NOT NULL,
    `name_enc` narchar(255) NULL,
    PRIMARY KEY (`id`),
    INDEX `parentCollectionId` (`parentCollectionId`)
) ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;