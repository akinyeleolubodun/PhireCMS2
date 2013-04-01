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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `config`
--

INSERT INTO `[{prefix}]config` (`setting`, `value`) VALUES
('system_version', ''),
('server_os', ''),
('server_software', ''),
('db_version', ''),
('php_version', ''),
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('table_optimization', '0'),
('optimization_period', ''),
('last_optimization', '0000-00-00 00:00:00'),
('pagination_limit', '25'),
('pagination_range', '10'),
('default_editor', 'Source'),
('default_template', '<!DOCTYPE html>\n<!-- Header //-->\n<html>\n\n<head>\n\n    <title>\n        [{page_title}]\n    </title>\n\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n\n</head>\n\n<body>\n    <h1>[{page_sub_title}]</h1>\n[{page_content}]\n</body>\n\n</html>');

-- --------------------------------------------------------

--
-- Table structure for table `content_types`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content_types` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7009 ;

--
-- Dumping data for table `content_types`
--

INSERT INTO `[{prefix}]content_types` (`id`, `name`, `type`) VALUES
(7001, 'html', 'text/html'),
(7002, 'text', 'text/plain'),
(7003, 'css', 'text/css'),
(7004, 'javascript', 'text/javascript'),
(7005, 'xml, plain', 'text/xml'),
(7006, 'xml, application', 'application/xml'),
(7007, 'rss', 'application/rss+xml'),
(7008, 'json', 'application/json');

-- --------------------------------------------------------

--
-- Table structure for table `plugins`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]plugins` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `author` varchar(255),
  `version` varchar(255),
  `description` varchar(255),
  `file` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `tables` text NOT NULL,
  `active` int(1),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14001 ;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sites` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `aliases` varchar(255),
  `docroot` varchar(255),
  `default_content_type_id` int(16),
  `default_template_id` int(16),
  `default_title` text,
  `default_404` text,
  `default_datetime_format` varchar(255),
  `separator` varchar(255),
  `media_formats` text,
  `media_filesize` int(16),
  `media_actions` varchar(255),
  `media_sizes` varchar(255),
  `comments` int(1),
  `anonymous_comments` int(1),
  `comment_approval` int(1),
  `captcha_type` varchar(255),
  `spam_filter` text,
  `history_limit` int(16),
  `feed_limit` int(16),
  `pagination_limit` int(16),
  `pagination_range` int(16),
  `force_ssl` int(1),
  `cache_type` varchar(255),
  `cache_limit` int(16),
  `live` int(1),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6001 ;

--
-- Dumping data for table `sites`
--

INSERT INTO `[{prefix}]sites` (`id`, `domain`, `aliases`, `docroot`, `default_content_type_id`, `default_template_id`, `default_title`, `default_404`, `default_datetime_format`, `separator`, `media_formats`, `media_filesize`, `media_actions`, `media_sizes`, `comments`, `anonymous_comments`, `captcha_type`, `spam_filter`, `feed_limit`, `pagination_limit`, `pagination_range`, `force_ssl`, `cache_type`, `cache_limit`, `live`) VALUES
(6001, '', '', '', 7001, 0, 'My Default Site', '<p>We''re sorry. That page was not found.</p>\n', 'M j Y g:i A', ' > ', 'jpg|jpe|jpeg|gif|png', 10000000, 'resize|resize|resize|cropThumb', '800|400|120|60', 0, 0, '', '', 0, 25, 10, 0, '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `site_relationships`

--
CREATE TABLE IF NOT EXISTS `[{prefix}]site_relationships` (
  `id` int(16) NOT NULL,
  `site_id` int(16) NOT NULL,
  `relationship` varchar(255) NOT NULL,  -- content, plugin, section, template, theme, user, user_type, etc.
  UNIQUE (`id`, `site_id`, `relationship`),
  CONSTRAINT `fk_site_relationship` FOREIGN KEY (`site_id`) REFERENCES `[{prefix}]sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]templates` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `content_type_id` int(16),
  `device` varchar(255),
  `name` varchar(255),
  `template` mediumtext,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_template_content_type` FOREIGN KEY (`content_type_id`) REFERENCES `[{prefix}]content_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10001 ;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]themes` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `author` varchar(255),
  `version` varchar(255),
  `description` varchar(255),
  `file` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `active` int(1),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13001 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2003 ;

--
-- Dumping data for table `user_types`
--

INSERT INTO `[{prefix}]user_types` (`id`, `type`, `login`, `registration`, `multiple_sessions`, `mobile_access`, `email_as_username`, `force_ssl`, `track_sessions`, `verification`, `approval`, `unsubscribe_login`, `global_access`, `allowed_attempts`, `session_expiration`, `password_encryption`, `password_salt`, `ip_allowed`, `ip_blocked`, `log_emails`, `log_exclude`, `controller`, `sub_controllers`) VALUES
(2001, 'user', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 2, '', '', '', '', '', '', ''),
(2002, 'member', 1, 1, 1, 1, 1, 0, 1, 1, 1, 0, 0, 0, 0, 2, '', '', '', '', '', '', '');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3003 ;

--
-- Dumping data for table `[{prefix}]user_roles`
--

INSERT INTO `[{prefix}]user_roles` (`id`, `type_id`, `name`) VALUES
(3001, 2001, 'Admin'),
(3002, 2002, 'Full');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4001 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5001 ;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sections` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `parent_id` int(16),
  `short_template_id` int(16),
  `long_template_id` int(16),
  `short_limit` int(16),
  `long_limit` int(16),
  `sort_order` varchar(255),
  `paginate` int(1),
  `requests` int(16),
  `role_id` int(16),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_section_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11001 ;

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `template_id` int(16),
  `section_id` int(16),
  `media_id` int(16),
  `content_type_id` int(16),
  `uri` text NOT NULL,
  `title` text NOT NULL,
  `description` text,
  `content` mediumtext,
  `requests` int(16),
  `comments` int(1),
  `feed` int(1),
  `force_ssl` int(1),
  `order` int(16),
  `role_id` int(16),
  `live` int(1),
  `created` datetime,
  `published` datetime,
  `expires` datetime,
  `updated` datetime,
  `created_by` int(16),
  `updated_by` int(16),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_content_template` FOREIGN KEY (`template_id`) REFERENCES `[{prefix}]templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_content_section` FOREIGN KEY (`section_id`) REFERENCES `[{prefix}]sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_content_content_type` FOREIGN KEY (`content_type_id`) REFERENCES `[{prefix}]content_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_content_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8001 ;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]comments` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `content_id` int(16) NOT NULL,
  `parent_id` int(16),
  `user_id` int(16),
  `name` varchar(255),
  `email` varchar(255),
  `content` text NOT NULL,
  `ip` varchar(255) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `posted` datetime NOT NULL,
  `approved` int(1) NOT NULL,
  `spam` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_comment_content` FOREIGN KEY (`content_id`) REFERENCES `[{prefix}]content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `[{prefix}]users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12001 ;

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]fields` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL, -- input (text, file, etc), checkbox, radio, select, textarea, etc
  `attributes` varchar(255),    -- field attributes, i.e., size="40", rows="5", etc
  `values` varchar(255),        -- values for a selectable field type
  `default` varchar(255),       -- default value or values
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15003 ;

--
-- Dumping data for table `fields`
--

INSERT INTO `[{prefix}]fields` (`id`, `name`, `type`, `attributes`) VALUES
(15001, 'keywords', 'text', 'size="80"'),
(15002, 'description', 'text', 'size="80"');

-- --------------------------------------------------------

--
-- Table structure for table `field_values`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]field_values` (
  `content_id` int(16) NOT NULL,
  `field_id` int(16) NOT NULL,
  `value` text NOT NULL,
  UNIQUE (`content_id`, `field_id`),
  CONSTRAINT `fk_field_content` FOREIGN KEY (`content_id`) REFERENCES `[{prefix}]content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]feeds` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `template_id` int(16),
  `feed_limit` int(16),
  `cache_type` varchar(255),
  `cache_limit` int(16),
  `role_id` int(16),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_feed_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16001 ;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]tags` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDb  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17001 ;

-- --------------------------------------------------------

--
-- Table structure for table `tagged_content`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]tagged_content` (
  `tag_id` int(16) NOT NULL,
  `content_id` int(16) NOT NULL,
  UNIQUE (`tag_id`, `content_id`),
  CONSTRAINT `fk_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `[{prefix}]tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tag_content_id` FOREIGN KEY (`content_id`) REFERENCES `[{prefix}]content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDb  DEFAULT CHARSET=utf8 ;

