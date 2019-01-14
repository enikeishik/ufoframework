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
