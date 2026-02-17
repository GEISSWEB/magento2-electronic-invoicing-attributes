<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddEInvoicingCustomerAttributes implements DataPatchInterface, PatchVersionInterface
{
    private const ATTRIBUTES = [
        'buyer_reference' => [
            'label' => 'Buyer Reference',
            'sort_order' => 200,
            'note' => 'Buyer routing identifier for electronic invoicing (Leitweg-ID, cost center, etc.) - EN16931 BT-10',
        ],
        'buyer_registration' => [
            'label' => 'Company Registration',
            'sort_order' => 210,
            'note' => 'Legal registration identifier (HRB, SIREN, etc.) - EN16931 BT-47',
        ],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CustomerSetupFactory $customerSetupFactory,
        private readonly AttributeSetFactory $attributeSetFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var AttributeSet $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        foreach (self::ATTRIBUTES as $attributeCode => $attributeConfig) {
            $customerSetup->addAttribute(
                Customer::ENTITY,
                $attributeCode,
                [
                    'type' => 'varchar',
                    'label' => $attributeConfig['label'],
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'visible_on_front' => true,
                    'user_defined' => true,
                    'system' => false,
                    'sort_order' => $attributeConfig['sort_order'],
                    'position' => $attributeConfig['sort_order'],
                    'note' => $attributeConfig['note'],
                ]
            );

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);

            $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => [
                    'adminhtml_customer',
                    'customer_account_edit',
                ],
            ]);

            $attribute->save();
        }

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion(): string
    {
        return '1.0.0';
    }
}
