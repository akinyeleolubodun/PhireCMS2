--
-- Phire CMS 2.0 MySQL Database
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `phirecms`
--

-- --------------------------------------------------------

--
-- Table structure for table `ph_types`
--

CREATE TABLE IF NOT EXISTS `ph_types` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2003 ;

--
-- Dumping data for table `ph_types`
--

INSERT INTO `ph_types` (`id`, `type`, `login`, `registration`, `multiple_sessions`, `mobile_access`, `email_as_username`, `force_ssl`, `track_sessions`, `verification`, `approval`, `unsubscribe_login`, `global_access`, `allowed_attempts`, `session_expiration`, `password_encryption`, `password_salt`, `ip_allowed`, `ip_blocked`, `log_emails`, `log_exclude`) VALUES
(2001, 'User', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 2, '', '', '', '', ''),
(2002, 'Member', 1, 1, 1, 1, 1, 0, 1, 1, 1, 0, 0, 0, 0, 2, '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `ph_roles`
--

CREATE TABLE IF NOT EXISTS `ph_roles` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type_id` int(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_role_type` FOREIGN KEY (`type_id`) REFERENCES `ph_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3005 ;

--
-- Dumping data for table `ph_roles`
--

INSERT INTO `ph_roles` (`id`, `type_id`, `name`) VALUES
(3001, 2001, 'Admin'),
(3002, 2001, 'Restricted'),
(3003, 2002, 'Full'),
(3004, 2002, 'Basic');

-- --------------------------------------------------------

--
-- Table structure for table `ph_permissions`
--

CREATE TABLE IF NOT EXISTS `ph_permissions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `role_id` int(16) NOT NULL,
  `resource` varchar(255),
  `permissions` varchar(255),
  UNIQUE (`role_id`, `resource`),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_permission_role` FOREIGN KEY (`role_id`) REFERENCES `ph_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4003 ;

--
-- Dumping data for table `ph_permissions`
--

INSERT INTO `ph_permissions` (`id`, `role_id`, `resource`, `permissions`) VALUES
(4001, 3002, 'users', 'read,add,edit'),
(4002, 3004, 'profile', 'read');


-- --------------------------------------------------------

--
-- Table structure for table `ph_users`
--

CREATE TABLE IF NOT EXISTS `ph_users` (
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
  CONSTRAINT `fk_user_type` FOREIGN KEY (`type_id`) REFERENCES `ph_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `ph_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1004 ;

--
-- Dumping data for table `ph_users`
--

INSERT INTO `ph_users` (`id`, `type_id`, `role_id`, `first_name`, `last_name`, `email`, `username`, `password`, `updates`, `verified`) VALUES
(1001, 2001, 3001, 'System', 'Admin', 'test@admin.com', 'admin', 'babfd5547a2ee2692ee03d3f0d973dc8ce7297d4', 1, 1),
(1002, 2001, 3002, 'Test', 'User', 'test@user.com', 'testuser', 'c214105243281cf6147b81fde537bc2769200211', 1, 1),
(1003, 2002, 3003, 'Test', 'Member', 'test@member.com', 'test@member.com', '7c4a8d09ca3762af61e59520943dc26494f8941b', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ph_sessions`
--

CREATE TABLE IF NOT EXISTS `ph_sessions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `user_id` int(16),
  `ip` varchar(255) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  `last` datetime NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `ph_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5001 ;

--
-- Dumping data for table `ph_sessions`
--
