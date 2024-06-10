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
class SilvershopVariationsJsonResponseTest extends FunctionalTest
{
    protected static $fixture_file = [
        'vendor/silvershop/core/tests/php/Fixtures/shop.yml',
        'vendor/silvershop/core/tests/php/Fixtures/variations.yml'
    ];

    public $mp3player;
    public $socks;
    public $cart;


    public function setUpOnce(): void
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
        $this->objFromFixture(ProductCategory::class, 'products')->copyVersionToStage('Stage', 'Live');
        $this->objFromFixture(ProductCategory::class, 'clothing')->copyVersionToStage('Stage', 'Live');
        $this->objFromFixture(ProductCategory::class, 'clearance')->copyVersionToStage('Stage', 'Live');

        $this->mp3player->copyVersionToStage('Stage', 'Live');
        $this->socks->copyVersionToStage('Stage', 'Live');

        $this->cart = ShoppingCart::singleton();
        $this->cart->clear();
    }

    public function testVariations(): void
    {
        $ballRoot = $this->objFromFixture(Product::class, 'ball');
        // add parent for categories in JSON response
        $parent = $this->objFromFixture(ProductCategory::class, 'products');
        $ballRoot->ParentID = $parent->ID;
        $ballRoot->write();

        $ballRoot->copyVersionToStage('Stage', 'Live');
        $ball1 = $this->objFromFixture(Variation::class, 'redLarge');
        $ball2 = $this->objFromFixture(Variation::class, 'redSmall');
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
        $this->get(ShoppingCartController::remove_all_item_link($ball1) . "?ajax=1");
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
