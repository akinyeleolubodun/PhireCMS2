--
-- Phire CMS 2.0 MSSQL Database
--

--
-- Table structure for table [comments]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]comments' AND xtype = 'U')
CREATE TABLE [[{prefix}]comments] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(9001, 1),
  [content_id] int NOT NULL,
  [user_id] int NOT NULL,
  [author] varchar(255) NOT NULL,
  [content] varchar(max) NOT NULL,
  [ip] varchar(255) NOT NULL,
  [ua] varchar(255) NOT NULL,
  [posted] datetime2(0) NOT NULL,
  [approved] int NOT NULL,
  [spam] int NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [content_types]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]content_types' AND xtype = 'U')
CREATE TABLE [[{prefix}]content_types] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(4001, 1),
  [name] varchar(255) NOT NULL,
  [type] varchar(255) NOT NULL,
  UNIQUE ([id])
) ;

--
-- Dumping data for table [content_types]
--

INSERT INTO [[{prefix}]content_types] ([name], [type]) VALUES
('HTML', 'varchar(max)/html'),
('Text', 'varchar(max)/plain'),
('CSS', 'varchar(max)/css'),
('JavaScript', 'varchar(max)/javascript'),
('XML - Plain', 'varchar(max)/xml'),
('XML - Application', 'application/xml'),
('RSS', 'application/rss+xml');

--
-- Table structure for table [content]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]content' AND xtype = 'U')
CREATE TABLE [[{prefix}]content] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(6001, 1),
  [site_id] int NOT NULL,
  [user_id] int NOT NULL,
  [section_id] int NOT NULL,
  [uri] varchar(255) NOT NULL,
  [title] varchar(max) NOT NULL,
  [description] varchar(max) NOT NULL,
  [tags] varchar(max) NOT NULL,
  [requests] int NOT NULL,
  [comments] int NOT NULL,
  [feed] int NOT NULL,
  [force_ssl] int NOT NULL,
  [access_id] int NOT NULL,
  [content_order] int NOT NULL,
  [live] int NOT NULL,
  [created_on] datetime2(0) NOT NULL,
  [expire_on] datetime2(0) NOT NULL,
  [updated_on] datetime2(0) NOT NULL,
  [created_by] int NOT NULL,
  [updated_by] int NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [events]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]events' AND xtype = 'U')
CREATE TABLE [[{prefix}]events] (
  [content_id] int NOT NULL PRIMARY KEY,
  [template_id] int NOT NULL,
  [content] varchar(max) NOT NULL,
  [recurring] varchar(255) NOT NULL,
  [address] varchar(255) NOT NULL,
  [city] varchar(255) NOT NULL,
  [state] varchar(255) NOT NULL,
  [zip] varchar(255) NOT NULL,
  [country] varchar(255) NOT NULL,
  [phone] varchar(255) NOT NULL,
  [link] varchar(max) NOT NULL,
  [media_id] int NOT NULL,
  UNIQUE ([content_id])
) ;

--
-- Table structure for table [feeds]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]feeds' AND xtype = 'U')
CREATE TABLE [[{prefix}]feeds] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(8001, 1),
  [site_id] int NOT NULL,
  [uri] varchar(255) NOT NULL,
  [title] varchar(255) NOT NULL,
  [template] varchar(max) NOT NULL,
  [feed_limit] int NOT NULL,
  [cache] int NOT NULL,
  [access_id] int NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [fields]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]fields' AND xtype = 'U')
CREATE TABLE [[{prefix}]fields] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(16001, 1),
  [name] varchar(255) NOT NULL,
  [type] varchar(255) NOT NULL, -- input (varchar(max), file, etc), checkbox, radio, select, varchar(max)area, etc
  [attributes] varchar(255) NOT NULL, -- input attributes, i.e., size="40", rows="5", etc
  [option_values] varchar(255) NOT NULL, -- values for a selectable field type
  [used_by] varchar(255) NOT NULL, -- Use on pages, files, images, events, members and plugins
  UNIQUE ([id])
) ;

--
-- Dumping data for table [fields]
--

INSERT INTO [[{prefix}]fields] ([name], [type], [attributes], [option_values], [used_by]) VALUES
('keywords', 'text', 'size="80"', '', 'page'),
('description', 'text', 'size="80"', '', 'page');

--
-- Table structure for table [field_values]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]field_values' AND xtype = 'U')
CREATE TABLE [[{prefix}]field_values] (
  [field_id] int NOT NULL,
  [object_id] int NOT NULL,
  [object_type] varchar(255) NOT NULL, -- page, file, image, event, member or plugin
  [value] varchar(max) NOT NULL,
  UNIQUE ([field_id], [object_id])
) ;

--
-- Table structure for table [members]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]members' AND xtype = 'U')
CREATE TABLE [[{prefix}]members] (
    [id] int NOT NULL PRIMARY KEY IDENTITY(10001, 1),
    [site_id] int NOT NULL,
    [username] varchar(255) NOT NULL,
    [password] varchar(255) NOT NULL,
    [first_name] varchar(255) NOT NULL,
    [last_name] varchar(255) NOT NULL,
    [email] varchar(255) NOT NULL,
    [address] varchar(255) NOT NULL,
    [city] varchar(255) NOT NULL,
    [state] varchar(255) NOT NULL,
    [zip] varchar(255) NOT NULL,
    [country] varchar(255) NOT NULL,
    [phone] varchar(255) NOT NULL,
    [organization] varchar(255) NOT NULL,
    [position] varchar(255) NOT NULL,
    [birth_date] date NOT NULL,
    [gender] varchar(255) NOT NULL,
    [verified] int NOT NULL,
    [approved] int NOT NULL,
    [updates] int NOT NULL,
    [access_id] int NOT NULL,
    [last_login] datetime2(0) NOT NULL,
    [last_ua] varchar(255) NOT NULL,
    [last_ip] varchar(255) NOT NULL,
    [failed_attempts] int NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [pages]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]pages' AND xtype = 'U')
CREATE TABLE [[{prefix}]pages] (
  [content_id] int NOT NULL PRIMARY KEY,
  [parent_id] int NOT NULL,
  [template_id] int NOT NULL,
  [content] varchar(max) NOT NULL,
  [media_id] int NOT NULL,
  UNIQUE ([content_id])
) ;

--
-- Table structure for table [plugins]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]plugins' AND xtype = 'U')
CREATE TABLE [[{prefix}]plugins] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(15001, 1),
  [site_id] int NOT NULL,
  [name] varchar(255) NOT NULL,
  [author] varchar(255) NOT NULL,
  [version] varchar(255) NOT NULL,
  [description] varchar(255) NOT NULL,
  [file] varchar(255) NOT NULL,
  [folder] varchar(255) NOT NULL,
  [tables] varchar(max) NOT NULL,
  [subfolders] varchar(max) NOT NULL,
  [controller] varchar(255) NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [sections]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]sections' AND xtype = 'U')
CREATE TABLE [[{prefix}]sections] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(7001, 1),
  [site_id] int NOT NULL,
  [template_id] int NOT NULL,
  [parent_id] int NOT NULL,
  [uri] varchar(255) NOT NULL,
  [title] varchar(255) NOT NULL,
  [short_template] varchar(max) NOT NULL,
  [short_template_container] varchar(255) NOT NULL,
  [short_limit] int NOT NULL,
  [long_template] varchar(max) NOT NULL,
  [long_template_container] varchar(255) NOT NULL,
  [long_limit] int NOT NULL,
  [sort_order] varchar(255) NOT NULL,
  [paginate] int NOT NULL,
  [requests] int NOT NULL,
  [access_id] int NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [sessions]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]sessions' AND xtype = 'U')
CREATE TABLE [[{prefix}]sessions] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(13001, 1),
  [user_id] int NOT NULL,
  [member_id] int NOT NULL,
  [ip] varchar(255) NOT NULL,
  [ua] varchar(255) NOT NULL,
  [start] datetime2(0) NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [sites]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]sites' AND xtype = 'U')
CREATE TABLE [[{prefix}]sites] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(2001, 1),
  [domain] varchar(255) NOT NULL,
  [aliases] varchar(255) NOT NULL,
  [docroot] varchar(255) NOT NULL,
  [email] varchar(255) NOT NULL,
  [default_content_type_id] int NOT NULL,
  [default_template_id] int NOT NULL,
  [default_title] varchar(255) NOT NULL,
  [default_404] varchar(max) NOT NULL,
  [default_datetime_format] varchar(255) NOT NULL,
  [separator] varchar(255) NOT NULL,
  [allowed_content_types] varchar(255) NOT NULL,
  [feed_type] varchar(255) NOT NULL,
  [feed_limit] int NOT NULL,
  [cache] int NOT NULL,
  [pagination_limit] int NOT NULL,
  [pagination_range] int NOT NULL,
  [force_ssl] int NOT NULL,
  [live] int NOT NULL,
  UNIQUE ([id])
) ;

--
-- Dumping data for table [sites]
--

INSERT INTO [[{prefix}]sites] ([domain], [aliases], [docroot], [email], [default_content_type_id], [default_template_id], [default_title], [default_404], [default_datetime_format], [separator], [allowed_content_types], [feed_type], [feed_limit], [cache], [pagination_limit], [pagination_range], [force_ssl], [live]) VALUES
('', '', '', '', 4001, 0, 'My Default Site', '<p>We''re sorry. That page was not found.</p>\n', 'M j Y g:i A', ' > ', '4001|4002|4003|4004|4005|4006|4007', 'rss', 0, 0, 25, 10, 0, 1);

--
-- Table structure for table [site_404s]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]site_404s' AND xtype = 'U')
CREATE TABLE [[{prefix}]site_404s] (
  [site_id] int NOT NULL,
  [uri] varchar(255) NOT NULL,
  [requests] int NOT NULL,
  UNIQUE ([site_id], [uri])
) ;

--
-- Table structure for table [site_config_comments]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]site_config_comments' AND xtype = 'U')
CREATE TABLE [[{prefix}]site_config_comments] (
  [site_id] int NOT NULL PRIMARY KEY,
  [allow_comments] int NOT NULL,
  [allow_anonymous] int NOT NULL,
  [comment_approval] int NOT NULL,
  [captcha] int NOT NULL,
  [spam_filter] varchar(max) NOT NULL,
  UNIQUE ([site_id])
) ;

--
-- Dumping data for table [site_config_comments]
--

INSERT INTO [[{prefix}]site_config_comments] ([site_id], [allow_comments], [allow_anonymous], [comment_approval], [captcha], [spam_filter]) VALUES
(2001, 1, 1, 0, 0, '');

--
-- Table structure for table [site_config_media]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]site_config_media' AND xtype = 'U')
CREATE TABLE [[{prefix}]site_config_media] (
  [site_id] int NOT NULL PRIMARY KEY,
  [large_action] varchar(255) NOT NULL,
  [large_size] int NOT NULL,
  [medium_action] varchar(255) NOT NULL,
  [medium_size] int NOT NULL,
  [small_action] varchar(255) NOT NULL,
  [small_size] int NOT NULL,
  [allowed_image_formats] varchar(max) NOT NULL,
  [image_max_filesize] int NOT NULL,
  [allowed_file_formats] varchar(max) NOT NULL,
  [file_max_filesize] int NOT NULL,
  UNIQUE ([site_id])
) ;

--
-- Dumping data for table [site_config_media]
--

INSERT INTO [[{prefix}]site_config_media] ([site_id], [large_action], [large_size], [medium_action], [medium_size], [small_action], [small_size], [allowed_image_formats], [image_max_filesize], [allowed_file_formats], [file_max_filesize]) VALUES
(2001, 'resize', 800, 'resize', 240, 'crop', 70, 'jpg|gif|png|jpe|jpeg', 10000000, '', 10000000);

--
-- Table structure for table [site_config_members]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]site_config_members' AND xtype = 'U')
CREATE TABLE [[{prefix}]site_config_members] (
  [site_id] int NOT NULL PRIMARY KEY,
  [allow_login] int NOT NULL,
  [allow_registration] int NOT NULL,
  [registration_type] varchar(255) NOT NULL,
  [verification] int NOT NULL,
  [approval] int NOT NULL,
  [redirects] varchar(max) NOT NULL,
  [force_ssl] int NOT NULL,
  [password_encryption] int NOT NULL,
  [multiple_sessions] int NOT NULL,
  [mobile_access] int NOT NULL,
  [ip_allowed] varchar(max) NOT NULL,
  [ip_blocked] varchar(max) NOT NULL,
  [email_as_username] int NOT NULL,
  [default_access] int NOT NULL,
  [login_attempts] int NOT NULL,
  [session_expiration] int NOT NULL,
  UNIQUE ([site_id])
) ;

--
-- Dumping data for table [site_config_members]
--

INSERT INTO [[{prefix}]site_config_members] ([site_id], [allow_login], [allow_registration], [registration_type], [verification], [approval], [redirects], [force_ssl], [password_encryption], [multiple_sessions], [mobile_access], [ip_allowed], [ip_blocked], [email_as_username], [default_access], [login_attempts], [session_expiration]) VALUES
(2001, 0, 0, '', 1, 1, '', 0, 0, 0, 0, '', '', 0, 0, 0, 1800);

--
-- Table structure for table [site_searches]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]site_searches' AND xtype = 'U')
CREATE TABLE [[{prefix}]site_searches] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(12001, 1),
  [site_id] int NOT NULL,
  [keywords] varchar(max) NOT NULL,
  [results] int NOT NULL,
  [timestamp] datetime2(0) NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [sys_access]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]sys_access' AND xtype = 'U')
CREATE TABLE [[{prefix}]sys_access] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(3001, 1),
  [type] varchar(255) NOT NULL,
  [name] varchar(255) NOT NULL,
  [level] int NOT NULL,
  UNIQUE ([id])
) ;

--
-- Dumping data for table [sys_access]
--

INSERT INTO [[{prefix}]sys_access] ([type], [name], [level]) VALUES
('user', 'Admin', 3),
('user', 'Basic', 2),
('user', 'Restricted', 1),
('member', 'Full', 2),
('member', 'Basic', 1);

--
-- Table structure for table [sys_config]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]sys_config' AND xtype = 'U')
CREATE TABLE [[{prefix}]sys_config] (
  [setting] varchar(255) NOT NULL PRIMARY KEY,
  [value] varchar(max) NOT NULL,
  UNIQUE ([setting])
) ;

--
-- Dumping data for table [sys_config]
--

INSERT INTO [[{prefix}]sys_config] ([setting], [value]) VALUES
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
('default_system_template', '<!DOCTYPE html>\n<!-- Header //-->\n<html>\n\n<head>\n\n    <title>\n        [{page_title}]\n    </title>\n\n    <meta http-equiv="Content-Type" content="varchar(max)/html; charset=utf-8" />\n\n</head>\n\n<body>\n    <h1>[{page_sub_title}]</h1>\n[{page_content}]\n</body>\n\n</html>'),
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
-- Table structure for table [tags]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]tags' AND xtype = 'U')
CREATE TABLE [[{prefix}]tags] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(11001, 1),
  [site_id] int NOT NULL,
  [tag] varchar(255) NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [templates]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]templates' AND xtype = 'U')
CREATE TABLE [[{prefix}]templates] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(5001, 1),
  [site_id] int NOT NULL,
  [parent_id] int NOT NULL,
  [device] varchar(255) NOT NULL,
  [content_type_id] int NOT NULL,
  [name] varchar(255) NOT NULL,
  [template] varchar(max) NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [themes]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]themes' AND xtype = 'U')
CREATE TABLE [[{prefix}]themes] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(14001, 1),
  [site_id] int NOT NULL,
  [name] varchar(255) NOT NULL,
  [author] varchar(255) NOT NULL,
  [version] varchar(255) NOT NULL,
  [description] varchar(255) NOT NULL,
  [file] varchar(255) NOT NULL,
  [folder] varchar(255) NOT NULL,
  [templates] varchar(255) NOT NULL,
  UNIQUE ([id])
) ;

--
-- Table structure for table [users]
--

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '[{prefix}]users' AND xtype = 'U')
CREATE TABLE [[{prefix}]users] (
  [id] int NOT NULL PRIMARY KEY IDENTITY(1001, 1),
  [username] varchar(255) NOT NULL,
  [password] varchar(255) NOT NULL,
  [first_name] varchar(255) NOT NULL,
  [last_name] varchar(255) NOT NULL,
  [email] varchar(255) NOT NULL,
  [allowed_sites] varchar(255) NOT NULL,
  [access_id] int NOT NULL,
  [last_login] datetime2(0) NOT NULL,
  [last_ua] varchar(255) NOT NULL,
  [last_ip] varchar(255) NOT NULL,
  [failed_attempts] int NOT NULL,
  UNIQUE ([id])
) ;
