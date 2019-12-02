# New table create statement for Digia Iiris testing tool
# Basically the same table as Zabbix 'items' table, as this one will 
# be used for backing up the original item before testing starts
# New columns:
#  'testid' - new primary key
#  'test_value'   - value sent to the item
#  'test_delay'   - the interval 'test_value' is sent to the item

CREATE OR REPLACE TABLE `item_testing` (
`testid` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
`itemid` BIGINT(20) UNSIGNED NOT NULL,
`type` INT(11) NOT NULL DEFAULT '0',
`snmp_community` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`snmp_oid` VARCHAR(512) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`hostid` BIGINT(20) UNSIGNED NOT NULL,
`name` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`key_` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`delay` VARCHAR(1024) NOT NULL DEFAULT '0' COLLATE 'utf8_bin',
`history` VARCHAR(255) NOT NULL DEFAULT '90d' COLLATE 'utf8_bin',
`trends` VARCHAR(255) NOT NULL DEFAULT '365d' COLLATE 'utf8_bin',
`status` INT(11) NOT NULL DEFAULT '0',
`value_type` INT(11) NOT NULL DEFAULT '0',
`trapper_hosts` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`units` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`snmpv3_securityname` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`snmpv3_securitylevel` INT(11) NOT NULL DEFAULT '0',
`snmpv3_authpassphrase` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`snmpv3_privpassphrase` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`formula` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`lastlogsize` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
`logtimefmt` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`valuemapid` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
`params` TEXT NULL COLLATE 'utf8_bin',
`ipmi_sensor` VARCHAR(128) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`authtype` INT(11) NOT NULL DEFAULT '0',
`username` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`password` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`publickey` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`privatekey` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`mtime` INT(11) NOT NULL DEFAULT '0',
`interfaceid` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
`port` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`description` TEXT NOT NULL COLLATE 'utf8_bin',
`inventory_link` INT(11) NOT NULL DEFAULT '0',
`lifetime` VARCHAR(255) NOT NULL DEFAULT '30d' COLLATE 'utf8_bin',
`snmpv3_authprotocol` INT(11) NOT NULL DEFAULT '0',
`snmpv3_privprotocol` INT(11) NOT NULL DEFAULT '0',
`snmpv3_contextname` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`evaltype` INT(11) NOT NULL DEFAULT '0',
`jmx_endpoint` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`master_itemid` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
`test_value` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
`test_delay` VARCHAR(1024) NOT NULL DEFAULT '0' COLLATE 'utf8_bin',
PRIMARY KEY (`testid`),
UNIQUE INDEX `itemid` (`itemid`)
)
COLLATE='utf8_bin'
ENGINE=InnoDB
;