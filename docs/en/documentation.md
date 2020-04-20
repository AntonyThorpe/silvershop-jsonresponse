# Documentation of SilverShop JSON Response

## Example of returned JSON data following adding an item to the Cart
```json
{
    "id": "10",     // the ProductID just added
    "cart": {
        "id": "12",   // the OrderID
        "subTotal": 608,        // subTotal should match to all the items in the cart
        "grandTotal": 699.2,    // should equal subTotal + the total of all modifiers (see below)
        "items": [
            {
                "id": "10",   // Product id
                "internalItemID": "EBV2",
                "title": "Mp3 Player",
                "quantity": 3,
                "unitPrice": 200,
                "href": "electronics/mp3-player/",
                "categories": ["electronics"],
                "addLink": "shoppingcart/add/Product/10?SecurityID=...",
                "removeLink": "shoppingcart/remove/Product/10?SecurityID=...",
                "removeallLink": "shoppingcart/removeall/Product/10?SecurityID=...",
                "setquantityLink": "shoppingcart/setquantity/Product/10?SecurityID=...",
                "image": {
                    "alt": "Mp3 Player",
                    "height": 28,
                    "src": "assets/photos/_resampled/ScaleWidth ... .jpg",
                    "width": 45
                },
                "variations": {
                    "1": {
                        "title": "Size:Large, Color:Red"
                    },
                    "2": {
                        "title": "Size:Small, Color:Red"
                    }
                }
            },
            {
                "id": "12",
                "internalItemID": "BG56",
                "title": "Beach Ball",
                "quantity": 1,
                "unitPrice": 22,
                "href": "toys/beach-ball/",
                "categories": ["toys", "beach"],
                "addLink": "shoppingcart/add/ProductVariation/2?SecurityID=...",
                "removeLink": "shoppingcart/remove/ProductVariation/2?SecurityID=...",
                "removeallLink": "shoppingcart/removeall/ProductVariation/2?SecurityID=...",
                "setquantityLink": "shoppingcart/setquantity/ProductVariation/2?SecurityID=...",
                "image": {
                    "alt": "Beach Ball",
                    "height": 20,
                    "src": "assets/photos/_resampled/ScaleWidth ... .jpg",
                    "width": 45
                },
                "variations": [
                    {
                        "id": "1",
                        "title": "Size:Large, Color:Red"
                    },
                    {
                        "id": "2",
                        "title": "Size:Small, Color:Red"
                    }
                ]
            }
        ],
        "modifiers": [
            {
                "id": "15",
                "tableTitle": "Tax : GST 15%",
                "tableValue": "91.20"
            }
        ]
    },
    "message": {
        "content": "Quantity has been set.",
        "type": "good"
    }
}
```

## Notes on the JSON Example
 * The `addLink`, `removeLink`, `removeallLink`, and `setquantityLink` are the urls that can be later called with ajax
 * Formatting has been stripped from the quantities and values.  It is assumed that CSS can be used to style appropriately and that Order Item totals can be calculated using javascript.
 * There is an extension hook if you have additional Modifier details to extract
 * The `href` is the link to the product's page

## An Additional End-Point
To get a copy of the current shopping cart call `shoppingcart/get`.  This provides:
* OrderID
* Items and Modifications as listed above
* Plus the Subtotal and Grandtotal

## An Ajax Example for the Product Page using Knockoutjs
Add a submit binding handler to the form via `updateForm` on the Product_ControllerExtension class.
```php
namespace Your\Nampspace;

use SilverStripe\ORM\DataExtension;

class Product_ControllerExtension extends DataExtension
{
    public function updateForm(&$form)
    {
        $form->setAttribute("data-bind", "submit: addToCartProductPage");
    }
}
```
In `app/config/config.yml`
```yml
SilverShop\Page\ProductController:
  extensions:
    - Your\Nampspace\Product_ControllerExtension
```
After a `dev/build` this sets the data-bind attribute to capture the submit event:
```html
<form data-bind="submit: addToCartProductPage" id="blah" action="/products/rugby/worldcupjersey/Form" method="post" enctype="application/x-www-form-urlencoded" class="addproductform" role="form">
```
And in javascript capture the click event, extract the data from the formElement and send an ajax message to the server:
```javascipt
this.addToCartProductPage = function(formElement) {
    return $.ajax({
        url: $(formElement).attr('action'),
        method: $(formElement).attr('method'),
        cache: false,
        data: {
            Quantity: self.numeric.peek(),
            SecurityID: $(ko.utils.getFormFields(formElement, 'SecurityID')).val()
        }
    })
    .done(function(data) {
       // update javascript objects
       // notify analytics
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
       // opps
});
```
