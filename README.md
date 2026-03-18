# Electronic Invoicing Attributes for Magento 2

A Magento 2 module that adds customer and order attributes for electronic invoicing compliance with EN16931, XRechnung, and ZUGFeRD standards.

## Overview

This module extends Magento 2 with essential fields for B2B and B2G electronic invoicing in Europe. It enables customers to store their buyer reference (Leitweg-ID) and company registration number, and provides configurable checkout fields for capturing invoicing data at order placement.

**Perfect for:**
- German public sector invoicing (XRechnung with Leitweg-ID)
- B2B invoicing with EN16931 compliance
- ZUGFeRD/Factur-X invoice generation

## Features

### Customer Attributes
- **Buyer Reference** (`buyer_reference`) - Stores the buyer's routing identifier (Leitweg-ID, cost center, etc.) - Maps to EN16931 BT-10
- **Company Registration** (`buyer_registration`) - Stores the legal registration identifier (HRB, SIREN, etc.) - Maps to EN16931 BT-47

### Checkout Fields
- **Buyer Reference** and **Project Reference** fields in the checkout payment step
- Each field individually configurable (enable/disable per Store View)
- Configurable tooltip text per field via admin
- Auto-save to quote via REST API (Luma) or Magewire (Hyvä Checkout)
- Pre-populates buyer reference from customer attribute for logged-in customers
- Luma: Magento-standard `field-tooltip toggle` dropdown pattern
- Hyvä: `title` attribute on input fields

### Order Extension Attributes
- `einvoicing_buyer_reference` - Captured from checkout at order placement
- `einvoicing_project_reference` - Optional project reference for invoicing

### Admin Integration
- Customer form fieldset "E-Invoicing" in admin customer edit
- E-Invoicing data displayed on order view, invoice view, and credit memo view
- Attributes visible and editable by administrators

### Frontend Integration
- Customer account edit form with E-Invoicing fieldset
- Checkout payment step with collapsible E-Invoicing section
- **Dual theme support:** Works with both Luma and Hyvä themes out of the box

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

Navigate to **Stores > Configuration > Electronic Invoicing > Checkout Fields**:

| Config Path | Type | Default | Scope |
|---|---|---|---|
| `buyer_reference_enabled` | Yes/No | Yes | Store View |
| `buyer_reference_tooltip` | Text | `For German public sector invoicing, enter your Leitweg-ID` | Store View |
| `project_reference_enabled` | Yes/No | Yes | Store View |
| `project_reference_tooltip` | Text | `Optional project or contract reference for the invoice` | Store View |

- Disable a field to hide it from checkout
- Both fields disabled: entire E-Invoicing section is hidden, no API calls made
- Tooltip text appears as a help icon dropdown (Luma) or `title` attribute (Hyvä)

## Usage

### For Customers

**Account page** (`/customer/account/edit/`):
- **Buyer Reference**: Enter your Leitweg-ID (for German public sector) or other routing identifier
- **Company Registration**: Enter your company registration number (HRB, SIREN, etc.)

**Checkout** (payment step):
- **Buyer Reference**: Pre-populated from customer attribute if available
- **Project Reference**: Optional reference for the invoice

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

## REST API

### Customer Cart

```
GET  /V1/carts/mine/einvoicing          # Get e-invoicing data for current cart
POST /V1/carts/mine/einvoicing          # Save e-invoicing data to current cart
```
Authentication: Customer token (resource: `self`)

### Guest Cart

```
GET  /V1/guest-carts/:cartId/einvoicing # Get e-invoicing data for guest cart
POST /V1/guest-carts/:cartId/einvoicing # Save e-invoicing data to guest cart
```
Authentication: Anonymous (masked cart ID required)

### Payload

```json
{
    "buyerReference": "04011000-1234512345-06",
    "projectReference": "PROJECT-2025-001"
}
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

## Hyvä Theme Support

### Customer Account

Native Hyvä theme support using the `hyva_customer_account_edit` layout handle with Tailwind CSS styling. No additional modules required.

### Hyvä Checkout

For Hyvä Checkout support, install the companion module:

```bash
composer require geissweb/module-electronic-invoicing-attributes-hyva-checkout
bin/magento module:enable Geissweb_ElectronicInvoicingAttributesHyvaCheckout
bin/magento setup:upgrade
bin/magento cache:flush
```

This module provides a Magewire component (`EInvoicingFields`) that integrates the e-invoicing fields into the Hyvä Checkout payment step with real-time data persistence.

## Database Schema

The module creates:
- Customer EAV attributes (`buyer_reference`, `buyer_registration`) in `eav_attribute` table
- Order extension data in `geissweb_einvoicing_order` table
- Quote extension data in `geissweb_einvoicing_quote` table

Both extension tables store `buyer_reference` and `project_reference` with foreign keys to their respective parent tables (cascade delete).

## Testing

Run unit tests:
```bash
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist path/to/Geissweb_ElectronicInvoicingAttributes/Test/Unit
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
- **Documentation**: [GEISSWEB](https://geissweb.com)

