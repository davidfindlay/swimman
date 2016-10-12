DROP TABLE IF EXISTS `#__msq_entrymanager`;

CREATE TABLE '#__msq_entrymanager' (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `greeting` varchar(25) NOT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
 
INSERT INTO `#__msq_entrymanager` (`greeting`) VALUES
        ('Hello World!'),
        ('Good bye World!');