namespace Vendor\Module\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $connection = $setup->getConnection();
            $connection->changeColumn(
                $setup->getTable('lovat_settings'),
                'departure_zip',
                'departure_zip',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => false,
                    'comment' => 'Departure zip code'
                ]
            );
        }

        $setup->endSetup();
    }
}
