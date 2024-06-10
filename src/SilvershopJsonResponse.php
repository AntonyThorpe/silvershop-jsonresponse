<?php

namespace AntonyThorpe\SilverShopJsonResponse;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Extension\ProductImageExtension;
use SilverShop\Forms\AddProductForm;
use SilverShop\Forms\VariationForm;
use SilverShop\Model\Buyable;
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
    private static array $allowed_actions = ['get'];

    /**
     * get the shopping cart
     *
     * @return HTTPResponse $response with JSON body
     */
    public function get(HTTPRequest $request)
    {
        if (!$request->isAjax()) {
            return $this->getOwner()->httpError(404, _t(ShoppingCart::class . 'GetCartAjaxOnly', 'Ajax request only Bo'));
        }
        $response = $this->getOwner()->getResponse();
        $response->removeHeader('Content-Type');
        $response->addHeader('Content-Type', 'application/json; charset=utf-8');
        $data = $this->getCurrentShoppingCart();
        $this->getOwner()->extend('updateGet', $data, $request, $response);
        return $response->setBody(json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG));
    }

    /**
     * Add one of an item to a cart (Category Page)
     *
     * @see 'add' function of SilverShop\Cart\ShoppingCartController ($this->getOwner)
     * @param HTTPRequest $request
     * @param string $response
     * @param Buyable $product
     * @param int $quantity
     */
    public function updateAddResponse(&$request, &$response, $product = null, $quantity = 1): void
    {
        if ($request->isAjax()) {
            $responseMessage = $response;
            $response = $this->getOwner()->getResponse();
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $responseMessage,
                'type' => $this->getOwner()->cart->getMessageType()
            ];
            $this->getOwner()->cart->clearMessage();

            $this->getOwner()->extend('updateAddResponseShopJsonResponse', $data, $request, $response, $product, $quantity);
            $response->setBody(json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG));
        }
    }

    /**
     * Remove one of an item from a cart (Cart Page)
     *
     * @see 'remove' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param string $response
     * @param Buyable $product
     * @param int $quantity
     */
    public function updateRemoveResponse(&$request, &$response, $product = null, $quantity = 1): void
    {
        if ($request->isAjax()) {
            $responseMessage = $response;
            $response = $this->getOwner()->getResponse();
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $responseMessage,
                'type' => $this->getOwner()->cart->getMessageType()
            ];
            $this->getOwner()->cart->clearMessage();

            $this->getOwner()->extend('updateRemoveResponseShopJsonResponse', $data, $request, $response, $product, $quantity);
            $response->setBody(json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG));
        }
    }

    /**
     * Remove all of an item from a cart (Cart Page)
     * Quantity is NIL
     *
     * @see 'removeall' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param string $response
     * @param Buyable $product
     */
    public function updateRemoveAllResponse(&$request, &$response, $product = null): void
    {
        if ($request->isAjax()) {
            $responseMessage = $response;
            $response = $this->getOwner()->getResponse();
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $responseMessage,
                'type' => $this->getOwner()->cart->getMessageType()
            ];
            $this->getOwner()->cart->clearMessage();

            $this->getOwner()->extend('updateRemoveAllResponseShopJsonResponse', $data, $request, $response, $product);
            $response->setBody(json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG));
        }
    }

    /**
     * Update the quantity of an item in a cart (Cart Page)
     *
     * @see 'setquantity' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param string $response
     * @param Buyable $product
     * @param int $quantity
     */
    public function updateSetQuantityResponse(&$request, &$response, $product = null, $quantity = 1): void
    {
        if ($request->isAjax()) {
            $response = $this->getOwner()->getResponse();
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');
            $shoppingcart = ShoppingCart::curr();
            $shoppingcart->calculate(); // recalculate the shopping cart

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $this->getOwner()->cart->getMessage(),
                'type' => $this->getOwner()->cart->getMessageType()
            ];
            $this->getOwner()->cart->clearMessage();

            $this->getOwner()->extend('updateSetQuantityResponseShopJsonResponse', $data, $request, $response, $product, $quantity);
            $response->setBody(json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG));
        }
    }

    /**
     * Clear all items from the cart (Cart Page)
     *
     * @see 'clear' function of ShoppingCart_Controller ($this->owner)
     * @param HTTPRequest $request
     * @param string $response
     */
    public function updateClearResponse(&$request, &$response): void
    {
        if ($request->isAjax()) {
            $response = $this->getOwner()->getResponse();
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');

            $data = $this->getCurrentShoppingCart();
            $data['message'] = [
                'content' => $this->getOwner()->cart->getMessage(),
                'type' => $this->getOwner()->cart->getMessageType()
            ];
            $this->getOwner()->cart->clearMessage();

            $this->getOwner()->extend('updateClearResponseShopJsonResponse', $data, $request, $response);
            $response->setBody(json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG));
        }
    }

    /**
     * Update the variations of a product (Cart Page)
     *
     * @see 'addtocart' function of VariationForm ($this->owner)
     * @param HTTPRequest $request
     * @param string $response
     * @param Buyable $variation
     * @param int $quantity
     * @param VariationForm $form
     */
    public function updateVariationFormResponse(&$request, &$response, $variation = null, $quantity = 1, $form = null): void
    {
        if ($request->isAjax()) {
            $response = $this->getOwner()->getResponse();
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

            $this->getOwner()->extend('updateVariationFormResponseShopJsonResponse', $data, $request, $response, $variation, $quantity, $form);
            $response->setBody(json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG));
        }
    }

    /**
     * Add one of an item to a cart (Product Page)
     *
     * @see the addtocart function within AddProductForm class
     * @param HTTPRequest $request
     * @param string $response
     * @param Buyable $buyable
     * @param int $quantity
     * @param AddProductForm $form
     */
    public function updateAddProductFormResponse(&$request, &$response, $buyable, $quantity, $form): void
    {
        if ($request->isAjax()) {
            $response = $this->getOwner()->getController()->getResponse();
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

            $this->getOwner()->extend('updateAddProductFormResponseShopJsonResponse', $data, $request, $response, $buyable, $quantity, $form);
            $response->setBody(json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG));
        }
    }

    /**
     * Provide a copy of the current order in the required format
     * Note the id is the cart's id
     * @return array of product id, subTotal, grandTotal, and items & modifiers
     */
    public function getCurrentShoppingCart(): array
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
     */
    protected function getCurrentShoppingCartItems(): array
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
                    $data["image"] = ['alt' => $image->getTitle(), 'src' => $image->getAbsoluteURL(), 'width' => $image->getWidth(), 'height' => $image->getHeight()];
                }

                // Variations
                if (method_exists($item, 'SubTitle')) {
                    $data['subtitle'] = $item->SubTitle();
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
    protected function getCurrentShoppingCartModifiers(): array
    {
        $result = [];
        $shoppingcart = ShoppingCart::curr();

        if ($shoppingcart->Modifiers()->exists()) {
            $modifiers = $shoppingcart->Modifiers();
            foreach ($modifiers->sort('Sort')->getIterator() as $modifier) {
                if ($modifier->ShowInTable()) {
                    $data = ['id' => (string) $modifier->ID, 'tableTitle' => $modifier->getTableTitle(), 'tableValue' => (float) $modifier->TableValue()];

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
