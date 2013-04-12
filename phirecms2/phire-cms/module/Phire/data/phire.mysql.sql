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
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('pagination_limit', '25'),
('pagination_range', '10'),
('default_editor', 'Source'),
('default_template', '<!DOCTYPE html>\n<!-- Header //-->\n<html>\n\n<head>\n\n    <title>\n        [{title}]\n    </title>\n\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n\n</head>\n\n<body>\n    <h1>[{title}]</h1>\n[{content}]\n</body>\n\n</html>');

-- --------------------------------------------------------

--
-- Table structure for table `plugins`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]plugins` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `folder` varchar(255) NOT NULL,
  `active` int(1),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=10001 ;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sites` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `aliases` varchar(255),
  `docroot` varchar(255),
  `content_type` varchar(255),
  `template_id` int(16),
  `title` text,
  `error` text,
  `datetime_format` varchar(255),
  `separator` varchar(255),
  `media_formats` text,
  `media_filesize` int(16),
  `media_actions` text,
  `history_limit` int(16),
  `feed_limit` int(16),
  `pagination_limit` int(16),
  `pagination_range` int(16),
  `force_ssl` int(1),
  `cache_type` varchar(255),
  `cache_limit` int(16),
  `live` int(1),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=6001 ;

--
-- Dumping data for table `sites`
--

INSERT INTO `[{prefix}]sites` (`id`, `domain`, `aliases`, `docroot`, `content_type`, `template_id`, `title`, `error`, `datetime_format`, `separator`, `media_formats`, `media_filesize`, `media_actions`, `history_limit`, `feed_limit`, `pagination_limit`, `pagination_range`, `force_ssl`, `cache_type`, `cache_limit`, `live`) VALUES
(6001, '', '', '', 'text/html', 0, 'My Default Site', '<p>We''re sorry. That page was not found.</p>\n', 'M j Y g:i A', ' > ', 'a:24:{s:3:"bz2";s:17:"application/bzip2";s:3:"csv";s:8:"text/csv";s:3:"doc";s:18:"application/msword";s:4:"docx";s:18:"application/msword";s:3:"gif";s:9:"image/gif";s:2:"gz";s:18:"application/x-gzip";s:3:"jpe";s:10:"image/jpeg";s:3:"jpg";s:10:"image/jpeg";s:4:"jpeg";s:10:"image/jpeg";s:3:"pdf";s:15:"application/pdf";s:3:"png";s:9:"image/png";s:3:"ppt";s:18:"application/msword";s:4:"pptx";s:18:"application/msword";s:3:"svg";s:13:"image/svg+xml";s:3:"swf";s:29:"application/x-shockwave-flash";s:3:"tar";s:17:"application/x-tar";s:3:"tgz";s:18:"application/x-gzip";s:3:"tif";s:10:"image/tiff";s:4:"tiff";s:10:"image/tiff";s:3:"tsv";s:8:"text/tsv";s:3:"txt";s:10:"text/plain";s:3:"xls";s:18:"application/msword";s:4:"xlsx";s:18:"application/msword";s:3:"zip";s:17:"application/x-zip";}', 10000000, 'a:4:{s:5:"large";a:1:{s:6:"resize";i:800;}s:6:"medium";a:1:{s:6:"resize";i:400;}s:5:"small";a:1:{s:6:"resize";i:120;}s:5:"thumb";a:1:{s:9:"cropThumb";i:60;}}', 5, 0, 25, 10, 0, '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `site_objects`

--
CREATE TABLE IF NOT EXISTS `[{prefix}]site_objects` (
  `id` int(16) NOT NULL,
  `site_id` int(16) NOT NULL,
  `object` varchar(255) NOT NULL,  -- content, plugin, section, template, theme, user, user_type, etc.
  UNIQUE (`id`, `site_id`, `object`),
  CONSTRAINT `fk_site_object` FOREIGN KEY (`site_id`) REFERENCES `[{prefix}]sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]themes` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `folder` varchar(255) NOT NULL,
  `active` int(1),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=9001 ;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]templates` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `theme_id` int(16),
  `file` varchar(255),
  `parent_id` int(16),
  `device` varchar(255),
  `name` varchar(255),
  `template` mediumtext,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_theme_template` FOREIGN KEY (`theme_id`) REFERENCES `[{prefix}]themes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=8001 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_types` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `login` int(1),
  `registration` int(1),
  `multiple_sessions` int(1),
  `mobile_access` int(1),
  `email_as_username` int(1),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2002 ;

--
-- Dumping data for table `user_types`
--

INSERT INTO `[{prefix}]user_types` (`id`, `type`, `login`, `registration`, `multiple_sessions`, `mobile_access`, `email_as_username`, `force_ssl`, `track_sessions`, `verification`, `approval`, `unsubscribe_login`, `global_access`, `allowed_attempts`, `session_expiration`, `password_encryption`, `password_salt`, `ip_allowed`, `ip_blocked`, `log_emails`, `log_exclude`, `controller`, `sub_controllers`) VALUES
(2001, 'user', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 2, '', '', '', '', '', '', '');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=3002 ;

--
-- Dumping data for table `[{prefix}]user_roles`
--

INSERT INTO `[{prefix}]user_roles` (`id`, `type_id`, `name`) VALUES
(3001, 2001, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_permissions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `role_id` int(16) NOT NULL,
  `resource` varchar(255),
  `permissions` varchar(255),
  UNIQUE (`role_id`, `resource`),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_permission_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=4001 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type_id` int(16),
  `role_id` int(16),
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255),
  `city` varchar(255),
  `state` varchar(255),
  `zip` varchar(255),
  `country` varchar(255),
  `phone` varchar(255),
  `organization` varchar(255),
  `position` varchar(255),
  `birth_date` date,
  `gender` varchar(1),
  `updates` int(1),
  `verified` int(1),
  `last_login` datetime,
  `last_ua` varchar(255),
  `last_ip` varchar(255),
  `failed_attempts` int(16),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_user_type` FOREIGN KEY (`type_id`) REFERENCES `[{prefix}]user_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=5001 ;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]media` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `content_type` varchar(255),
  `file` text NOT NULL,
  `title` text,
  `caption` text,
  `description` text,
  `order` int(16),
  `uploaded` datetime,
  `updated` datetime,
  `created_by` int(16),
  `updated_by` int(16),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=11001 ;

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `template_id` int(16),
  `media_id` int(16),
  `content_type` varchar(255),
  `uri` text NOT NULL,
  `title` text NOT NULL,
  `description` text,
  `content` mediumtext,
  `requests` int(16),
  `feed` int(1),
  `force_ssl` int(1),
  `order` int(16),
  `roles` text,
  `private` int(1),
  `live` int(1),
  `created` datetime,
  `published` datetime,
  `expires` datetime,
  `updated` datetime,
  `created_by` int(16),
  `updated_by` int(16),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_content_template` FOREIGN KEY (`template_id`) REFERENCES `[{prefix}]templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_content_media` FOREIGN KEY (`media_id`) REFERENCES `[{prefix}]media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=7001 ;


