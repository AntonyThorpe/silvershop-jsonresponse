# silvershop-jsonresponse
Silvershop submodule that provides JSON responses for cart updates

[![Build Status](https://travis-ci.org/AntonyThorpe/silvershop-jsonresponse.svg?branch=master)](https://travis-ci.org/AntonyThorpe/silvershop-jsonresponse)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AntonyThorpe/silvershop-jsonresponse/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/AntonyThorpe/silvershop-jsonresponse/?branch=master)
![helpfulrobot](https://helpfulrobot.io/antonythorpe/silvershop-jsonresponse/badge)
[![Latest Stable Version](https://poser.pugx.org/antonythorpe/silvershop-jsonresponse/v/stable)](https://packagist.org/packages/antonythorpe/silvershop-jsonresponse)
[![Total Downloads](https://poser.pugx.org/antonythorpe/silvershop-jsonresponse/downloads)](https://packagist.org/packages/antonythorpe/silvershop-jsonresponse)
[![Latest Unstable Version](https://poser.pugx.org/antonythorpe/silvershop-jsonresponse/v/unstable)](https://packagist.org/packages/antonythorpe/silvershop-jsonresponse)
[![License](https://poser.pugx.org/antonythorpe/silvershop-jsonresponse/license)](https://packagist.org/packages/antonythorpe/silvershop-jsonresponse)

Based upon the excellent work of Mark Guinn with [Silvershop Ajax](https://github.com/markguinn/silverstripe-shop-ajax/blob/master/README.md)

## How it works
Utilises the Silvershop extension points to provide a JSON response for cart changes

## Use Case
Enhanced UX utilising a MV* Javascript library

## Features
* Additional endpoint of `shoppingcart/get` to obtain a JSON copy of the cart
* JSON includes urls to `addLink`, `removeLink`, `removeallLink`, and `setquantityLink` for each product item in the cart
* JSON includes image link, height, width, and alt for each item in the cart
* Includes Subtotal and Grandtotal
* Extension points for all endpoints, plus also for Modifiers

## Limitations
Variations or the clear method have not been used in production

## Requirements
* [Silvershop (a Silverstripe module)](https://github.com/silvershop/silvershop-core)

## Documentation
[Index](/docs/en/index.md)

## Support
None sorry

## Contributions
[Link](contributing.md)

## Change Log
[Link](changelog.md)

## License
[MIT](LICENSE)

