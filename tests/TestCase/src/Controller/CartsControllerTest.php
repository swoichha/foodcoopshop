<?php

/**
 * CartsControllerTest
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
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class CartsControllerTest extends AppCakeTestCase
{

    // artischocke, 0,5 € deposit
    public $productId1 = '346';
    // milk with attribute 0,5, 0,5 € deposit
    public $productId2 = '60-10';
    // knoblauch, 0% tax
    public $productId3 = '344';
    public $Cart;

    public $Product;

    public $Order;

    public $StockAvailable;

    public $EmailLog;

    public function setUp()
    {
        parent::setUp();
        $this->Cart = TableRegistry::get('Carts');
        $this->Product = TableRegistry::get('Products');
        $this->Order = TableRegistry::get('Orders');
        $this->StockAvailable = TableRegistry::get('StockAvailables');
        $this->EmailLog = TableRegistry::get('EmailLogs');
    }

    public function testAddLoggedOut()
    {
        $this->addProductToCart($this->productId1, 2);
        $this->assertJsonAccessRestricted();
        $this->assertJsonError();
    }

    public function testAddWrongProductId1()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart(8787, 2);
        $this->assertRegExpWithUnquotedString('Das Produkt mit der ID 8787 ist nicht vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddWrongProductId2()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart('test', 2);
        $this->assertRegExpWithUnquotedString('Das Produkt mit der ID test ist nicht vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddWrongAmount()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 100);
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl "100" ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testRemoveProduct()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 2);
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals([], $cart['CartProducts'], 'cart must be empty');
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $this->assertRegExpWithUnquotedString('Produkt 346 war nicht in Warenkorb vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testCartLoggedIn()
    {
        
        // manufacturer status needs to be changed as well, therefore use a superadmin account for both shopping and changing manufacturer data
        $this->loginAsSuperadmin();

        /**
         * START add product
         */
        $amount1 = 2;
        $this->addProductToCart($this->productId1, $amount1);
        $this->assertJsonOk();

        // check if product was placed in cart
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($this->productId1, $cart['CartProducts'][0]['productId'], 'product id not found in cart');
        $this->assertEquals($amount1, $cart['CartProducts'][0]['amount'], 'amount not found in cart or amount wrong');

        // try to add an amount that is not available any more
        $this->addTooManyProducts($this->productId1, 99, $amount1, 'Die gewünschte Anzahl (101) des Produktes "Artischocke" ist leider nicht mehr verfügbar. Verfügbare Menge: 98', 0);

        /**
         * START add product with attribute
         */
        $amount2 = 3;
        $this->addProductToCart($this->productId2, $amount2);
        $this->assertJsonOk();

        // check if product was placed in cart
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($this->productId2, $cart['CartProducts'][1]['productId'], 'product id not found in cart');
        $this->assertEquals($amount2, $cart['CartProducts'][1]['amount'], 'amount not found in cart or amount wrong');

        // try to add an amount that is not available any more
        $this->addTooManyProducts($this->productId2, 48, $amount2, 'Die gewünschte Anzahl (51) der Variante "0,5l" des Produktes "Milch" ist leider nicht mehr verfügbar. Verfügbare Menge: 20', 1);

        /**
         * START add product with zero tax
         */
        $amount3 = 1;
        $this->addProductToCart($this->productId3, $amount3);
        $this->assertJsonOk();

        // cake cart status check BEFORE finish
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($cart['Cart']['status'], 1, 'cake cart status wrong');

        $this->assertEquals($cart['Cart']['id_cart'], 1, 'cake cart id wrong');

        /**
         * START finish cart
         */
        // START test if PRODUCT that was deactivated during shopping process
        $this->changeProductStatus($this->productId1, APP_OFF);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Das Produkt (.*) ist leider nicht mehr aktiviert und somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeProductStatus($this->productId1, APP_ON);

        // START test if MANUFACTURER was deactivated during shopping process
        $manufacturerId = 5;
        $this->changeManufacturerStatus($manufacturerId, APP_OFF);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Der Hersteller des Produktes (.*) hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeManufacturerStatus($manufacturerId, APP_ON);

        // START test if MANUFACTURER's holiday mode was activated during shopping process
        $manufacturerId = 5;
        $this->changeManufacturerHolidayMode($manufacturerId, date('Y-m-d'));
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Der Hersteller des Produktes (.*) hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeManufacturerHolidayMode($manufacturerId, null);

        // START test if stock available for PRODUCT has gone down (eg. by another order)
        $this->changeStockAvailable($this->productId1, 1);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Anzahl \(2\) des Produktes (.*) ist leider nicht mehr (.*) Menge: 1/', $this->browser->getContent()); // ü needs to be escaped properly
        $this->changeStockAvailable($this->productId1, 98); // reset to old stock available

        // START test if stock available for ATTRIBUTE has gone down (eg. by another order)
        $this->changeStockAvailable($this->productId2, 1);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Anzahl \(3\) der Variante (.*) des Produktes (.*) ist leider nicht mehr (.*) Menge: 1/', $this->browser->getContent()); // ü needs to be escaped properly
        $this->changeStockAvailable($this->productId2, 20); // reset to old stock available

        // FINALLY order can be finished

        // 1) do not check legal checkboxes
        $this->finishCart(0, 0);
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die AGB.', $this->browser->getContent(), 'checkbox validation general_terms_and_conditions_accepted did not work');
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.', $this->browser->getContent(), 'checkbox validation cancellation_terms_accepted did not work');

        // 2) add order comment
        $this->finishCart(1, 1, 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, adfasfd sa');
        $this->assertRegExpWithUnquotedString('Bitte gib maximal 500 Zeichen ein.', $this->browser->getContent(), 'order comment validation did not work');

        // 3) check the checkboxes and add a valid order comment
        $orderComment = 'this is a valid order comment';
        $this->finishCart(1, 1, $orderComment);
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        $this->checkCartStatusAfterFinish();

        /**
         * START check order
         */
        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'OrderDetails.OrderDetailTaxes'
            ]
        ])->first();
        
        $this->assertNotEquals([], $order, 'order not correct');
        $this->assertEquals($order->id_order, $orderId, 'order id not correct');
        $this->assertEquals($order->id_customer, $this->browser->getLoggedUserId(), 'order customer_id not correct');
        $this->assertEquals($order->id_cart, 1, 'order cart_id not correct');
        $this->assertEquals($order->current_state, 3, 'order current_state not correct');
        $this->assertEquals($order->total_deposit, 2.5, 'order total_deposit not correct');
        $this->assertEquals($order->total_paid_tax_excl, 5.578515, 'order total_paid_tax_excl not correct');
        $this->assertEquals($order->total_paid_tax_incl, 6.136364, 'order total_paid_tax_incl not correct');
        $this->assertEquals($order->general_terms_and_conditions_accepted, 1, 'order general_terms_and_conditions_accepted not correct');
        $this->assertEquals($order->cancellation_terms_accepted, 1, 'order cancellation_terms_accepted not correct');
        $this->assertEquals($order->comment, $orderComment, 'order comment not correct');

        // check order_details for product1
        $this->checkOrderDetails($order->order_details[0], 'Artischocke : Stück', 2, 0, 1, 3.305786, 3.305786, 3.64, 0.17, 0.34, 2);

        // check order_details for product2 (third! index)
        $this->checkOrderDetails($order->order_details[2], 'Milch : 0,5l', 3, 10, 1.5, 1.636365, 1.636365, 1.86, 0.07, 0.21, 3);

        // check order_details for product3 (second! index)
        $this->checkOrderDetails($order->order_details[1], 'Knoblauch : 100 g', 1, 0, 0, 0.636364, 0.636364, 0.636364, 0.000000, 0.000000, 0);

        $this->checkStockAvailable($this->productId1, 96);
        $this->checkStockAvailable($this->productId2, 17);
        $this->checkStockAvailable($this->productId3, 77);

        // check new (empty) cart
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($cart['Cart']['id_cart'], 2, 'cake cart id wrong');
        $this->assertEquals([], $cart['CartProducts'], 'cake cart products not empty');

        // check email to customer
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs(
            $emailLogs[0],
            'Bestellbestätigung',
            [
                'Artischocke : Stück',
                'Hallo Demo Superadmin,',
                'Content-Disposition: attachment; filename="Informationen-ueber-Ruecktrittsrecht-und-Ruecktrittsformular.pdf"',
                'Content-Disposition: attachment; filename="Bestelluebersicht.pdf"',
                'Content-Disposition: attachment; filename="Allgemeine-Geschaeftsbedingungen.pdf"'
            ],
            [
                Configure::read('test.loginEmailSuperadmin')
            ]
        );

        $this->browser->doFoodCoopShopLogout();
    }

    public function testShopOrder()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $testCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => Configure::read('test.customerId')
            ]
        ])->first();
        $responseHtml = $this->browser->get($this->Slug->getOrdersList().'/initShopOrder/' . Configure::read('test.customerId'));
        $this->assertRegExp('/Diese Bestellung wird für \<b\>' . $testCustomer['Customers']['name'] . '\<\/b\> getätigt./', $responseHtml);
        $this->assertUrl($this->browser->getUrl(), $this->browser->baseUrl . '/', 'redirect did not work');
    }

    /**
     * cart products should never have the amount 0
     * with a bit of hacking it would be possible, check here that if that happens,
     * finishing the cart does not break the order
     */
    public function testOrderIfAmountOfOneProductIsNull()
    {
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId1, 1);
        $this->addProductToCart($this->productId1, - 1);
        $this->addProductToCart($this->productId2, 1);
        $this->finishCart();
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());
        $this->assertTrue(is_int($orderId), 'order not finished correctly');

        $this->checkCartStatusAfterFinish();

        // only one order with the cake cart id should have been created
        $orders = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_cart' => 1
            ],
            'contain' => [
                'OrderDetails'
            ]
        ]);
        $this->assertEquals(1, $orders->count(), 'more than one order inserted');
        
        foreach ($orders as $order) {
            foreach($order->order_details as $orderDetail) {
                $this->assertFalse($orderDetail->product_quantity == 0, 'product quantity must not be 0!');
            }
        }
    }

    /**
     * cake cart status check AFTER finish
     * as cart is finished, a new cart is already existing
     */
    private function checkCartStatusAfterFinish()
    {
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => 1
            ]
        ])->first();
        $this->assertEquals($cart['Cart']['status'], 0, 'cake cart status wrong');
    }

    private function addTooManyProducts($productId, $amount, $expectedAmount, $expectedErrorMessage, $productIndex)
    {
        $this->addProductToCart($productId, $amount);
        $response = $this->browser->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertEquals($productId, $response->productId);
        $this->assertJsonError();
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($expectedAmount, $cart['CartProducts'][$productIndex]['amount'], 'amount not found in cart or wrong');
    }

    private function checkValidationError()
    {
        $this->assertRegExp('/initCartErrors()/', $this->browser->getContent());
    }

    private function changeStockAvailable($productId, $amount)
    {
        $this->Product->changeQuantity([[$productId => $amount]]);
    }

    private function checkStockAvailable($productId, $result)
    {
        $ids = $this->Product->getProductIdAndAttributeId($productId);

        // get changed product
        $stockAvailable = $this->StockAvailable->find('all', [
            'conditions' => [
                'StockAvailables.id_product' => $ids['productId'],
                'StockAvailables.id_product_attribute' => $ids['attributeId']
            ]
        ])->first();

        // stock available check of changed product
        $this->assertEquals($stockAvailable->quantity, $result, 'stockavailable quantity wrong');
    }

    private function checkOrderDetails($orderDetail, $name, $quantity, $productAttributeId, $deposit, $productPrice, $totalPriceTaxExcl, $totalPriceTaxIncl, $taxUnitAmount, $taxTotalAmount, $taxId)
    {

        // check order_details
        $this->assertEquals($orderDetail->product_name, $name, '%s order_detail product name was not correct');
        $this->assertEquals($orderDetail->product_quantity, $quantity, 'order_detail quantity was not correct');
        $this->assertEquals($orderDetail->product_attribute_id, $productAttributeId, 'order_detail product_attribute_id was not correct');
        $this->assertEquals($orderDetail->deposit, $deposit, 'order_detail deposit was not correct');
        $this->assertEquals($orderDetail->product_price, $productPrice, 'order_detail product_price was not correct');
        $this->assertEquals($orderDetail->total_price_tax_excl, $totalPriceTaxExcl, 'order_detail total_price_tax_excl not correct');
        $this->assertEquals($orderDetail->total_price_tax_incl, $totalPriceTaxIncl, 'order_detail total_price_tax_incl not correct');
        $this->assertEquals($orderDetail->id_tax, $taxId, 'order_detail id_tax not correct');

        // check order_details_tax
        $this->assertEquals($orderDetail->order_detail_tax->unit_amount, $taxUnitAmount, 'order_detail tax unit amount not correct');
        $this->assertEquals($orderDetail->order_detail_tax->total_amount, $taxTotalAmount, 'order_detail tax total amount not correct');
    }
    

    /**
     * @param int $productId
     * @param int $amount
     * @return json string
     */
    private function changeProductStatus($productId, $status)
    {
        $this->Product->changeStatus([[$productId => $status]]);
    }

    private function changeManufacturerStatus($manufacturerId, $status)
    {
        $this->changeManufacturer($manufacturerId, 'active', $status);
    }

    /**
     * @param int $productId
     * @return json string
     */
    private function removeProduct($productId)
    {
        $this->browser->ajaxPost('/warenkorb/ajaxRemove', [
            'productId' => $productId
        ]);
        return $this->browser->getJsonDecodedContent();
    }
}
