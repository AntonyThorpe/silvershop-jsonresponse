<?php

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
     * @param SS_HTTPRequest $request
     * @return SS_HTTPResponse $response with JSON body
     */
    public function get(SS_HTTPRequest $request)
    {
        if (!$request->isAjax()) {
            return $this->owner->httpError(404, _t("ShoppingCart.GETCARTAJAXONLY", "Ajax request only Bo"));
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
     * @param SS_HTTPRequest $request
     * @param AjaxHTTPResponse $response
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

            $data = array(
                'id' => (string) $product->ID,
                'internalItemID' => $product->InternalItemID,
                'title' => $product->Title,
                'url' => $product->URLSegment,
                'categories' => $product->getCategories()->column('Title'),
                'message' => array(
                    'content' => $this->owner->cart->getMessage(),
                    'type' => $this->owner->cart->getMessageType(),
                ),
            );
            $this->owner->cart->clearMessage();

            // add separately as these are absent with variations
            if (method_exists($product, "getPrice")) {
                $data['unitPrice'] = $product->getPrice();
            }
            if (method_exists($product, "addLink")) {
                $data['addLink'] = $product->addLink();
            }
            if (method_exists($product, "removeLink")) {
                $data['removeLink'] = $product->removeLink();
            }
            if (method_exists($product, "removeallLink")) {
                $data['removeallLink'] = $product->removeallLink();
            }
            if (method_exists($product->Item(), "setquantityLink")) {
                $data['setquantityLink'] = $product->Item()->setquantityLink();
            }

            if ($shoppingcart) {
                $data['subTotal'] = $shoppingcart->SubTotal();
                $data['grandTotal'] = $shoppingcart->GrandTotal();
            }

            $this->owner->extend('updateAddResponseShopJsonResponse', $data, $request, $response, $product, $quantity);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Remove one of an item from a cart (Cart Page)
     *
     * @see 'remove' function of ShoppingCart_Controller ($this->owner)
     * @param SS_HTTPRequest $request
     * @param AjaxHTTPResponse $response
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

            $data = array(
                'id' => (string) $product->ID,
                'message' => array(
                    'content' => $this->owner->cart->getMessage(),
                    'type' => $this->owner->cart->getMessageType(),
                ),
            );
            $this->owner->cart->clearMessage();

            if ($shoppingcart) {
                $data['subTotal'] = $shoppingcart->SubTotal();
                $data['grandTotal'] = $shoppingcart->GrandTotal();
            }

            $this->owner->extend('updateRemoveResponseShopJsonResponse', $data, $request, $response, $product, $quantity);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Remove all of an item from a cart (Cart Page)
     * Quantity is NIL
     *
     * @see 'removeall' function of ShoppingCart_Controller ($this->owner)
     * @param SS_HTTPRequest $request
     * @param AjaxHTTPResponse $response
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

            $data = array(
                'id' => (string) $product->ID,
                'message' => array(
                    'content' => $this->owner->cart->getMessage(),
                    'type' => $this->owner->cart->getMessageType(),
                ),
            );
            $this->owner->cart->clearMessage();

            if ($shoppingcart) {
                $data['subTotal'] = $shoppingcart->SubTotal();
                $data['grandTotal'] = $shoppingcart->GrandTotal();
            }

            $this->owner->extend('updateRemoveAllResponseShopJsonResponse', $data, $request, $response, $product);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Update the quantity of an item in a cart (Cart Page)
     *
     * @see 'setquantity' function of ShoppingCart_Controller ($this->owner)
     * @param SS_HTTPRequest $request
     * @param AjaxHTTPResponse $response
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

            $currentquantity = (int) $product->Item()->Quantity; // quantity of the order item left now in the cart

            $data = array(
                'id' => (string) $product->ID,
                'quantity' => $currentquantity,
                'message' => array(
                    'content' => $this->owner->cart->getMessage(),
                    'type' => $this->owner->cart->getMessageType(),
                ),
            );
            $this->owner->cart->clearMessage();

            // include totals if required
            if ($shoppingcart) {
                $data['subTotal'] = $shoppingcart->SubTotal();
                $data['grandTotal'] = $shoppingcart->GrandTotal();
            }

            $this->owner->extend('updateSetQuantityResponseShopJsonResponse', $data, $request, $response, $product, $currentquantity);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Clear all items from the cart (Cart Page)
     *
     * @see 'clear' function of ShoppingCart_Controller ($this->owner)
     * @param SS_HTTPRequest $request
     * @param AjaxHTTPResponse $response
     */
    public function updateClearResponse(&$request, &$response)
    {
        if ($request->isAjax()) {
            if (!$response) {
                $response = $this->owner->getResponse();
            }
            $response->removeHeader('Content-Type');
            $response->addHeader('Content-Type', 'application/json; charset=utf-8');

            $data = array(
                'message' => array(
                    'content' => $this->owner->cart->getMessage(),
                    'type' => $this->owner->cart->getMessageType(),
                ),
            );
            $this->owner->cart->clearMessage();

            $this->owner->extend('updateClearResponseShopJsonResponse', $data, $request, $response);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Update the variations of a product (Cart Page)
     *
     * @see 'addtocart' function of VariationForm ($this->owner)
     * @param SS_HTTPRequest $request
     * @param AjaxHTTPResponse $response
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

            $data = array(
                'id' => (string) $variation->ID,
                'message' => array(
                    'content' => $form->Message(),
                    'type' => $form->MessageType(),
                ),
            );
            $form->clearMessage();

            // include totals if required
            if ($shoppingcart) {
                $data['subTotal'] = $shoppingcart->SubTotal();
                $data['grandTotal'] = $shoppingcart->GrandTotal();
            }

            $this->owner->extend('updateVariationFormResponseShopJsonResponse', $data, $request, $response, $variation, $quantity, $form);
            $response->setBody(json_encode($data));
        }
    }

    /**
     * Add one of an item to a cart (Product Page)
     *
     * @see the addtocart function within AddProductForm class
     * @param SS_HTTPRequest $request
     * @param AjaxHTTPResponse $response
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

            $data = array(
                'id' => (string) $buyable->ID,
                'internalItemID' => $buyable->InternalItemID,
                'title' => $buyable->Title,
                'url' => $buyable->URLSegment,
                'categories' => $buyable->getCategories()->column('Title'),
                'addLink' => $buyable->addLink(),
                'removeLink' => $buyable->removeLink(),
                'removeallLink' => $buyable->removeallLink(),
                'setquantityLink' => $buyable->Item()->setquantityLink(),
                'message' => array(
                    'content' => $form->Message(),
                    'type' => $form->MessageType(),
                ),
            );
            $form->clearMessage();

            // include totals if required
            if ($shoppingcart) {
                $data['subTotal'] = $shoppingcart->SubTotal();
                $data['grandTotal'] = $shoppingcart->GrandTotal();
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

            $result['subTotal'] = $shoppingcart->SubTotal();
            $result['grandTotal'] = $shoppingcart->GrandTotal();
        }
        return $result;
    }

    /**
     * Provide a copy of the current order's items, including image details and variations
     * @todo  what about subTitles?  i.e the variation choosen (I think)
     * @return array
     */
    protected function getCurrentShoppingCartItems()
    {
        $result = array();
        $items = ShoppingCart::curr()->Items();

        if ($items->exists()) {
            foreach ($items->getIterator() as $item) {

                // Definitions
                $data = array();
                $product = $item->Product();

                $data["id"] = (string) $item->ProductID;
                $data["internalItemID"] = $product->InternalItemID;
                $data["title"] = $product->Title;
                $data["quantity"] = (int) $item->Quantity;
                $data["unitPrice"] = $product->getPrice();
                $data["href"] = $item->Link();
                $data['categories'] = $product->getCategories()->column('Title');
                $data["addLink"] = $item->addLink();
                $data["removeLink"] = $item->removeLink();
                $data["removeallLink"] = $item->removeallLink();
                $data["setquantityLink"] = $item->setquantityLink();

                // Image
                if ($image = $item->Image()->ScaleWidth((int) Product_Image::config()->cart_image_width)) {
                    $data["image"] = array(
                        'alt' => $image->Title,
                        'src' => $image->Filename,
                        'width' => $image->Width,
                        'height' => $image->Height,
                    );
                }

                // Variations
                if ($product->has_many("Variations")) {
                    $variations = $product->Variations();
                    if ($variations->exists()) {
                        $data['variations'] = array();
                        foreach ($variations as $variation) {
                            $data['variations'][] = array(
                                'id' => (string) $variation->ID,
                                'title' => $variation->Title,
                            );
                        }
                    }
                }
                $result[] = $data;
            }
        }
        return $result;
    }

    /**
     * Provide a copy of the current order's modifiers
     * @todo Only FlatTaxModifier tested
     * @return array of modifiers (note: this excludes subtotal and grandtotal)
     */
    protected function getCurrentShoppingCartModifiers()
    {
        $result = array();
        $modifiers = ShoppingCart::curr()->Modifiers();

        if ($modifiers->exists()) {
            foreach ($modifiers->sort('Sort')->getIterator() as $modifier) {
                if ($modifier->ShowInTable()) {
                    $data = array(
                        'id' => (string) $modifier->ID,
                        'tableTitle' => $modifier->TableTitle(),
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
        $this->owner->extend('updateGetCurrentShoppingCartModifiers', $result);
        return $result;
    }
}
