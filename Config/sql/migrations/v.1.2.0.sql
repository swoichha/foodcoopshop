INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_APP_NAME', 'Name der Foodcoop', '', 'text', '5', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_APP_ADDRESS', 'Adresse der Foodcoop<br /><div class="small">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>', '', 'textarea', '6', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_APP_EMAIL', 'E-Mail-Adresse der Foodcoop<br /><div class="small"></div>', '', 'text', '7', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_PLATFORM_OWNER', 'Betreiber der Plattform<br /><div class="small">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>', '', 'textarea', '8', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_SHOP_ORDER_DEFAULT_STATE', 'Bestellstatus für Sofort-Bestellungen', '1', 'dropdown', '75', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
UPDATE `fcs_configuration` SET `type` = 'textarea_big' WHERE `fcs_configuration`.`name` = 'FCS_FOOTER_CMS_TEXT';
UPDATE `fcs_configuration` SET `type` = 'textarea_big' WHERE `fcs_configuration`.`name` = 'FCS_RIGHT_INFO_BOX_HTML';
UPDATE `fcs_configuration` SET `type` = 'textarea_big' WHERE `fcs_configuration`.`name` = 'FCS_REGISTRATION_EMAIL_TEXT';