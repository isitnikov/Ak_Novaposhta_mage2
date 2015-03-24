<?php

namespace Ak\NovaPoshta\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $installer->run("
CREATE TABLE {$installer->getTable('novaposhta_city')} (
  `id` int(10) unsigned NOT NULL,
  `name_ru` varchar(100),
  `name_ua` varchar(100),
  `updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `name_ru` (`name_ru`),
  INDEX `name_ua` (`name_ua`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$installer->getTable('novaposhta_warehouse')} (
  `id` int(10) unsigned NOT NULL,
  `city_id` int(10) unsigned NOT NULL,
  `address_ru` varchar(200),
  `address_ua` varchar(200),
  `phone` varchar(100),
  `weekday_work_hours` varchar(20),
  `weekday_reseiving_hours` varchar(20),
  `weekday_delivery_hours` varchar(20),
  `saturday_work_hours` varchar(20),
  `saturday_reseiving_hours` varchar(20),
  `saturday_delivery_hours` varchar(20),
  `max_weight_allowed` int(4),
  `longitude` float(10,6),
  `latitude` float(10,6),
  `number_in_city` int(3) unsigned NOT NULL,
  `updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT FOREIGN KEY (`city_id`) REFERENCES `{$installer->getTable('novaposhta_city')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

        $installer->run("CREATE TABLE `{$installer->getTable('novaposhta_quote_address')}` (
  `address_id` int(10) unsigned NOT NULL,
  `warehouse_id` int(10) unsigned DEFAULT NULL,
  `warehouse_label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

        $installer->run("CREATE TABLE `{$installer->getTable('novaposhta_order_address')}` (
  `address_id` int(10) unsigned NOT NULL,
  `warehouse_id` int(10) unsigned DEFAULT NULL,
  `warehouse_label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

        $installer->endSetup();
    }
}