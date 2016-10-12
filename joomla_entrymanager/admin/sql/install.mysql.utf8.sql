-- $Id: install.mysql.utf8.sql 74 2010-12-01 22:04:52Z chdemko $

DROP TABLE IF EXISTS `#__entrymanager`;
 
CREATE TABLE `#__entrymanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `greeting` varchar(25) NOT NULL,
  `catid` int(11) NOT NULL DEFAULT '0',
  `params` TEXT NOT NULL DEFAULT '',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
 
INSERT INTO `#__entrymanager` (`greeting`) VALUES
        ('Hello World!'),
        ('Good bye World!');

