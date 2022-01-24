<?php

namespace Lovat\Api\Setup;

use Lovat\Api\Model\ResourceModel\Orders as LovatModel;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists(LovatModel::TABLE_NAME)) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable(LovatModel::TABLE_NAME)
            )
                ->addColumn(
                    'log_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Log id'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    55,
                    ['nullable' => false],
                    'Execution result'
                )
                ->setComment('Lovat api log table');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
