<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('Manufacturer', 'Model');

/**
 * ManufacturersControllerTest
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
class ManufacturersControllerTest extends AppCakeTestCase
{

    public $Manufacturer;
    
    // called only after the first test method of this class
    public static function setUpBeforeClass()
    {
        self::initTestDatabase();
    }

    public function setUp()
    {
        parent::setUp();
        $this->Manufacturer = new Manufacturer();
    }

    public function testAddManufacturer()
    {
        $this->browser->doFoodCoopShopLogin();
        
        $manufacturerData = array(
            'Manufacturer' => array(
                'name' => 'Test Manufacturer',
                'bank_name' => 'Test Bank',
                'iban' => 'Iban',
                'bic' => 'bic',
                'holiday' => 0,
                'active' => 1,
                'additional_text_for_invoice' => '',
                'uid_number' => '',
                'tmp_image' => '',
                'delete_image' => ''
            ),
            'ManufacturerLang' => array(
                'short_description' => 'Test Description'
            ),
            'Address' => array(
                'firstname' => '',
                'lastname' => '',
                'email' => 'fcs-demo-gemuese-hersteller@mailinator.com',
                'phone_mobile' => '',
                'phone' => '',
                'address1' => 'Street 1',
                'address2' => 'Street 2',
                'postcode' => '',
                'city' => 'Test City'
            ),
            'referer' => ''
        );
        $response = $this->addManufacturer($manufacturerData);
        
        // provoke errors
        $this->assertRegExp('/' . preg_quote('Beim Speichern sind 5 Fehler aufgetreten!') . '/', $response);
        $this->assertRegExp('/' . preg_quote('Bitte gib einen gültigen IBAN ein.') . '/', $response);
        $this->assertRegExp('/' . preg_quote('Bitte gib einen gültigen BIC ein.') . '/', $response);
        $this->assertRegExp('/' . preg_quote('Diese E-Mail-Adresse existiert bereits.') . '/', $response);
        $this->assertRegExp('/' . preg_quote('Bitte gib den Vornamen des Rechnungsempfängers an.') . '/', $response);
        $this->assertRegExp('/' . preg_quote('Bitte gib den Nachnamen des Rechnungsempfängers an.') . '/', $response);
        
        // set proper data and post again
        $manufacturerData['Manufacturer']['iban'] = 'AT193357281080332578';
        $manufacturerData['Manufacturer']['bic'] = 'BFKKAT2K';
        $manufacturerData['Address']['email'] = 'test-manufacturer@mailinator.com';
        $manufacturerData['Address']['firstname'] = 'Test';
        $manufacturerData['Address']['lastname'] = 'Manufacturer';
        
        $response = $this->addManufacturer($manufacturerData);
        
        $this->assertRegExp('/' . preg_quote('Der Hersteller wurde erfolgreich gespeichert.') . '/', $response);
        
        // get inserted manufacturer from database and check detail page for patterns
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.name' => $manufacturerData['Manufacturer']['name']
            )
        ));
        
        $response = $this->browser->get($this->Slug->getManufacturerDetail($manufacturer['Manufacturer']['id_manufacturer'], $manufacturer['Manufacturer']['name']));
        $this->assertRegExp('/' . preg_quote('<h1>' . $manufacturer['Manufacturer']['name']) . '/', $response);
        
        $this->doTestCustomerRecord($manufacturer);
        
        $this->browser->doFoodCoopShopLogout();
    }

    public function testEditManufacturer()
    {
        $this->browser->doFoodCoopShopLogin();
        
        $manufacturerId = 4;
        $response = $this->getEditManufacturer($manufacturerId);
        
        $this->browser->setFieldById('ManufacturerName', 'Huhuu');
        
        // test with valid customer email address must fail
        $this->browser->setFieldById('AddressEmail', 'foodcoopshop-demo-mitglied@mailinator.com');
        $this->browser->submitFormById('ManufacturerEditForm');
        $this->assertRegExp('/' . preg_quote('Diese E-Mail-Adresse existiert bereits.') . '/', $this->browser->getContent());
        
        // test with valid manufacturer email address must fail
        $this->browser->setFieldById('AddressEmail', 'fcs-demo-gemuese-hersteller@mailinator.com');
        $this->browser->submitFormById('ManufacturerEditForm');
        $this->assertRegExp('/' . preg_quote('Diese E-Mail-Adresse existiert bereits.') . '/', $this->browser->getContent());
        
        // test with valid email address
        $this->browser->setFieldById('AddressEmail', 'new-email-address@mailinator.com');
        $this->browser->submitFormById('ManufacturerEditForm');
        $this->assertRegExp('/' . preg_quote('Der Hersteller wurde erfolgreich gespeichert.') . '/', $this->browser->getContent());
        
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        $this->doTestCustomerRecord($manufacturer);
        
        $this->browser->doFoodCoopShopLogout();
    }

    public function testAutomaticAddingOfCustomerRecord()
    {
        $this->browser->doFoodCoopShopLogin();
        
        // manufacturer 16 does not yet have a related customer record (foreign_key: email)
        $manufacturerId = 16;
        $response = $this->getEditManufacturer($manufacturerId);
        
        // saving customer must add a customer record
        $this->browser->submitFormById('ManufacturerEditForm');
        $this->assertRegExp('/' . preg_quote('Der Hersteller wurde erfolgreich gespeichert.') . '/', $this->browser->getContent());
        
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        $this->doTestCustomerRecord($manufacturer);
        
        $this->browser->doFoodCoopShopLogout();
    }

    private function doTestCustomerRecord($manufacturer)
    {
        $customerRecord = $this->Manufacturer->getCustomerRecord($manufacturer);
        $this->assertEquals($manufacturer['Address']['firstname'], $customerRecord['Customer']['firstname']);
        $this->assertEquals($manufacturer['Address']['lastname'], $customerRecord['Customer']['lastname']);
        $this->assertEquals(APP_ON, $customerRecord['Customer']['active']);
    }

    /**
     *
     * @param array $data            
     * @return string
     */
    private function getEditManufacturer($manufacturerId)
    {
        $this->browser->get($this->Slug->getManufacturerEdit($manufacturerId));
        return $this->browser->getContent();
    }

    /**
     *
     * @param array $data            
     * @return string
     */
    private function addManufacturer($data)
    {
        $this->browser->post($this->Slug->getManufacturerAdd(), array(
            'data' => $data
        ));
        return $this->browser->getContent();
    }
}

?>