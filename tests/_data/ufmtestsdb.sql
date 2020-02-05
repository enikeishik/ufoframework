CREATE TABLE IF NOT EXISTS `test_items_table` (
  `id` int(11) NOT NULL auto_increment,
  `disabled` tinyint(1) NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
TRUNCATE TABLE `test_items_table`; /* cleanup from previous tests */
INSERT INTO `test_items_table` VALUES(1,0,NOW(),'','');

CREATE TABLE IF NOT EXISTS `cache` (
  `id` int(11) NOT NULL auto_increment,
  `key` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `dtm` int(11) NOT NULL default '0',
  `created` int(11) NOT NULL default '0',
  `lifetime` int(11) NOT NULL default '0',
  `savetime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
TRUNCATE TABLE `cache`; /* cleanup from previous tests */

CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) NOT NULL auto_increment,
  `path` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `module` varchar(255) default NULL, /* skip NOT NULL for tests */
  `system` tinyint(1) NOT NULL default '0',
  `disabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
TRUNCATE TABLE `sections`; /* cleanup from previous tests */

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL auto_increment,
  `vendor` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `package` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `disabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
TRUNCATE TABLE `modules`; /* cleanup from previous tests */

CREATE TABLE IF NOT EXISTS `widgets` (
  `id` int(11) NOT NULL auto_increment,
  `section_id` int(11) NOT NULL default '0',
  `place` varchar(255) NOT NULL default '',
  `order_id` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `widget` text NOT NULL,
  `disabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
TRUNCATE TABLE `widgets`; /* cleanup from previous tests */
