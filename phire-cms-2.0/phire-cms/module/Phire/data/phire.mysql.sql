--
-- Phire CMS 2.0 MySQL Database
--

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]comments` (
  `id` int(16) NOT NULL auto_increment,
  `content_id` int(16) NOT NULL,
  `user_id` int(16) NOT NULL,
  `author` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `ip` varchar(255) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `posted` datetime NOT NULL,
  `approved` int(1) NOT NULL,
  `spam` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9001 ;

--
-- Table structure for table `content_types`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content_types` (
  `id` int(16) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4001 ;

--
-- Dumping data for table `content_types`
--

INSERT INTO `[{prefix}]content_types` (`id`, `name`, `type`) VALUES
(4001, 'HTML', 'text/html'),
(4002, 'Text', 'text/plain'),
(4003, 'CSS', 'text/css'),
(4004, 'JavaScript', 'text/javascript'),
(4005, 'XML - Plain', 'text/xml'),
(4006, 'XML - Application', 'application/xml'),
(4007, 'RSS', 'application/rss+xml');

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]content` (
  `id` int(16) NOT NULL auto_increment,
  `site_id` int(16) NOT NULL,
  `user_id` int(16) NOT NULL,
  `section_id` int(16) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `tags` text NOT NULL,
  `requests` int(16) NOT NULL,
  `comments` int(1) NOT NULL,
  `feed` int(1) NOT NULL,
  `force_ssl` int(1) NOT NULL,
  `access_id` int(16) NOT NULL,
  `content_order` int(16) NOT NULL,
  `live` int(1) NOT NULL,
  `created_on` datetime NOT NULL,
  `expire_on` datetime NOT NULL,
  `updated_on` datetime NOT NULL,
  `created_by` int(16) NOT NULL,
  `updated_by` int(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6001 ;

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]events` (
  `content_id` int(16) NOT NULL,
  `template_id` int(16) NOT NULL,
  `content` mediumtext NOT NULL,
  `recurring` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `link` text NOT NULL,
  `media_id` int(16) NOT NULL,
  PRIMARY KEY  (`content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `feeds`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]feeds` (
  `id` int(16) NOT NULL auto_increment,
  `site_id` int(16) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `template` text NOT NULL,
  `feed_limit` int(16) NOT NULL,
  `cache` int(16) NOT NULL,
  `access_id` int(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8001 ;

--
-- Table structure for table `fields`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]fields` (
  `id` int(16) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL, -- input (text, file, etc), checkbox, radio, select, textarea, etc
  `attributes` varchar(255) NOT NULL, -- input attributes, i.e., size="40", rows="5", etc
  `option_values` varchar(255) NOT NULL, -- values for a selectable field type
  `used_by` varchar(255) NOT NULL, -- Use on pages, files, images, events, members and plugins
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16001 ;

--
-- Dumping data for table `fields`
--

INSERT INTO `[{prefix}]fields` (`id`, `name`, `type`, `attributes`, `option_values`, `used_by`) VALUES
(17001, 'keywords', 'text', 'size="80"', '', 'page'),
(17002, 'description', 'text', 'size="80"', '', 'page');

--
-- Table structure for table `field_values`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]field_values` (
  `field_id` int(16) NOT NULL,
  `object_id` int(16) NOT NULL,
  `object_type` varchar(255) NOT NULL, -- page, file, image, event, member or plugin
  `value` text NOT NULL,
  UNIQUE KEY `field_id` (`field_id`, `object_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `members`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]members` (
    `id` int(16) NOT NULL auto_increment,
    `site_id` int(16) NOT NULL,
    `username` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `fname` varchar(255) NOT NULL,
    `lname` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `address` varchar(255) NOT NULL,
    `city` varchar(255) NOT NULL,
    `state` varchar(255) NOT NULL,
    `zip` varchar(255) NOT NULL,
    `country` varchar(255) NOT NULL,
    `phone` varchar(255) NOT NULL,
    `organization` varchar(255) NOT NULL,
    `position` varchar(255) NOT NULL,
    `birth_date` date NOT NULL,
    `gender` varchar(255) NOT NULL,
    `verified` int(1) NOT NULL,
    `approved` int(1) NOT NULL,
    `updates` int(1) NOT NULL,
    `access_id` int(16) NOT NULL,
    `last_login` datetime NOT NULL,
    `last_ua` varchar(255) NOT NULL,
    `last_ip` varchar(255) NOT NULL,
    `failed_attempts` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`site_id`),
  INDEX (`username`),
  INDEX (`email`),
  INDEX (`fname`),
  INDEX (`lname`),
  INDEX (`site_id`, `id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10001 ;

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]pages` (
  `content_id` int(16) NOT NULL,
  `parent_id` int(16) NOT NULL,
  `template_id` int(16) NOT NULL,
  `content` mediumtext NOT NULL,
  `media_id` int(16) NOT NULL,
  PRIMARY KEY  (`content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `plugins`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]plugins` (
  `id` int(16) NOT NULL auto_increment,
  `site_id` int(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `tables` text NOT NULL,
  `subfolders` text NOT NULL,
  `controller` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15001 ;

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sections` (
  `id` int(16) NOT NULL auto_increment,
  `site_id` int(16) NOT NULL,
  `template_id` int(16) NOT NULL,
  `parent_id` int(16) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_template` text NOT NULL,
  `short_template_container` varchar(255) NOT NULL,
  `short_limit` int(16) NOT NULL,
  `long_template` text NOT NULL,
  `long_template_container` varchar(255) NOT NULL,
  `long_limit` int(16) NOT NULL,
  `sort_order` varchar(255) NOT NULL,
  `paginate` int(1) NOT NULL,
  `requests` int(16) NOT NULL,
  `access_id` int(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7001 ;

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sessions` (
  `id` int(16) NOT NULL auto_increment,
  `user_id` int(16) NOT NULL,
  `member_id` int(16) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13001 ;

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sites` (
  `id` int(16) NOT NULL auto_increment,
  `domain` varchar(255) NOT NULL,
  `aliases` varchar(255) NOT NULL,
  `docroot` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `default_content_type_id` int(16) NOT NULL,
  `default_template_id` int(16) NOT NULL,
  `default_title` varchar(255) NOT NULL,
  `default_404` text NOT NULL,
  `default_datetime_format` varchar(255) NOT NULL,
  `separator` varchar(255) NOT NULL,
  `allowed_content_types` varchar(255) NOT NULL,
  `feed_type` varchar(255) NOT NULL,
  `feed_limit` int(16) NOT NULL,
  `cache` int(16) NOT NULL,
  `pagination_limit` int(16) NOT NULL,
  `pagination_range` int(16) NOT NULL,
  `force_ssl` int(1) NOT NULL,
  `live` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2001 ;

--
-- Dumping data for table `sites`
--

INSERT INTO `[{prefix}]sites` (`id`, `domain`, `aliases`, `docroot`, `email`, `default_content_type_id`, `default_template_id`, `default_title`, `default_404`, `default_datetime_format`, `separator`, `allowed_content_types`, `feed_type`, `feed_limit`, `cache`, `pagination_limit`, `pagination_range`, `force_ssl`, `live`) VALUES
(2001, '', '', '', '', 4001, 0, 'My Default Site', '<p>We''re sorry. That page was not found.</p>\n', 'M j Y g:i A', ' > ', '4001|4002|4003|4004|4005|4006|4007', 'rss', 0, 0, 25, 10, 0, 1);

--
-- Table structure for table `site_404s`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]site_404s` (
  `site_id` int(16) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `requests` int(16) NOT NULL,
  UNIQUE KEY `site_id` (`site_id`, `uri`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `site_config_comments`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]site_config_comments` (
  `site_id` int(16) NOT NULL,
  `allow_comments` int(1) NOT NULL,
  `allow_anonymous` int(1) NOT NULL,
  `comment_approval` int(1) NOT NULL,
  `captcha` int(1) NOT NULL,
  `spam_filter` text NOT NULL,
  PRIMARY KEY  (`site_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `site_config_comments`
--

INSERT INTO `[{prefix}]site_config_comments` (`site_id`, `allow_comments`, `allow_anonymous`, `comment_approval`, `captcha`, `spam_filter`) VALUES
(2001, 1, 1, 0, 0, '');

--
-- Table structure for table `site_config_media`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]site_config_media` (
  `site_id` int(16) NOT NULL,
  `large_action` varchar(255) NOT NULL,
  `large_size` int(16) NOT NULL,
  `medium_action` varchar(255) NOT NULL,
  `medium_size` int(16) NOT NULL,
  `small_action` varchar(255) NOT NULL,
  `small_size` int(16) NOT NULL,
  `allowed_image_formats` text NOT NULL,
  `image_max_filesize` int(16) NOT NULL,
  `allowed_file_formats` text NOT NULL,
  `file_max_filesize` int(16) NOT NULL,
  PRIMARY KEY  (`site_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `site_config_media`
--

INSERT INTO `[{prefix}]site_config_media` (`site_id`, `large_action`, `large_size`, `medium_action`, `medium_size`, `small_action`, `small_size`, `allowed_image_formats`, `image_max_filesize`, `allowed_file_formats`, `file_max_filesize`) VALUES
(2001, 'resize', 800, 'resize', 240, 'crop', 70, 'jpg|gif|png|jpe|jpeg', 10000000, '', 10000000);

--
-- Table structure for table `site_config_members`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]site_config_members` (
  `site_id` int(16) NOT NULL,
  `allow_login` int(1) NOT NULL,
  `allow_registration` int(1) NOT NULL,
  `registration_type` varchar(255) NOT NULL,
  `verification` int(1) NOT NULL,
  `approval` int(1) NOT NULL,
  `redirects` text NOT NULL,
  `force_ssl` int(1) NOT NULL,
  `password_encryption` int(1) NOT NULL,
  `multiple_sessions` int(1) NOT NULL,
  `mobile_access` int(1) NOT NULL,
  `ip_allowed` text NOT NULL,
  `ip_blocked` text NOT NULL,
  `email_as_username` int(1) NOT NULL,
  `default_access` int(16) NOT NULL,
  `login_attempts` int(16) NOT NULL,
  `session_expiration` int(16) NOT NULL,
  PRIMARY KEY  (`site_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `site_config_members`
--

INSERT INTO `[{prefix}]site_config_members` (`site_id`, `allow_login`, `allow_registration`, `registration_type`, `verification`, `approval`, `redirects`, `force_ssl`, `password_encryption`, `multiple_sessions`, `mobile_access`, `ip_allowed`, `ip_blocked`, `email_as_username`, `default_access`, `login_attempts`, `session_expiration`) VALUES
(2001, 0, 0, '', 1, 1, '', 0, 0, 0, 0, '', '', 0, 0, 0, 1800);

--
-- Table structure for table `site_searches`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]site_searches` (
  `id` int(16) NOT NULL auto_increment,
  `site_id` int(16) NOT NULL,
  `keywords` text NOT NULL,
  `results` int(16) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12001 ;

--
-- Table structure for table `sys_access`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sys_access` (
  `id` int(16) NOT NULL auto_increment,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` int(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3001 ;

--
-- Dumping data for table `sys_access`
--

INSERT INTO `[{prefix}]sys_access` (`id`, `type`, `name`, `level`) VALUES
(3001, 'user', 'Admin', 3),
(3002, 'user', 'Basic', 2),
(3003, 'user', 'Restricted', 1),
(3004, 'member', 'Full', 2),
(3005, 'member', 'Basic', 1);

--
-- Table structure for table `sys_config`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sys_config` (
  `setting` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`setting`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `sys_config`
--

INSERT INTO `[{prefix}]sys_config` (`setting`, `value`) VALUES
('system_version', ''),
('system_docroot', ''),
('server_os', ''),
('server_software', ''),
('db_version', ''),
('php_version', ''),
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('force_ssl', '0'),
('table_optimization', '0'),
('optimization_period', ''),
('last_optimization', '0000-00-00 00:00:00'),
('pagination_limit', '25'),
('pagination_range', '10'),
('default_editor', 'Source'),
('default_system_template', '<?xml version="1.0" encoding="utf-8"?>\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">\n<!-- Header //-->\n<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">\n\n<head>\n\n    <title>\n        [{page_title}]\n    </title>\n\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n\n</head>\n\n<body>\n    <h1>[{page_sub_title}]</h1>\n[{page_content}]\n</body>\n\n</html>'),
('notification', ''),
('notification_email', ''),
('notification_filter', ''),
('multiple_sessions', '1'),
('mobile_access', '1'),
('email_as_username', '0'),
('password_encryption', '0'),
('ip_allowed', ''),
('ip_blocked', ''),
('default_access', '0'),
('login_attempts', '0'),
('session_expiration', '1800');

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]tags` (
  `id` int(16) NOT NULL auto_increment,
  `site_id` int(16) NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11001 ;

--
-- Table structure for table `templates`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]templates` (
  `id` int(16) NOT NULL auto_increment,
  `site_id` int(16) NOT NULL,
  `parent_id` int(16) NOT NULL,
  `device` varchar(255) NOT NULL,
  `content_type_id` int(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `template` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5001 ;

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]themes` (
  `id` int(16) NOT NULL auto_increment,
  `site_id` int(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `templates` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14001 ;

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]users` (
  `id` int(16) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `allowed_sites` varchar(255) NOT NULL,
  `access_id` int(16) NOT NULL,
  `last_login` datetime NOT NULL,
  `last_ua` varchar(255) NOT NULL,
  `last_ip` varchar(255) NOT NULL,
  `failed_attempts` int(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;