ALTER TABLE `fcs_orders` ADD `comment` TEXT NULL AFTER `cancellation_terms_accepted`;
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_ORDER_COMMENT_ENABLED', 'Kommentarfeld bei Bestell-Abschluss anzeigen?<br /><div class=\"small\">Wird im Admin-Bereich unter \"Bestellungen\" angezeigt.</div>', '0', 'boolean', '13', '2017-07-09 00:00:00', '2017-07-09 00:00:00');