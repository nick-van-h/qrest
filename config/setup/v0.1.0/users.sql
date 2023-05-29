CREATE TABLE `users` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `key_enc_pw` VARCHAR(255) NOT NULL,
    `key_enc_rec` VARCHAR(255) NULL,
    `key_iv` VARCHAR(255) NOT NULL,
    `validation_message_enc` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `username` (`username`)
) ENGINE = InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;