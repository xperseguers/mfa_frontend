#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_mfafrontend_enable smallint(5) unsigned DEFAULT '0' NOT NULL,
	tx_mfafrontend_secret varchar(255)
);
