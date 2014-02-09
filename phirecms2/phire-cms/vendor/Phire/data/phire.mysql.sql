--
-- Phire CMS 2.0 MySQL Database
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]config` (
  `setting` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `config`
--

INSERT INTO `[{prefix}]config` (`setting`, `value`) VALUES
('system_version', ''),
('system_domain', ''),
('system_document_root', ''),
('server_operating_system', ''),
('server_software', ''),
('database_version', ''),
('php_version', ''),
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('system_title', 'Phire CMS 2.0'),
('system_email', ''),
('site_title', 'Default Site Title'),
('separator', '>'),
('default_language', 'en_US'),
('error_message', 'Sorry. That page was not found.'),
('datetime_format', 'M j Y g:i A'),
('media_allowed_types', 'a:27:{i:0;s:2:"ai";i:1;s:3:"bz2";i:2;s:3:"csv";i:3;s:3:"doc";i:4;s:4:"docx";i:5;s:3:"eps";i:6;s:3:"gif";i:7;s:2:"gz";i:8;s:4:"html";i:9;s:3:"htm";i:10;s:3:"jpe";i:11;s:3:"jpg";i:12;s:4:"jpeg";i:13;s:3:"pdf";i:14;s:3:"png";i:15;s:3:"ppt";i:16;s:4:"pptx";i:17;s:3:"psd";i:18;s:3:"svg";i:19;s:3:"swf";i:20;s:3:"tar";i:21;s:3:"txt";i:22;s:3:"xls";i:23;s:4:"xlsx";i:24;s:5:"xhtml";i:25;s:3:"xml";i:26;s:3:"zip";}'),
('media_max_filesize', '25000000'),
('media_actions', 'a:4:{s:5:"large";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:720;s:7:"quality";i:60;}s:6:"medium";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:480;s:7:"quality";i:60;}s:5:"small";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:240;s:7:"quality";i:60;}s:5:"thumb";a:3:{s:6:"action";s:9:"cropThumb";s:6:"params";i:60;s:7:"quality";i:60;}}'),
('media_image_adapter', 'Gd'),
('feed_type', '9'),
('feed_limit', '20'),
('open_authoring', '1'),
('incontent_editing', '0'),
('pagination_limit', '25'),
('pagination_range', '10'),
('force_ssl', '0'),
('live', '1');

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_types` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `default_role_id` int(16),
  `login` int(1),
  `registration` int(1),
  `multiple_sessions` int(1),
  `mobile_access` int(1),
  `email_as_username` int(1),
  `email_verification` int(1),
  `force_ssl` int(1),
  `track_sessions` int(1),
  `verification` int(1),
  `approval` int(1),
  `unsubscribe_login` int(1),
  `global_access` int(1),
  `allowed_attempts` int(16),
  `session_expiration` int(16),
  `timeout_warning` int(1),
  `password_encryption` int(1),
  `ip_allowed` text,
  `ip_blocked` text,
  `log_emails` text,
  `log_exclude` text,
  `controller` text,
  `sub_controllers` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2002 ;

--
-- Dumping data for table `user_types`
--

INSERT INTO `[{prefix}]user_types` (`id`, `type`, `default_role_id`, `login`, `registration`, `multiple_sessions`, `mobile_access`, `email_as_username`, `email_verification`, `force_ssl`, `track_sessions`, `verification`, `approval`, `unsubscribe_login`, `global_access`, `allowed_attempts`, `session_expiration`, `timeout_warning`, `password_encryption`, `ip_allowed`, `ip_blocked`, `log_emails`, `log_exclude`, `controller`, `sub_controllers`) VALUES
(2001, 'user', 3001, 1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 30, 0, 2, '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_roles` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type_id` int(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `permissions` text,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_role_type` FOREIGN KEY (`type_id`) REFERENCES `[{prefix}]user_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3002 ;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `[{prefix}]user_roles` (`id`, `type_id`, `name`) VALUES
(3001, 2001, 'Admin');

ALTER TABLE `[{prefix}]user_types` ADD CONSTRAINT `fk_default_role` FOREIGN KEY (`default_role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type_id` int(16),
  `role_id` int(16),
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255) NOT NULL,
  `verified` int(1),
  `logins` text,
  `failed_attempts` int(16),
  `site_ids` text,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_user_type` FOREIGN KEY (`type_id`) REFERENCES `[{prefix}]user_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

--
-- Dumping data for table `users`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_sessions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `user_id` int(16),
  `ip` varchar(255) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  `last` datetime NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `[{prefix}]users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4001 ;

--
-- Dumping data for table `user_sessions`
--

-- --------------------------------------------------------

--
-- Table structure for table `content_types`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content_types` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `uri` int(1) NOT NULL,
  `order` int(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5003 ;

--
-- Dumping data for table `content_types`
--

INSERT INTO `[{prefix}]content_types` (`id`, `name`, `uri`, `order`) VALUES
(5001, 'Page', 1, 1),
(5002, 'Media', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `site_id` int(16),
  `type_id` int(16),
  `parent_id` int(16),
  `template` varchar(255),
  `title` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `feed` int(1),
  `force_ssl` int(1),
  `status` int(1),
  `roles` text,
  `created` datetime,
  `updated` datetime,
  `published` datetime,
  `expired` datetime,
  `created_by` int(16),
  `updated_by` int(16),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_content_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `[{prefix}]content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_content_type` FOREIGN KEY (`type_id`) REFERENCES `[{prefix}]content_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `[{prefix}]users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `[{prefix}]users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6004 ;

--
-- Dumping data for table `content`
--

INSERT INTO `[{prefix}]content` (`id`, `site_id`, `type_id`, `parent_id`, `template`, `title`, `uri`, `slug`, `feed`, `force_ssl`, `status`) VALUES
(6001, 0, 5001, NULL, 'index.phtml', 'Home', '/', '', 1, 0, 2),
(6002, 0, 5001, NULL, 'sub.phtml', 'About', '/about', 'about', 1, 0, 2),
(6003, 0, 5001, 6002, 'sub.phtml', 'Sample Page', '/about/sample-page', 'sample-page', 1, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `navigation`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]navigation` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `navigation` varchar(255) NOT NULL,
  `spaces` int(16),
  `top_node` varchar(255),
  `top_id` varchar(255),
  `top_class` varchar(255),
  `top_attributes` varchar(255),
  `parent_node` varchar(255),
  `parent_id` varchar(255),
  `parent_class` varchar(255),
  `parent_attributes` varchar(255),
  `child_node` varchar(255),
  `child_id` varchar(255),
  `child_class` varchar(255),
  `child_attributes` varchar(255),
  `on_class` varchar(255),
  `off_class` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7002 ;

--
-- Dumping data for table `navigation`
--

INSERT INTO `[{prefix}]navigation` (`id`, `navigation`, `spaces`, `top_node`, `top_id`) VALUES
(7001, 'Main Nav', 4, 'ul', 'main-nav');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]categories` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `title` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `order` int(16) NOT NULL,
  `total` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_category_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `[{prefix}]categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8002 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `[{prefix}]categories` (`id`, `parent_id`, `title`, `uri`, `slug`, `order`, `total`) VALUES
(8001, NULL, 'My Favorites', '/my-favorites', 'my-favorites', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `content_to_categories`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content_to_categories` (
  `content_id` int(16) NOT NULL,
  `category_id` int(16) NOT NULL,
  UNIQUE (`content_id`, `category_id`),
  CONSTRAINT `fk_category_content_id` FOREIGN KEY (`content_id`) REFERENCES `[{prefix}]content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_content_category_id` FOREIGN KEY (`category_id`) REFERENCES `[{prefix}]categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `content_to_categories`
--

INSERT INTO `[{prefix}]content_to_categories` (`content_id`, `category_id`) VALUES
(6002, 8001),
(6003, 8001);

-- --------------------------------------------------------

--
-- Table structure for table `navigation_tree`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]navigation_tree` (
  `navigation_id` int(16) NOT NULL,
  `content_id` int(16),
  `category_id` int(16),
  `order` int(16) NOT NULL,
  UNIQUE (`navigation_id`, `content_id`, `category_id`),
  CONSTRAINT `fk_navigation_id` FOREIGN KEY (`navigation_id`) REFERENCES `[{prefix}]navigation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_navigation_content_id` FOREIGN KEY (`content_id`) REFERENCES `[{prefix}]content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `navigation_tree`
--

INSERT INTO `[{prefix}]navigation_tree` (`navigation_id`, `content_id`, `category_id`, `order`) VALUES
(7001, 6001, NULL, 1),
(7001, 6002, NULL, 2),
(7001, 6003, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]templates` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `name` varchar(255) NOT NULL,
  `content_type` varchar(255) NOT NULL,
  `device` varchar(255) NOT NULL,
  `template` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_template_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `[{prefix}]templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9001 ;

-- --------------------------------------------------------

--
-- Table structure for table `extensions`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]extensions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `type` int(1) NOT NULL,
  `active` int(1) NOT NULL,
  `assets` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10002 ;

--
-- Dumping data for table `extensions`
--

INSERT INTO `[{prefix}]extensions` (`id`, `name`, `file`, `type`, `active`, `assets`) VALUES
(10001, 'default', 'default.tar.gz', 0, 1, 'a:2:{s:9:"templates";a:9:{i:0;s:10:"date.phtml";i:1;s:11:"error.phtml";i:2;s:13:"sidebar.phtml";i:3;s:14:"category.phtml";i:4;s:11:"index.phtml";i:5;s:12:"header.phtml";i:6;s:12:"search.phtml";i:7;s:9:"sub.phtml";i:8;s:12:"footer.phtml";}s:4:"info";a:4:{s:10:"Theme Name";s:13:"Default Theme";s:6:"Author";s:11:"Nick Sagona";s:11:"Description";s:41:"This is a default theme for Phire CMS 2.0";s:7:"Version";s:3:"1.0";}}');

-- --------------------------------------------------------

--
-- Table structure for table `field_groups`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]field_groups` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `order` int(16),
  `dynamic` int(1),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12001 ;

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]fields` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `group_id` int(16),
  `type` varchar(255),
  `name` varchar(255),
  `label` varchar(255),
  `values` varchar(255),
  `default_values` varchar(255),
  `attributes` varchar(255),
  `validators` varchar(255),
  `encryption` int(1) NOT NULL,
  `order` int(16) NOT NULL,
  `required` int(1) NOT NULL,
  `editor` varchar(255),
  `models` text,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `[{prefix}]field_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11004 ;

--
-- Dumping data for table `fields`
--

INSERT INTO `[{prefix}]fields` (`id`, `group_id`, `type`, `name`, `label`, `values`, `default_values`, `attributes`, `validators`, `encryption`, `order`, `required`, `editor`, `models`) VALUES
(11001, NULL, 'text', 'description', 'Description:', '', '', 'size="80"', NULL, 0, 1, 0, 'source', 'a:1:{i:0;a:2:{s:5:"model";s:19:"Phire\\Model\\Content";s:7:"type_id";i:5001;}}'),
(11002, NULL, 'text', 'keywords', 'Keywords:', '', '', 'size="80"', NULL, 0, 2, 0, 'source', 'a:1:{i:0;a:2:{s:5:"model";s:19:"Phire\\Model\\Content";s:7:"type_id";i:5001;}}'),
(11003, NULL, 'textarea-history', 'content', 'Content:', '', '', 'rows="20" cols="110" style="display: block;"', NULL, 0, 3, 0, 'source', 'a:1:{i:0;a:2:{s:5:"model";s:19:"Phire\\Model\\Content";s:7:"type_id";i:5001;}}');

-- --------------------------------------------------------

--
-- Table structure for table `field_values`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]field_values` (
  `field_id` int(16) NOT NULL,
  `model_id` int(16) NOT NULL,
  `value` mediumtext,
  `timestamp` int(16),
  `history` mediumtext,
  UNIQUE (`field_id`, `model_id`),
  CONSTRAINT `fk_field_id` FOREIGN KEY (`field_id`) REFERENCES `[{prefix}]fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `field_values`
--

INSERT INTO `[{prefix}]field_values` (`field_id`, `model_id`, `value`, `timestamp`, `history`) VALUES
(11001, 6001, 's:41:"This is the welcome page for Phire CMS 2.";', NULL, NULL),
(11001, 6002, 's:39:"This is the about page for Phire CMS 2.";', NULL, NULL),
(11001, 6003, 's:40:"This is the sample page for Phire CMS 2.";', NULL, NULL),
(11002, 6001, 's:36:"default site, phire cms 2, home page";', NULL, NULL),
(11002, 6002, 's:37:"default site, phire cms 2, about page";', NULL, NULL),
(11002, 6003, 's:38:"default site, phire cms 2, sample page";', NULL, NULL),
(11003, 6001, 's:963:"<p>This is the home page for Phire CMS 2.</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede.</p>\r\n\r\n<p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat.</p>";', NULL, NULL),
(11003, 6002, 's:964:"<p>This is the about page for Phire CMS 2.</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede.</p>\r\n\r\n<p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat.</p>";', NULL, NULL),
(11003, 6003, 's:965:"<p>This is the sample page for Phire CMS 2.</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede.</p>\r\n\r\n<p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat.</p>";', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sites` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `document_root` varchar(255) NOT NULL,
  `base_path` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `force_ssl` int(1) NOT NULL,
  `live` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13001 ;
