--
-- Phire CMS 2.0 SQLite Database
--

--
-- Set database encoding
--

PRAGMA encoding = "UTF-8";

--
-- Table structure for table comments
--

CREATE TABLE IF NOT EXISTS [{prefix}]comments (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  content_id integer NOT NULL,
  user_id integer NOT NULL,
  author varchar NOT NULL,
  content text NOT NULL,
  ip varchar NOT NULL,
  ua varchar NOT NULL,
  posted datetime NOT NULL,
  approved integer NOT NULL,
  spam integer NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]comments', 9000);

--
-- Table structure for table content_types
--

CREATE TABLE IF NOT EXISTS [{prefix}]content_types (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  name varchar NOT NULL,
  type varchar NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]content_types', 4000);

--
-- Dumping data for table content_types
--

INSERT INTO [{prefix}]content_types (id, name, type) VALUES (4001, 'HTML', 'text/html');
INSERT INTO [{prefix}]content_types (id, name, type) VALUES (4002, 'Text', 'text/plain');
INSERT INTO [{prefix}]content_types (id, name, type) VALUES (4003, 'CSS', 'text/css');
INSERT INTO [{prefix}]content_types (id, name, type) VALUES (4004, 'JavaScript', 'text/javascript');
INSERT INTO [{prefix}]content_types (id, name, type) VALUES (4005, 'XML - Plain', 'text/xml');
INSERT INTO [{prefix}]content_types (id, name, type) VALUES (4006, 'XML - Application', 'application/xml');
INSERT INTO [{prefix}]content_types (id, name, type) VALUES (4007, 'RSS', 'application/rss+xml');

--
-- Table structure for table content
--

CREATE TABLE IF NOT EXISTS [{prefix}]content (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  site_id integer NOT NULL,
  user_id integer NOT NULL,
  section_id integer NOT NULL,
  uri varchar NOT NULL,
  title text NOT NULL,
  description text NOT NULL,
  tags text NOT NULL,
  requests integer NOT NULL,
  comments integer NOT NULL,
  feed integer NOT NULL,
  force_ssl integer NOT NULL,
  access_id integer NOT NULL,
  content_order integer NOT NULL,
  live integer NOT NULL,
  created_on datetime NOT NULL,
  expire_on datetime NOT NULL,
  updated_on datetime NOT NULL,
  created_by integer NOT NULL,
  updated_by integer NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]content', 6000);

--
-- Table structure for table events
--

CREATE TABLE IF NOT EXISTS [{prefix}]events (
  content_id integer NOT NULL PRIMARY KEY,
  template_id integer NOT NULL,
  content mediumtext NOT NULL,
  recurring varchar NOT NULL,
  address varchar NOT NULL,
  city varchar NOT NULL,
  state varchar NOT NULL,
  zip varchar NOT NULL,
  country varchar NOT NULL,
  phone varchar NOT NULL,
  link text NOT NULL,
  media_id integer NOT NULL,
  UNIQUE (content_id)
) ;

--
-- Table structure for table feeds
--

CREATE TABLE IF NOT EXISTS [{prefix}]feeds (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  site_id integer NOT NULL,
  uri varchar NOT NULL,
  title varchar NOT NULL,
  template text NOT NULL,
  feed_limit integer NOT NULL,
  cache integer NOT NULL,
  access_id integer NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]feeds', 8000);

--
-- Table structure for table fields
--

CREATE TABLE IF NOT EXISTS [{prefix}]fields (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  name varchar NOT NULL,
  type varchar NOT NULL, -- input (text, file, etc), checkbox, radio, select, textarea, etc
  attributes varchar NOT NULL, -- input attributes, i.e., size="40", rows="5", etc
  option_values varchar NOT NULL, -- values for a selectable field type
  used_by varchar NOT NULL, -- Use on pages, files, images, events, members and plugins
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]fields', 16000);

--
-- Dumping data for table fields
--

INSERT INTO [{prefix}]fields (id, name, type, attributes, option_values, used_by) VALUES (17001, 'keywords', 'text', 'size="80"', '', 'page');
INSERT INTO [{prefix}]fields (id, name, type, attributes, option_values, used_by) VALUES (17002, 'description', 'text', 'size="80"', '', 'page');

--
-- Table structure for table field_values
--

CREATE TABLE IF NOT EXISTS [{prefix}]field_values (
  field_id integer NOT NULL,
  object_id integer NOT NULL,
  object_type varchar NOT NULL, -- page, file, image, event, member or plugin
  value text NOT NULL,
  UNIQUE (field_id, object_id)
) ;

--
-- Table structure for table members
--

CREATE TABLE IF NOT EXISTS [{prefix}]members (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    site_id integer NOT NULL,
    username varchar NOT NULL,
    password varchar NOT NULL,
    fname varchar NOT NULL,
    lname varchar NOT NULL,
    email varchar NOT NULL,
    address varchar NOT NULL,
    city varchar NOT NULL,
    state varchar NOT NULL,
    zip varchar NOT NULL,
    country varchar NOT NULL,
    phone varchar NOT NULL,
    organization varchar NOT NULL,
    position varchar NOT NULL,
    birth_date date NOT NULL,
    gender varchar NOT NULL,
    verified integer NOT NULL,
    approved integer NOT NULL,
    updates integer NOT NULL,
    access_id integer NOT NULL,
    last_login datetime NOT NULL,
    last_ua varchar NOT NULL,
    last_ip varchar NOT NULL,
    failed_attempts integer NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]members', 10000);

--
-- Table structure for table pages
--

CREATE TABLE IF NOT EXISTS [{prefix}]pages (
  content_id integer NOT NULL PRIMARY KEY,
  parent_id integer NOT NULL,
  template_id integer NOT NULL,
  content mediumtext NOT NULL,
  media_id integer NOT NULL,
  UNIQUE (content_id)
) ;

--
-- Table structure for table plugins
--

CREATE TABLE IF NOT EXISTS [{prefix}]plugins (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  site_id integer NOT NULL,
  name varchar NOT NULL,
  author varchar NOT NULL,
  version varchar NOT NULL,
  description varchar NOT NULL,
  file varchar NOT NULL,
  folder varchar NOT NULL,
  tables text NOT NULL,
  subfolders text NOT NULL,
  controller varchar NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]plugins', 15000);

--
-- Table structure for table sections
--

CREATE TABLE IF NOT EXISTS [{prefix}]sections (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  site_id integer NOT NULL,
  template_id integer NOT NULL,
  parent_id integer NOT NULL,
  uri varchar NOT NULL,
  title varchar NOT NULL,
  short_template text NOT NULL,
  short_template_container varchar NOT NULL,
  short_limit integer NOT NULL,
  long_template text NOT NULL,
  long_template_container varchar NOT NULL,
  long_limit integer NOT NULL,
  sort_order varchar NOT NULL,
  paginate integer NOT NULL,
  requests integer NOT NULL,
  access_id integer NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]sections', 7000);

--
-- Table structure for table sessions
--

CREATE TABLE IF NOT EXISTS [{prefix}]sessions (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  user_id integer NOT NULL,
  member_id integer NOT NULL,
  ip varchar NOT NULL,
  ua varchar NOT NULL,
  start datetime NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]sessions', 13000);

--
-- Table structure for table sites
--

CREATE TABLE IF NOT EXISTS [{prefix}]sites (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  domain varchar NOT NULL,
  aliases varchar NOT NULL,
  docroot varchar NOT NULL,
  email varchar NOT NULL,
  default_content_type_id integer NOT NULL,
  default_template_id integer NOT NULL,
  default_title varchar NOT NULL,
  default_404 text NOT NULL,
  default_datetime_format varchar NOT NULL,
  separator varchar NOT NULL,
  allowed_content_types varchar NOT NULL,
  feed_type varchar NOT NULL,
  feed_limit integer NOT NULL,
  cache integer NOT NULL,
  pagination_limit integer NOT NULL,
  pagination_range integer NOT NULL,
  force_ssl integer NOT NULL,
  live integer NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]sites', 2000);

--
-- Dumping data for table sites
--

INSERT INTO [{prefix}]sites (id, domain, aliases, docroot, email, default_content_type_id, default_template_id, default_title, default_404, default_datetime_format, separator, allowed_content_types, feed_type, feed_limit, cache, pagination_limit, pagination_range, force_ssl, live) VALUES
(2001, '', '', '', '', 4001, 0, 'My Default Site', '<p>We''re sorry. That page was not found.</p>\n', 'M j Y g:i A', ' > ', '4001|4002|4003|4004|4005|4006|4007', 'rss', 0, 0, 25, 10, 0, 1);

--
-- Table structure for table site_404s
--

CREATE TABLE IF NOT EXISTS [{prefix}]site_404s (
  site_id integer NOT NULL,
  uri varchar NOT NULL,
  requests integer NOT NULL,
  UNIQUE (site_id, uri)
) ;

--
-- Table structure for table site_config_comments
--

CREATE TABLE IF NOT EXISTS [{prefix}]site_config_comments (
  site_id integer NOT NULL PRIMARY KEY,
  allow_comments integer NOT NULL,
  allow_anonymous integer NOT NULL,
  comment_approval integer NOT NULL,
  captcha integer NOT NULL,
  spam_filter text NOT NULL,
  UNIQUE (site_id)
) ;

--
-- Dumping data for table site_config_comments
--

INSERT INTO [{prefix}]site_config_comments (site_id, allow_comments, allow_anonymous, comment_approval, captcha, spam_filter) VALUES
(2001, 1, 1, 0, 0, '');

--
-- Table structure for table site_config_media
--

CREATE TABLE IF NOT EXISTS [{prefix}]site_config_media (
  site_id integer NOT NULL PRIMARY KEY,
  large_action varchar NOT NULL,
  large_size integer NOT NULL,
  medium_action varchar NOT NULL,
  medium_size integer NOT NULL,
  small_action varchar NOT NULL,
  small_size integer NOT NULL,
  allowed_image_formats text NOT NULL,
  image_max_filesize integer NOT NULL,
  allowed_file_formats text NOT NULL,
  file_max_filesize integer NOT NULL,
  UNIQUE (site_id)
) ;

--
-- Dumping data for table site_config_media
--

INSERT INTO [{prefix}]site_config_media (site_id, large_action, large_size, medium_action, medium_size, small_action, small_size, allowed_image_formats, image_max_filesize, allowed_file_formats, file_max_filesize) VALUES
(2001, 'resize', 800, 'resize', 240, 'crop', 70, 'jpg|gif|png|jpe|jpeg', 10000000, '', 10000000);

--
-- Table structure for table site_config_members
--

CREATE TABLE IF NOT EXISTS [{prefix}]site_config_members (
  site_id integer NOT NULL PRIMARY KEY,
  allow_login integer NOT NULL,
  allow_registration integer NOT NULL,
  registration_type varchar NOT NULL,
  verification integer NOT NULL,
  approval integer NOT NULL,
  redirects text NOT NULL,
  force_ssl integer NOT NULL,
  password_encryption integer NOT NULL,
  multiple_sessions integer NOT NULL,
  mobile_access integer NOT NULL,
  ip_allowed text NOT NULL,
  ip_blocked text NOT NULL,
  email_as_username integer NOT NULL,
  default_access integer NOT NULL,
  login_attempts integer NOT NULL,
  session_expiration integer NOT NULL,
  UNIQUE (site_id)
) ;

--
-- Dumping data for table site_config_members
--

INSERT INTO [{prefix}]site_config_members (site_id, allow_login, allow_registration, registration_type, verification, approval, redirects, force_ssl, password_encryption, multiple_sessions, mobile_access, ip_allowed, ip_blocked, email_as_username, default_access, login_attempts, session_expiration) VALUES
(2001, 0, 0, '', 1, 1, '', 0, 0, 0, 0, '', '', 0, 0, 0, 1800);

--
-- Table structure for table site_searches
--

CREATE TABLE IF NOT EXISTS [{prefix}]site_searches (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  site_id integer NOT NULL,
  keywords text NOT NULL,
  results integer NOT NULL,
  timestamp datetime NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]site_searches', 12000);

--
-- Table structure for table sys_access
--

CREATE TABLE IF NOT EXISTS [{prefix}]sys_access (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  type varchar NOT NULL,
  name varchar NOT NULL,
  level integer NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]sys_access', 3000);

--
-- Dumping data for table sys_access
--

INSERT INTO [{prefix}]sys_access (id, type, name, level) VALUES (3001, 'user', 'Admin', 3);
INSERT INTO [{prefix}]sys_access (id, type, name, level) VALUES (3002, 'user', 'Basic', 2);
INSERT INTO [{prefix}]sys_access (id, type, name, level) VALUES (3003, 'user', 'Restricted', 1);
INSERT INTO [{prefix}]sys_access (id, type, name, level) VALUES (3004, 'member', 'Full', 2);
INSERT INTO [{prefix}]sys_access (id, type, name, level) VALUES (3005, 'member', 'Basic', 1);

--
-- Table structure for table sys_config
--

CREATE TABLE IF NOT EXISTS [{prefix}]sys_config (
  setting varchar NOT NULL PRIMARY KEY,
  value text NOT NULL,
  UNIQUE (setting)
) ;

--
-- Dumping data for table sys_config
--

INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('system_version', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('system_docroot', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('server_os', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('server_software', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('db_version', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('php_version', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('installed_on', '0000-00-00 00:00:00');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('updated_on', '0000-00-00 00:00:00');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('force_ssl', '0');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('table_optimization', '0');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('optimization_period', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('last_optimization', '0000-00-00 00:00:00');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('pagination_limit', '25');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('pagination_range', '10');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('default_editor', 'Source');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('default_system_template', '<?xml version="1.0" encoding="utf-8"?>\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">\n<!-- Header //-->\n<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">\n\n<head>\n\n    <title>\n        [{page_title}]\n    </title>\n\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n\n</head>\n\n<body>\n    <h1>[{page_sub_title}]</h1>\n[{page_content}]\n</body>\n\n</html>');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('notification', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('notification_email', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('notification_filter', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('multiple_sessions', '1');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('mobile_access', '1');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('email_as_username', '0');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('password_encryption', '0');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('ip_allowed', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('ip_blocked', '');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('default_access', '0');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('login_attempts', '0');
INSERT INTO [{prefix}]sys_config (setting, value) VALUES ('session_expiration', '1800');

--
-- Table structure for table tags
--

CREATE TABLE IF NOT EXISTS [{prefix}]tags (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  site_id integer NOT NULL,
  tag varchar NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]tags', 11000);

--
-- Table structure for table templates
--

CREATE TABLE IF NOT EXISTS [{prefix}]templates (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  site_id integer NOT NULL,
  parent_id integer NOT NULL,
  device varchar NOT NULL,
  content_type_id integer NOT NULL,
  name varchar NOT NULL,
  template mediumtext NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]templates', 5000);

--
-- Table structure for table themes
--

CREATE TABLE IF NOT EXISTS [{prefix}]themes (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  site_id integer NOT NULL,
  name varchar NOT NULL,
  author varchar NOT NULL,
  version varchar NOT NULL,
  description varchar NOT NULL,
  file varchar NOT NULL,
  folder varchar NOT NULL,
  templates varchar NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]themes', 14000);

--
-- Table structure for table users
--

CREATE TABLE IF NOT EXISTS [{prefix}]users (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  username varchar NOT NULL,
  password varchar NOT NULL,
  fname varchar NOT NULL,
  lname varchar NOT NULL,
  email varchar NOT NULL,
  allowed_sites varchar NOT NULL,
  access_id integer NOT NULL,
  last_login datetime NOT NULL,
  last_ua varchar NOT NULL,
  last_ip varchar NOT NULL,
  failed_attempts integer NOT NULL,
  UNIQUE (id)
) ;

INSERT INTO sqlite_sequence (name, seq) VALUES ('[{prefix}]users', 1000);