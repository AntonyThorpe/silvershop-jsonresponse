# Installation and Configuration of Silvershop JSON Response
## Installation
`composer require antonythorpe/silvershop-jsonresponse`

## Configuration
If needed, change the image width in the cart in your `config.yml` (default is 45):
```yaml
SilverShop\Extension\ProductImageExtension\Product_Image:
  cart_image_width: 45
```
