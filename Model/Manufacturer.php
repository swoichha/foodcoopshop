<?php

App::uses('AppModel', 'Model');

/**
 * Manufacturer
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
class Manufacturer extends AppModel
{

    public $useTable = 'manufacturer';

    public $primaryKey = 'id_manufacturer';

    public $actsAs = array(
        'Content'
    );

    public $hasOne = array(
        'Address' => array(
            'className' => 'AddressManufacturer',
            'conditions' => array(
                'Address.alias' => 'manufacturer',
                'Address.deleted' => 0
            ),
            'foreignKey' => 'id_manufacturer'
        ),
        'ManufacturerLang' => array(
            'foreignKey' => 'id_manufacturer'
        )
    );

    public $hasMany = array(
        'CakeInvoices' => array(
            'className' => 'CakeInvoice',
            'foreignKey' => 'id_manufacturer',
            'order' => array(
                'CakeInvoices.send_date DESC'
            ),
            'limit' => 1
        )
    );

    public $validate = array(
        'name' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib einen Namen an.'
            ),
            'minLength' => array(
                'rule' => array(
                    'between',
                    3,
                    64
                ), // 64 is set in db
                'message' => 'Bitte gib zwischen 3 und 64 Zeichen ein.'
            )
        ),
        'iban' => array(
            'regex' => array(
                'rule' => array(
                    'phone',
                    IBAN_REGEX
                ), // phone takes regex
                'allowEmpty' => true,
                'message' => 'Bitte gib einen gültigen IBAN ein.'
            )
        ),
        'bic' => array(
            'regex' => array(
                'rule' => array(
                    'phone',
                    BIC_REGEX
                ), // phone takes regex
                'allowEmpty' => true,
                'message' => 'Bitte gib einen gültigen BIC ein.'
            )
        )
    );

    /**
     *
     * @param $other json
     *            (contains manufacturer options)
     * @return boolean
     */
    public function getOptionSendInvoice($other)
    {
        $sendEmail = true;
        $addressOther = StringComponent::decodeJsonFromForm($other);
        if (is_array($addressOther)) {
            // sending of email can be disabled
            if (isset($addressOther['sendInvoice']) && ! $addressOther['sendInvoice']) {
                $sendEmail = false;
            }
        }
        return $sendEmail;
    }

    /**
     *
     * @param $other json
     *            (contains manufacturer options)
     * @return boolean
     */
    public function getOptionBulkOrdersAllowed($other)
    {
        $bulkOrdersAllowed = Configure::read('app.defaultBulkOrdersAllowed');
        $addressOther = StringComponent::decodeJsonFromForm($other);
        if (isset($addressOther['bulkOrdersAllowed'])) {
            $bulkOrdersAllowed = $addressOther['bulkOrdersAllowed'];
        }
        return $bulkOrdersAllowed;
    }

    /**
     *
     * @param $other json
     *            (contains manufacturer options)
     * @return boolean
     */
    public function getOptionSendOrderList($other)
    {
        $sendEmail = true;
        $addressOther = StringComponent::decodeJsonFromForm($other);
        if (is_array($addressOther)) {
            // sending of email can be disabled
            if (isset($addressOther['sendOrderList']) && ! $addressOther['sendOrderList']) {
                $sendEmail = false;
            }
        }
        return $sendEmail;
    }

    /**
     *
     * @param $other json
     *            (contains manufacturer options)
     * @return boolean
     */
    public function getOptionSendOrderListCc($other)
    {
        $ccRecipients = array();
        $addressOther = StringComponent::decodeJsonFromForm($other);
        if (is_array($addressOther)) {
            if (! empty($addressOther['sendOrderListCc'])) {
                $ccs = explode(';', $addressOther['sendOrderListCc']);
                foreach ($ccs as $cc) {
                    $ccRecipients[] = $cc;
                }
            }
        }
        return $ccRecipients;
    }

    /**
     * bindings with email as foreign key was tricky...
     * 
     * @param array $manufacturer            
     * @return boolean
     */
    public function getCustomerRecord($manufacturer)
    {
        $cm = ClassRegistry::init('Customer');
        
        $cm->recursive = - 1;
        $customer = $cm->find('first', array(
            'conditions' => array(
                'Customer.email' => $manufacturer['Address']['email']
            )
        ));
        
        return $customer;
    }

    public function hasCustomerRecord($manufacturer)
    {
        return (boolean) count($this->getCustomerRecord($manufacturer));
    }

    public function getForMenu($appAuth)
    {
        if ($appAuth->loggedIn() || Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            $productModel = ClassRegistry::init('Product');
        }
        $this->recursive = - 1;
        $conditions = array(
            'Manufacturer.active' => APP_ON
        );
        if (! $this->loggedIn()) {
            $conditions['Manufacturer.is_private'] = APP_OFF;
        }
        
        $manufacturers = $this->find('all', array(
            'fields' => array(
                'Manufacturer.id_manufacturer',
                'Manufacturer.name',
                'Manufacturer.id_manufacturer'
            ),
            'order' => array(
                'Manufacturer.name' => 'ASC'
            ),
            'conditions' => $conditions
        ));
        
        $manufacturersForMenu = array();
        foreach ($manufacturers as $manufacturer) {
            $manufacturerName = $manufacturer['Manufacturer']['name'];
            if ($appAuth->loggedIn() || Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                $productCount = $manufacturerName .= ' (' . $productModel->getCountByManufacturerId($manufacturer['Manufacturer']['id_manufacturer']) . ')';
            }
            $manufacturersForMenu[] = array(
                'name' => $manufacturerName,
                'slug' => Configure::read('slugHelper')->getManufacturerDetail($manufacturer['Manufacturer']['id_manufacturer'], $manufacturer['Manufacturer']['name'])
            );
        }
        return $manufacturersForMenu;
    }

    public function getForDropdown()
    {
        $this->recursive = - 1;
        $manufacturers = $this->find('all', array(
            'fields' => array(
                'Manufacturer.id_manufacturer',
                'Manufacturer.name',
                'Manufacturer.active'
            ),
            'order' => array(
                'Manufacturer.name' => 'ASC'
            )
        ));
        
        $offlineManufacturers = array();
        $onlineManufacturers = array();
        foreach ($manufacturers as $manufacturer) {
            $manufacturerNameForDropdown = $manufacturer['Manufacturer']['name'];
            if ($manufacturer['Manufacturer']['active'] == 0) {
                $offlineManufacturers[$manufacturer['Manufacturer']['id_manufacturer']] = $manufacturerNameForDropdown;
            } else {
                $onlineManufacturers[$manufacturer['Manufacturer']['id_manufacturer']] = $manufacturerNameForDropdown;
            }
        }
        $manufacturersForDropdown = array();
        if (! empty($onlineManufacturers)) {
            $manufacturersForDropdown['online'] = $onlineManufacturers;
        }
        if (! empty($offlineManufacturers)) {
            $manufacturersForDropdown['offline'] = $offlineManufacturers;
        }
        
        return $manufacturersForDropdown;
    }

    public function getProductsByManufacturerId($manufacturerId)
    {
        $sql = "SELECT ";
        $sql .= $this->getFieldsForProductListQuery();
        $sql .= "FROM ".$this->tablePrefix."product Product ";
        $sql .= $this->getJoinsForProductListQuery();
        $sql .= $this->getConditionsForProductListQuery();
        $sql .= "AND Manufacturer.id_manufacturer = :manufacturerId";
        $sql .= $this->getOrdersForProductListQuery();
        
        $params = array(
            'manufacturerId' => $manufacturerId,
            'active' => APP_ON,
            'langId' => Configure::read('app.langId'),
            'shopId' => Configure::read('app.shopId')
        );
        if (! $this->loggedIn()) {
            $params['isPrivate'] = APP_OFF;
        }
        
        $products = $this->getDataSource()->fetchAll($sql, $params);
        
        return $products;
    }

    /**
     * turns eg 24 into 0024
     * 
     * @param int $invoiceNumber            
     */
    public function formatInvoiceNumber($invoiceNumber)
    {
        return str_pad($invoiceNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getOrderList($manufacturerId, $order, $from, $to, $orderState)
    {
        switch ($order) {
            case 'product':
                $orderClause = 'od.product_name ASC, t.rate ASC, ' . Configure::read('htmlHelper')->getCustomerNameForSql() . ' ASC';
                break;
            case 'customer':
                $orderClause = Configure::read('htmlHelper')->getCustomerNameForSql() . ' ASC, od.product_name ASC';
                break;
        }
        
        $customerNameAsSql = Configure::read('htmlHelper')->getCustomerNameForSql();
        
        $sql = "SELECT
                m.id_manufacturer HerstellerID,
                m.name AS Hersteller,
                m.uid_number as UID, m.additional_text_for_invoice as Zusatztext,
                ma.*,
                t.rate as Steuersatz,
                odt.total_amount AS MWSt,
                od.product_id AS ArtikelID,
                od.product_name AS ArtikelName,
                od.product_quantity AS Menge,
                od.total_price_tax_incl AS PreisIncl,
                od.total_price_tax_excl as PreisExcl,
                DATE_FORMAT (o.date_add, '%d.%m.%Y') as Bestelldatum,
                pl.description_short as Produktbeschreibung,
                c.id_customer AS Kundennummer,
                {$customerNameAsSql} AS Kunde,
                od.id_order_invoice AS Rechnungsnummer
            FROM ".$this->tablePrefix."order_detail od
                LEFT JOIN ".$this->tablePrefix."product p ON p.id_product = od.product_id
                LEFT JOIN ".$this->tablePrefix."orders o ON o.id_order = od.id_order
                LEFT JOIN ".$this->tablePrefix."order_detail_tax odt ON odt.id_order_detail = od.id_order_detail 
                LEFT JOIN ".$this->tablePrefix."product_lang pl ON p.id_product = pl.id_product
                LEFT JOIN ".$this->tablePrefix."customer c ON c.id_customer = o.id_customer
                LEFT JOIN ".$this->tablePrefix."manufacturer m ON m.id_manufacturer = p.id_manufacturer
                LEFT JOIN ".$this->tablePrefix."address ma ON m.id_manufacturer = ma.id_manufacturer
                LEFT JOIN ".$this->tablePrefix."tax t ON odt.id_tax = t.id_tax
            WHERE 1 
                AND m.id_manufacturer = :manufacturerId
                AND DATE_FORMAT(o.date_add, '%Y-%m-%d') >= :dateFrom
                AND DATE_FORMAT(o.date_add, '%Y-%m-%d') <= :dateTo
                AND pl.id_lang = 1
                AND ma.deleted = 0
                AND ma.alias = 'manufacturer'
                AND o.current_state IN(:orderStates)
                ORDER BY {$orderClause}, DATE_FORMAT (o.date_add, '%d.%m.%Y, %H:%i') DESC;";
        
        $params = array(
            'manufacturerId' => $manufacturerId,
            'dateFrom' => "'" . Configure::read('timeHelper')->formatToDbFormatDate($from) . "'",
            'dateTo' => "'" . Configure::read('timeHelper')->formatToDbFormatDate($to) . "'",
            'orderStates' => join(',', $orderState)
        );
        // strange behavior: if $this->getDataSource()->fetchAll is used, $results is empty
        // problem seems to be caused by date fields
        // with interpolateQuery and normal fire of sql statemt, result is not empty and works...
        $replacedQuery = $this->interpolateQuery($sql, $params);
        $results = $this->query($replacedQuery);
        return $results;
    }
}

?>