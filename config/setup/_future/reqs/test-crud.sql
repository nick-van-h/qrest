INSERT INTO `test`(`id`, `value`) VALUES ('1','foo');
SELECT * FROM `test` WHERE 1;
UPDATE `test` SET `id`='1',`value`='Bar' WHERE 1;
DELETE FROM `test` WHERE `id`='1';