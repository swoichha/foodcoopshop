<?php
/**
 * app.config.php
 * this file contains the main configuration for foodcoopshop
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

date_default_timezone_set('Europe/Vienna');

App::uses('MyHtmlHelper', 'View/Helper');
App::uses('View', 'View');
Configure::write('htmlHelper', new MyHtmlHelper(new View()));
App::uses('MyTimeHelper', 'View/Helper');
Configure::write('timeHelper', new MyTimeHelper(new View()));
App::uses('SlugHelper', 'View/Helper');
Configure::write('slugHelper', new SlugHelper(new View()));

Configure::write('app.jsNamespace', 'foodcoopshop');

define('APP_ON', 1);
define('APP_OFF', 0);
define('APP_DEL', -1);

define('ORDER_STATE_OPEN', 3);
define('ORDER_STATE_CLOSED', 5);
define('ORDER_STATE_CASH_FREE', 1);
define('ORDER_STATE_CASH', 2);
define('ORDER_STATE_CANCELLED', 6);

define('CUSTOMER_GROUP_MEMBER', 3);
define('CUSTOMER_GROUP_ADMIN', 4);
define('CUSTOMER_GROUP_SUPERADMIN', 5);

define('PASSWORD_REGEX', '/^([^\\s]){6,32}$/');
define('PHONE_REGEX', '/^[0-9 ()+-\/]{7,20}$/');
define('ZIP_REGEX', '/^[0-9]{4,5}$/');
define('IBAN_REGEX', '/^([0-9a-zA-Z]\s?){20}$/');
define('BIC_REGEX', '/^[a-z]{6}[2-9a-z][0-9a-np-z]([a-z0-9]{3}|x{3})?$/i');

Configure::write('app.visibleOrderStates', array(
    ORDER_STATE_OPEN => 'offen',
    ORDER_STATE_CASH_FREE => 'abgeschlossen',
));

Configure::write('app.filesDir', DS.'files');
Configure::write('app.tmpWwwDir', DS.'tmp');
Configure::write('app.registrationAttachmentDir', Configure::read('app.filesDir') . DS . 'registration');
Configure::write('app.uploadedImagesDir', Configure::read('app.filesDir') . DS . 'images');

Configure::write('app.folder.invoices', APP . 'files_private'. DS . 'invoices');
Configure::write('app.folder.order_lists', APP . 'files_private' . DS .'order_lists');
Configure::write('app.folder.invoices_with_current_year_and_month', Configure::read('app.folder.invoices').DS.date('Y').DS.date('m'));
Configure::write('app.folder.order_lists_with_current_year_and_month', Configure::read('app.folder.order_lists').DS.date('Y').DS.date('m'));

Configure::write('app.useManufacturerCompensationPercentage', false);
Configure::write('app.defaultCompensationPercentage', 0);
Configure::write('app.defaultSendOrderList', true);
Configure::write('app.defaultSendInvoice', true);
Configure::write('app.defaultTaxId', 2);
Configure::write('app.defaultBulkOrdersAllowed', false);

Configure::write('app.isDepositPaymentCashless', false);
Configure::write('app.depositPaymentCashlessStartDate', '2016-01-01');

Configure::write('app.allowManualOrderListSending', false);
/**
 * implementeded for tuesday (2) and wednesday (3)
 */
Configure::write('app.sendOrderListsWeekday', 3);

Configure::write('app.customerMainNamePart', 'firstname');
Configure::write('app.categoryAllProducts', 20);

Configure::write('app.memberFeeFlexibleEnabled', false);

/**
 * image upload configuration
 */
Configure::write('app.productImageSizes', array(
 	 '150' => array('suffix' => '-home_default'),      // list page
	 '358' => array('suffix' => '-large_default'),     // detail page
	 '800' => array('suffix' => '-thickbox_default')   // lightbox
));
Configure::write('app.blogPostImageSizes', array(
 	 '170' => array('suffix' => '-home-default'),     // detail / list page
	 '800' => array('suffix' => '-single-default')    // lightbox
));
Configure::write('app.manufacturerImageSizes', array(
 	 '200' => array('suffix' => '-medium_default'),  // detail / list page
	 '800' => array('suffix' => '-large_default')    // lightbox
));
Configure::write('app.categoryImageSizes', array(
 	 '717' => array('suffix' => '-category_default') // detail AND lightbox
));
Configure::write('app.sliderImageSizes', array(
    '905' => array('suffix' => '-slider') // detail AND lightbox
));
Configure::write('app.tmpUploadFileSize', 800);
Configure::write('app.tmpUploadImagesDir', Configure::read('app.tmpWwwDir').DS.'images');

Configure::write('app.emailErrorLoggingEnabled', false);

Configure::write('app.langId', 1);
Configure::write('app.shopId', 1);
Configure::write('app.countryId', 2); // austria: 2, germany: 1

/**
 * if you work on windows, change to e.g
 * 'C:\\Programme\\xampp\\mysql\\bin\\mysqldump.exe'
 */
Configure::write('app.mysqlDumpCommand', 'mysqldump');

?>