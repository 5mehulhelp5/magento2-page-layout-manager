# Adobe Commerce / Magento 2 Performance optimisation module for avoid entity specific page cache bloat

This module provides configurable control over entity-specific page layout caching to prevent cache bloat in big stores.

## Problem

In Adobe Commerce / Magento 2, when `addPageLayoutHandles()` is called with `entitySpecific = true`, it creates
individual cache entries for each entity id or sku (catalog_category_view_id_123.xml or catalog_product_view_id_123.xml or catalog_product_view_sku_test.xml).
In large store setups, this can lead to massive cache growth.

## Solution

This module provides two possible configuration:
 1. Allow Entity-Specific Layout Handles (default: false) - when disabled, blocks all entity-specific layouts like `*_id_*.xml`
 2. Use Only Specific Validators (default: true) - When enabled, the entity-specific layout will be added only for validated requests

### Composer Installation

```bash
composer require hryvinskyi/magento2-page-layout-manager
php bin/magento module:enable Hryvinskyi_PageLayoutManager
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

### Manual Installation

1. Create directory `app/code/Hryvinskyi/PageLayoutManager`
2. Download and extract module contents to this directory
3. Enable the module:

```bash
bin/magento module:enable Hryvinskyi_PageLayoutManager
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

## Usage

### Creating Custom Validators

#### 1. Implement the Interface

```php
<?php
namespace MyModule\Model;

use Hryvinskyi\PageLayoutManager\Api\RequestValidatorInterface;

class MyCustomValidator implements RequestValidatorInterface
{
    public function isRequestAllowed(
        array $parameters = [],
        ?string $defaultHandle = null,
        array $context = []
    ): bool {
        // Your custom logic here
        // Return true to allow entity-specific caching
        // Return false to block it

        // Example: Allow only specific product pages
        if ($defaultHandle === 'catalog_product_view') {
            $productId = $context['product_id'] ?? null;
            return in_array($productId, [1, 2, 3]); // Only these products
        }

        return false;
    }
}
```

#### 2. Register in DI Configuration

```xml
<!-- etc/di.xml -->
<type name="Hryvinskyi\PageLayoutManager\Model\Strategy\AllowListStrategy">
    <arguments>
        <argument name="requestValidators" xsi:type="array">
            <item name="my_custom_validator" xsi:type="object">MyModule\Model\MyCustomValidator</item>
        </argument>
    </arguments>
</type>
```

### Available Context Data

The `$context` array contains:
- `entity_specific`: boolean indicating if this is an entity-specific request
- Additional data can be passed by the calling code

### Available Parameters

The `$parameters` array contains the same data passed to `addPageLayoutHandles()`:
- Layout handle parameters like `['type' => 'some_type', 'id' => 123]`


## Contributing

Contributions are welcome! Please ensure:
- All tests pass
- New features include tests
- Code follows SOLID principles
- Documentation is updated

## License

Copyright © 2025 Volodymyr Hryvinskyi. All rights reserved.