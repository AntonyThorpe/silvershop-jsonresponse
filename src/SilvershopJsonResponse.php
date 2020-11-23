<?php

namespace AntonyThorpe\SilverShopJsonResponse;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Page\Product;
use SilverShop\Extension\ProductImageExtension;
use SilverShop\Forms\AddProductForm;
use SilverShop\Forms\VariationForm;
use SilverShop\Model\Buyable;
use SilverShop\Model\Variation\Variation;
use SilverStripe\Core\Extension;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * ShopJsonResponse
 *
 * Json Response for shopping cart of Silverstripe Shop
 * @package shop
 */
class SilvershopJsonResponse extends Extension
{
    /**
     * Allow get action to obtain a copy of the shopping cart
     */
    private static $allowed_actions = array(
        'get'
    );

    /**
     * get the shopping cart
     *
     * @param HTTPRequest $request
     * @return HTTPResponse $response with JSON body
     */
    public function get(HTTPRequest $request)
    {
        if (!$request->isAjax()) {
            return $this->owner->httpError(404, _t(ShoppingCart::class . 'GetCartAjaxOnly', 'Ajax request only Bo'));
        }
        $response = $this->owner->getResponse();
        $response->removeHeader('Content-Type');
        $response->addHeader('Content-Type', 'application/json; charset=utf-8');

        $data = $this->getCurrentShoppingCart();

        $this->owner->extend('updateGet', $data, $request, $response);
        return $response->setBody(json_encode($data));
    }

    /**
     * Add one of an item to a cart (Category Page)
     *
     * @see 'add' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     * @param Buyable $product [optional]
     * @param int $quantity [optional]
     */
    public function updateAddResponse(&$request, &$response, $product = null, $quantity = 1)
    {
        if ($request->isAjax()) {
            if (!$response) {
                $response = $this->owner->getResponse();
            }
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $this->owner->cart->getMessage(),
                'type' => $this->owner->cart->getMessageType()
            ];
            $this->owner->cart->clearMessage();

            $this->owner->extend('updateAddResponseShopJsonResponse', $data, $request, $response, $product, $quantity);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Remove one of an item from a cart (Cart Page)
     *
     * @see 'remove' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     * @param Buyable $product [optional]
     * @param int $quantity [optional]
     */
    public function updateRemoveResponse(&$request, &$response, $product = null, $quantity = 1)
    {
        if ($request->isAjax()) {
            if (!$response) {
                $response = $this->owner->getResponse();
            }
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $this->owner->cart->getMessage(),
                'type' => $this->owner->cart->getMessageType()
            ];
            $this->owner->cart->clearMessage();

            $this->owner->extend('updateRemoveResponseShopJsonResponse', $data, $request, $response, $product, $quantity);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Remove all of an item from a cart (Cart Page)
     * Quantity is NIL
     *
     * @see 'removeall' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     * @param Buyable $product [optional]
     */
    public function updateRemoveAllResponse(&$request, &$response, $product = null)
    {
        if ($request->isAjax()) {
            if (!$response) {
                $response = $this->owner->getResponse();
            }
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $this->owner->cart->getMessage(),
                'type' => $this->owner->cart->getMessageType()
            ];
            $this->owner->cart->clearMessage();

            $this->owner->extend('updateRemoveAllResponseShopJsonResponse', $data, $request, $response, $product);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Update the quantity of an item in a cart (Cart Page)
     *
     * @see 'setquantity' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     * @param Buyable $product [optional]
     * @param int $quantity [optional]
     */
    public function updateSetQuantityResponse(&$request, &$response, $product = null, $quantity = 1)
    {
        if ($request->isAjax()) {
            if (!$response) {
                $response = $this->owner->getResponse();
            }
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $this->owner->cart->getMessage(),
                'type' => $this->owner->cart->getMessageType()
            ];
            $this->owner->cart->clearMessage();

            $this->owner->extend('updateSetQuantityResponseShopJsonResponse', $data, $request, $response, $product, $quantity);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Clear all items from the cart (Cart Page)
     *
     * @see 'clear' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    public function updateClearResponse(&$request, &$response)
    {
        if ($request->isAjax()) {
            if (!$response) {
                $response = $this->owner->getResponse();
            }
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $this->owner->cart->getMessage(),
                'type' => $this->owner->cart->getMessageType()
            ];
            $this->owner->cart->clearMessage();

            $this->owner->extend('updateClearResponseShopJsonResponse', $data, $request, $response);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Update the variations of a product (Cart Page)
     *
     * @see 'addtocart' function of VariationForm ($this->owner)
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     * @param Buyable $variation [optional]
     * @param int $quantity [optional]
     * @param VariationForm $form [optional]
     */
    public function updateVariationFormResponse(&$request, &$response, $variation = null, $quantity = 1, $form = null)
    {
        if ($request->isAjax()) {
            if (!$response) {
                $response = $this->owner->getResponse();
            }
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            if ($form) {
                $data['message'] = [
                    'content' => $form->getMessage(),
                    'type' => $form->getMessageType()
                ];
                $form->clearMessage();
            }


            $this->owner->extend('updateVariationFormResponseShopJsonResponse', $data, $request, $response, $variation, $quantity, $form);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Add one of an item to a cart (Product Page)
     *
     * @see the addtocart function within AddProductForm class
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     * @param Buyable $buyable [optional]
     * @param int $quantity [optional]
     * @param AddProductForm $form [optional]
     */
    public function updateAddProductFormResponse(&$request, &$response, $buyable, $quantity, $form)
    {
        if ($request->isAjax()) {
            if (!$response) {
                $response = $this->owner->getController()->getResponse();
            }
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            if ($form) {
                $data['message'] = [
                    'content' => $form->getMessage(),
                    'type' => $form->getMessageType()
                ];
                $form->clearMessage();
            }

            $this->owner->extend('updateAddProductFormResponseShopJsonResponse', $data, $request, $response, $buyable, $quantity, $form);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Provide a copy of the current order in the required format
     * Note the id is the cart's id
     * @return array of product id, subTotal, grandTotal, and items & modifiers
     */
    public function getCurrentShoppingCart()
    {
        $result = [];

        if ($shoppingcart = ShoppingCart::curr()) {
            $result['id'] = (string) $shoppingcart->getReference();

            if ($items = $this->getCurrentShoppingCartItems()) {
                $result['items'] = $items;
            }

            if ($modifiers = $this->getCurrentShoppingCartModifiers()) {
                $result['modifiers'] = $modifiers;
            }

            if ($shoppingcart->SubTotal()) {
                $result['subTotal'] = $shoppingcart->SubTotal();
                $result['grandTotal'] = $shoppingcart->GrandTotal();
            }
        }
        return $result;
    }

    /**
     * Provide a copy of the current order's items, including image details and variations
     * @return array
     */
    protected function getCurrentShoppingCartItems()
    {
        $result = [];
        $shoppingcart = ShoppingCart::curr();

        if ($shoppingcart->Items()->exists()) {
            foreach ($shoppingcart->Items()->getIterator() as $item) {
                // Definitions
                $data = [];
                $product = $item->Product();

                $data["id"] = (string) $item->ProductID;
                $data["internalItemID"] = $product->InternalItemID;
                $data["title"] = $product->getTitle();
                $data["quantity"] = (int) $item->Quantity;
                $data["unitPrice"] = $product->getPrice();
                $data["href"] = $item->Link();
                $data['categories'] = $product->getCategories()->column('Title');
                $data["addLink"] = $item->addLink();
                $data["removeLink"] = $item->removeLink();
                $data["removeallLink"] = $item->removeallLink();
                $data["setquantityLink"] = $item->setquantityLink();

                // Image
                if ($item->Image()) {
                    $image = $item->Image()->ScaleWidth((int) ProductImageExtension::config()->cart_image_width);
                    $data["image"] = array(
                        'alt' => $image->getTitle(),
                        'src' => $image->getAbsoluteURL(),
                        'width' => $image->getWidth(),
                        'height' => $image->getHeight(),
                    );
                }

                // Variations
                if ($subtitle = $item->SubTitle()) {
                    $data['subtitle'] = $subtitle;
                }

                $result[] = $data;
            }
        }
        return $result;
    }

    /**
     * Provide a copy of the current order's modifiers
     * @return array of modifiers (note: this excludes subtotal and grandtotal)
     */
    protected function getCurrentShoppingCartModifiers()
    {
        $result = [];
        $shoppingcart = ShoppingCart::curr();

        if ($shoppingcart->Modifiers()->exists()) {
            $modifiers = $shoppingcart->Modifiers();
            foreach ($modifiers->sort('Sort')->getIterator() as $modifier) {
                if ($modifier->ShowInTable()) {
                    $data = array(
                        'id' => (string) $modifier->ID,
                        'tableTitle' => $modifier->getTableTitle(),
                        'tableValue' => (float) $modifier->TableValue(),
                    );

                    if (method_exists($modifier, 'Link')) {
                        // add if there is a link
                        $data["href"] = $modifier->Link();
                    }

                    if (method_exists($modifier, 'removeLink')) {
                        // add if there is a canRemove method
                        $data["removeLink"] = $modifier->removeLink();
                    }

                    $result[] = $data;
                }
            }
        }
        return $result;
    }
}
