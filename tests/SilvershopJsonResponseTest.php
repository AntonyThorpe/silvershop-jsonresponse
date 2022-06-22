<?php

namespace AntonyThorpe\SilverShopJsonResponse\Tests;

use SilverShop\Cart\ShoppingCartController;
use SilverShop\Forms\VariationForm;
use SilverShop\Tests\ShopTest;
use SilverShop\Model\Order;
use SilverShop\Model\Modifiers\Tax\FlatTax;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Variation\Variation;
use SilverStripe\Dev\FunctionalTest;

/**
 * Functional tests of json responses for shopping cart of Silverstripe Shop
 *
 * @package shop
 * @subpackage tests
 */
class SilverShopJsonResponseTest extends FunctionalTest
{
    protected static $fixture_file = 'vendor/silvershop/core/tests/php/Fixtures/shop.yml';

    public function setUpOnce()
    {
        if (!ShoppingCartController::has_extension('SilvershopJsonResponse')) {
            ShoppingCartController::add_extension('SilvershopJsonResponse');
        }
        if (!VariationForm::has_extension('SilvershopJsonResponse')) {
            VariationForm::add_extension('SilvershopJsonResponse');
        }
        parent::setUpOnce();
    }

    public function setUp(): void
    {
        parent::setUp();
        ShopTest::setConfiguration(); //reset config
        Order::config()->modifiers = [ FlatTax::class ];

        // Needed, so that products can be published
        $this->logInWithPermission('ADMIN');

        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->socks = $this->objFromFixture(Product::class, 'socks');

        //publish some product categories and products
        $this->objFromFixture(ProductCategory::class, 'products')->publish('Stage', 'Live');
        $this->objFromFixture(ProductCategory::class, 'clothing')->publish('Stage', 'Live');
        $this->objFromFixture(ProductCategory::class, 'clearance')->publish('Stage', 'Live');

        $this->mp3player->publish('Stage', 'Live');
        $this->socks->publish('Stage', 'Live');

        $this->cart = ShoppingCart::singleton();
        $this->cart->clear();
    }

    public function testGet()
    {
        // add items via url to setup cart
        $this->get(ShoppingCartController::set_quantity_item_link($this->mp3player, array('quantity' => 5)));
        $this->get(ShoppingCartController::add_item_link($this->socks));
        $shoppingcart = ShoppingCart::curr();
        $shoppingcart->calculate();  // recalculate the shopping cart

        $response = $this->get("shoppingcart/get" . "?ajax=1");

        $this->assertStringContainsString(
            'tableTitle',
            $response->getBody(),
            "Json contains tableTitle"
        );
        $this->assertStringContainsString(
            'tableValue',
            $response->getBody(),
            "Json contains tableValue"
        );
        $this->assertStringContainsString(
            'Tax @ 15.0%',
            $response->getBody(),
            "Contains GST modifier in the response"
        );
        $this->assertStringContainsString(
            '"subTotal":1008',
            $response->getBody(),
            "Contains SubTotal of 1008"
        );
        $this->assertStringContainsString(
            '"grandTotal":1159.2',
            $response->getBody(),
            "Contains a GrandTotal of 1159.2; the GST amount (the Flat Tax Modifier) is the difference"
        );
    }

    public function testAddToCart()
    {
        // test ajax request (Product Category page)
        $response = $this->get(ShoppingCartController::add_item_link($this->mp3player) . "?ajax=1");
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "Response status code is 200"
        );
        $this->assertEquals(
            "application/json; charset=utf-8",
            $response->getHeader("Content-Type"),
            "Json response header"
        );
        $this->assertJson(
            $response->getBody(),
            "Contains json in the body of the response"
        );

        $this->assertStringContainsString(
            "addLink",
            $response->getBody(),
            "response contains a link to add additional quantities of an item in the cart"
        );
        $this->assertStringContainsString(
            "removeLink",
            $response->getBody(),
            "response contains a link to reduce the quantity of an item in a cart"
        );
        $this->assertStringContainsString(
            "removeallLink",
            $response->getBody(),
            "response contains a link to remove all of an item from a cart"
        );
        $this->assertStringContainsString(
            "setquantityLink",
            $response->getBody(),
            "response contains a link to set the quantity of an item in a cart"
        );
        $this->assertStringContainsString(
            "unitPrice",
            $response->getBody(),
            "response contains the unit price of items in a cart"
        );
        $this->assertStringContainsString(
            "subTotal",
            $response->getBody(),
            "response contains a subTotal when include_totals is set to true"
        );

        // See what's in the cart
        $items = ShoppingCart::curr()->Items();
        $this->assertNotNull($items);
        $this->assertEquals(
            $items->Count(),
            1,
            'There is 1 item in the cart'
        );
    }

    public function testRemoveFromCart()
    {

        // add items via url to setup
        $this->get(ShoppingCartController::set_quantity_item_link($this->mp3player, array('quantity' => 5)));
        $this->get(ShoppingCartController::add_item_link($this->socks));
        $shoppingcart = ShoppingCart::curr()->calculate();  // recalculate the shopping cart

        $cart = $this->get("shoppingcart/get" . "?ajax=1");
        $this->assertStringContainsString(
            '"title":"Mp3 Player"',
            $cart->getBody(),
            "Contains the mp3 player"
        );
        $this->assertStringContainsString(
            '"title":"Socks"',
            $cart->getBody(),
            "Contains the socks"
        );

        // remove the one of the mp3 players via url making the total 4
        $response = $this->get(ShoppingCartController::remove_item_link($this->mp3player) . "?ajax=1");
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "Response status code is 200"
        );
        $this->assertEquals(
            "application/json; charset=utf-8",
            $response->getHeader("Content-Type"),
            "Json response header"
        );
        $this->assertJson(
            $response->getBody(),
            "Contains json in the body of the response"
        );

        // remove the one of the socks via url making the total NIL so it is fully removed
        $response = $this->get(ShoppingCartController::remove_item_link($this->socks) . "?ajax=1");
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "Response status code is 200"
        );
        $this->assertEquals(
            "application/json; charset=utf-8",
            $response->getHeader("Content-Type"),
            "Json response header"
        );
        $this->assertJson(
            $response->getBody(),
            "Contains json in the body of the response"
        );
        $this->assertNull(
            $this->cart->get($this->socks),
            "socks completely removed"
        );
    }

    public function testRemoveAllFromCart()
    {
        // add items via url to setup
        $this->get(ShoppingCartController::set_quantity_item_link($this->mp3player, array('quantity' => 5)));
        $this->get(ShoppingCartController::add_item_link($this->socks));

        // remove items from cart via url
        $response = $this->get(ShoppingCartController::remove_all_item_link($this->mp3player) . "?ajax=1");
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "Response status code is 200"
        );
        $this->assertEquals(
            "application/json; charset=utf-8",
            $response->getHeader("Content-Type"),
            "Json response header"
        );
        $this->assertJson(
            $response->getBody(),
            "Contains json in the body of the response"
        );
        $this->assertNull(
            $this->cart->get($this->mp3player),
            "Mp3 Players are not in the cart"
        );
    }

    public function testSetQuantityInCart()
    {
        // add items via url to setup
        $this->get(ShoppingCartController::set_quantity_item_link($this->mp3player, array('quantity' => 5)));
        $this->get(ShoppingCartController::add_item_link($this->socks));

        // set items via url
        $response = $this->get(
            ShoppingCartController::set_quantity_item_link($this->mp3player, array('quantity' => 3, 'ajax' => 1))
        );
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "Response status code is 200"
        );
        $this->assertEquals(
            "application/json; charset=utf-8",
            $response->getHeader("Content-Type"),
            "Json response header"
        );
        $this->assertJson(
            $response->getBody(),
            "Contains json in the body of the response"
        );

        $this->assertStringContainsString(
            'Quantity has been set',
            $response->getBody(),
            "Contains a confirmation message that the quantity has been set to a new value"
        );
    }

    public function testClearAllItemsFromTheCart()
    {
        // add items via url to setup
        $this->get(ShoppingCartController::set_quantity_item_link($this->mp3player, array('quantity' => 5)));
        $this->get(ShoppingCartController::add_item_link($this->socks));

        // remove items via url
        $response = $this->get("shoppingcart/clear?ajax=1");
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "Response status code is 200"
        );
        $this->assertEquals(
            "application/json; charset=utf-8",
            $response->getHeader("Content-Type"),
            "Json response header"
        );
        $this->assertJson(
            $response->getBody(),
            "Contains json in the body of the response"
        );

        $this->assertStringContainsString(
            'Cart was successfully cleared',
            $response->getBody(),
            "Contains a message that the cart has been cleared"
        );
    }

    public function testVariations()
    {
        $this->loadFixture('vendor/silvershop/core/tests/php/Fixtures/variations.yml');
        $ballRoot = $this->objFromFixture(Product::class, 'ball');

        // add parent for categories in JSON response
        $parent = $this->objFromFixture(ProductCategory::class, 'products');
        $ballRoot->ParentID = $parent->ID;
        $ballRoot->write();

        $ballRoot->publish('Stage', 'Live');
        $ball1 = $this->objFromFixture(Variation::class, 'redlarge');
        $ball2 = $this->objFromFixture(Variation::class, 'redsmall');
        $this->logInWithPermission('ADMIN');
        $ball1->publishSingle();
        $ball2->publishSingle();

        // Add the two variation items
        $response = $this->get(ShoppingCartController::add_item_link($ball1) . "?ajax=1");
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "Response status code is 200"
        );
        $this->assertEquals(
            "application/json; charset=utf-8",
            $response->getHeader("Content-Type"),
            "Json response header"
        );
        $this->assertJson(
            $response->getBody(),
            "Contains json in the body of the response"
        );
        $this->assertStringContainsString(
            "Size:Large, Color:Red",
            $response->getBody(),
            "Contains json in the body of the cart id"
        );

        $response = $this->get(ShoppingCartController::add_item_link($ball2) . "?ajax=1");
        $items = ShoppingCart::curr()->Items();
        $this->assertEquals(
            $items->Count(),
            2,
            "There are 2 items in the cart"
        );

        // Remove one and see what happens
        $response = $this->get(ShoppingCartController::remove_all_item_link($ball1) . "?ajax=1");
        $this->assertEquals(
            $items->Count(),
            1,
            "There is 1 item in the cart"
        );
        $this->assertNull(
            $this->cart->get($ball1),
            "first item not in cart"
        );
        $this->assertNotNull(
            $this->cart->get($ball2),
            "second item is still in cart"
        );
    }
}
