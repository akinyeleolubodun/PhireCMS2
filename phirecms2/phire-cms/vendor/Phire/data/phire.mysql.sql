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
-- Dumping data for table `[{prefix}]config`
--

INSERT INTO `[{prefix}]config` (`setting`, `value`) VALUES
('system_version', ''),
('system_document_root', ''),
('server_operating_system', ''),
('server_software', ''),
('database_version', ''),
('php_version', ''),
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('site_title', 'Default Site Title'),
('separator', '&gt;'),
('default_language', 'en_US'),
('error_message', 'Sorry. That page was not found.'),
('datetime_format', 'M j Y g:i A'),
('media_allowed_types', 'a:27:{i:0;s:2:"ai";i:1;s:3:"bz2";i:2;s:3:"csv";i:3;s:3:"doc";i:4;s:4:"docx";i:5;s:3:"eps";i:6;s:3:"gif";i:7;s:2:"gz";i:8;s:4:"html";i:9;s:3:"htm";i:10;s:3:"jpe";i:11;s:3:"jpg";i:12;s:4:"jpeg";i:13;s:3:"pdf";i:14;s:3:"png";i:15;s:3:"ppt";i:16;s:4:"pptx";i:17;s:3:"psd";i:18;s:3:"svg";i:19;s:3:"swf";i:20;s:3:"tar";i:21;s:3:"txt";i:22;s:3:"xls";i:23;s:4:"xlsx";i:24;s:5:"xhtml";i:25;s:3:"xml";i:26;s:3:"zip";}'),
('media_max_filesize', '25000000'),
('media_actions', 'a:4:{s:5:"large";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:720;s:7:"quality";i:60;}s:6:"medium";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:480;s:7:"quality";i:60;}s:5:"small";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:240;s:7:"quality";i:60;}s:5:"thumb";a:3:{s:6:"action";s:9:"cropThumb";s:6:"params";i:60;s:7:"quality";i:60;}}'),
('media_image_adapter', 'Gd'),
('category_totals', '1'),
('feed_type', '10'),
('feed_limit', '20'),
('open_authoring', '1'),
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
  `password_encryption` int(1),
  `password_salt` text,
  `ip_allowed` text,
  `ip_blocked` text,
  `log_emails` text,
  `log_exclude` text,
  `controller` text,
  `sub_controllers` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2002 ;

--
-- Dumping data for table `[{prefix}]user_types`
--

INSERT INTO `[{prefix}]user_types` (`id`, `type`, `default_role_id`, `login`, `registration`, `multiple_sessions`, `mobile_access`, `email_as_username`, `email_verification`, `force_ssl`, `track_sessions`, `verification`, `approval`, `unsubscribe_login`, `global_access`, `allowed_attempts`, `session_expiration`, `password_encryption`, `password_salt`, `ip_allowed`, `ip_blocked`, `log_emails`, `log_exclude`, `controller`, `sub_controllers`) VALUES
(2001, 'user', 3001, 1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 30, 2, '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_roles` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type_id` int(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_role_type` FOREIGN KEY (`type_id`) REFERENCES `[{prefix}]user_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3002 ;

--
-- Dumping data for table `[{prefix}]user_roles`
--

INSERT INTO `[{prefix}]user_roles` (`id`, `type_id`, `name`) VALUES
(3001, 2001, 'Admin');

ALTER TABLE `[{prefix}]user_types` ADD CONSTRAINT `fk_default_role` FOREIGN KEY (`default_role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_permissions` (
  `role_id` int(16) NOT NULL,
  `resource` varchar(255),
  `permission` varchar(255),
  `allow` int(1),
  UNIQUE (`role_id`, `resource`, `permission`),
  CONSTRAINT `fk_permission_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

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
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_user_type` FOREIGN KEY (`type_id`) REFERENCES `[{prefix}]user_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

--
-- Dumping data for table `[{prefix}]users`
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
-- Dumping data for table `[{prefix}]user_sessions`
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5001 ;

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type_id` int(16),
  `parent_id` int(16),
  `template` varchar(255),
  `title` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `order` int(16) NOT NULL,
  `include` int(1),
  `feed` int(1),
  `status` int(1),
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6001 ;

-- --------------------------------------------------------

--
-- Table structure for table `content_categories`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content_categories` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `category` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `order` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_category_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `[{prefix}]content_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7001 ;

-- --------------------------------------------------------

--
-- Table structure for table `content_to_categories`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content_to_categories` (
  `content_id` int(16) NOT NULL,
  `category_id` int(16) NOT NULL,
  UNIQUE (`content_id`, `category_id`),
  CONSTRAINT `fk_category_content_id` FOREIGN KEY (`content_id`) REFERENCES `[{prefix}]content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_content_category_id` FOREIGN KEY (`category_id`) REFERENCES `[{prefix}]content_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


-- --------------------------------------------------------

--
-- Table structure for table `content_to_roles`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content_to_roles` (
  `content_id` int(16) NOT NULL,
  `role_id` int(16) NOT NULL,
  UNIQUE (`content_id`, `role_id`),
  CONSTRAINT `fk_role_content_id` FOREIGN KEY (`content_id`) REFERENCES `[{prefix}]content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_content_role_id` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `content_templates`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content_templates` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `name` varchar(255) NOT NULL,
  `content_type` varchar(255) NOT NULL,
  `device` varchar(255) NOT NULL,
  `template` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_template_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `[{prefix}]content_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8001 ;

-- --------------------------------------------------------

--
-- Table structure for table `extensions`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]extensions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` int(1) NOT NULL,
  `active` int(1) NOT NULL,
  `assets` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9001 ;

