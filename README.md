# Electronic Invoicing Attributes for Magento 2

A Magento 2 module that adds customer and order attributes for electronic invoicing compliance with EN16931, XRechnung, and ZUGFeRD standards.

## Overview

This module extends Magento 2 with essential fields for B2B and B2G electronic invoicing in Europe. It enables customers to store their buyer reference (Leitweg-ID) and company registration number, which are required for generating compliant electronic invoices.

**Perfect for:**
- German public sector invoicing (XRechnung with Leitweg-ID)
- B2B invoicing with EN16931 compliance
- ZUGFeRD/Factur-X invoice generation

## Features

### Customer Attributes
- **Buyer Reference** (`buyer_reference`) - Stores the buyer's routing identifier (Leitweg-ID, cost center, etc.) - Maps to EN16931 BT-10
- **Company Registration** (`buyer_registration`) - Stores the legal registration identifier (HRB, SIREN, etc.) - Maps to EN16931 BT-47

### Order Extension Attributes
- `einvoicing_buyer_reference` - Captured from customer at order placement
- `einvoicing_project_reference` - Optional project reference for invoicing

### Admin Integration
- Customer form fieldset "E-Invoicing" in admin customer edit
- Attributes visible and editable by administrators

### Frontend Integration
- Customer account edit form with E-Invoicing fieldset
- **Dual theme support:** Works with both Luma and Hyva themes out of the box
- Automatic form validation

## Requirements

- Magento 2.4.x (Open Source or Commerce)
- PHP 8.1 or higher

## Installation

### Via Composer (recommended)

```bash
composer require geissweb/module-electronic-invoicing-attributes
bin/magento module:enable Geissweb_ElectronicInvoicingAttributes
bin/magento setup:upgrade
bin/magento cache:flush
```

### Manual Installation

1. Create directory `app/code/Geissweb/ElectronicInvoicingAttributes`
2. Copy module files to this directory
3. Run:
```bash
bin/magento module:enable Geissweb_ElectronicInvoicingAttributes
bin/magento setup:upgrade
bin/magento cache:flush
```

## Configuration

No configuration required. The module works out of the box after installation.

## Usage

### For Customers

After installation, customers will see an "E-Invoicing Information" section on their account edit page (`/customer/account/edit/`):

- **Buyer Reference**: Enter your Leitweg-ID (for German public sector) or other routing identifier
- **Company Registration**: Enter your company registration number (HRB, SIREN, etc.)

### For Developers

#### Accessing Customer Attributes

```php
use Magento\Customer\Api\CustomerRepositoryInterface;

$customer = $customerRepository->getById($customerId);

// Get buyer reference (BT-10)
$buyerReference = $customer->getCustomAttribute('buyer_reference')?->getValue();

// Get company registration (BT-47)
$buyerRegistration = $customer->getCustomAttribute('buyer_registration')?->getValue();
```

#### Accessing Order Extension Attributes

```php
use Magento\Sales\Api\OrderRepositoryInterface;

$order = $orderRepository->get($orderId);
$extensionAttributes = $order->getExtensionAttributes();

// Get e-invoicing data
$buyerReference = $extensionAttributes->getEinvoicingBuyerReference();
$projectReference = $extensionAttributes->getEinvoicingProjectReference();
```

## EN16931 Compliance

This module provides data fields that map to EN16931 business terms:

| Module Field | EN16931 BT | Description |
|--------------|------------|-------------|
| `buyer_reference` | BT-10 | Buyer reference |
| `buyer_registration` | BT-47 | Buyer legal registration identifier |

These fields are essential for generating compliant electronic invoices in formats like:
- XRechnung (German CIUS)
- ZUGFeRD/Factur-X
- PEPPOL BIS Billing

## Hyva Theme Support

This module includes native Hyva theme support using the `hyva_customer_account_edit` layout handle. No additional configuration or compatibility modules are required.

**How it works:**
- Luma themes use `customer_account_edit.xml` with standard LESS styling
- Hyva themes automatically load `hyva_customer_account_edit.xml` with Tailwind CSS and Alpine.js

## Database Schema

The module creates:
- Customer EAV attributes in `eav_attribute` table
- Order extension data in `geissweb_einvoicing_order` table

## Testing

Run unit tests:
```bash
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist vendor/geissweb/module-electronic-invoicing-attributes/Test/Unit
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This software is licensed under the [PolyForm Noncommercial License 1.0.0](LICENSE).

**You may:**
- Use this software for your own shop or client projects
- Modify it for your own needs
- Share it with clients as part of your services

**You may not:**
- Sell this software as a product
- Include it in commercial products for sale
- Distribute it on marketplaces

## Support

- **Issues**: [GitHub Issues](https://github.com/geissweb/magento2-electronic-invoicing-attributes/issues)
- **Documentation**: [GEISSWEB](https://www.geissweb.de)

## Credits

Developed by [GEISS Weblösungen](https://www.geissweb.de) - Magento 2 Extension Provider since 2009.
