CREATE TABLE IF NOT EXISTS `#__foxcontact_settings` (
  `name` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__foxcontact_enquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `exported` tinyint(4) NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL,
  `url` text NOT NULL,
  `fields` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_time` (`date`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `#__fcf_sessions`;
DROP TABLE IF EXISTS `#__fcf_settings`;
DROP TABLE IF EXISTS `#__foxcontact_sessions`;
