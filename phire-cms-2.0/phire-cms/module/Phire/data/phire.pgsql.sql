--
-- Phire CMS 2.0 PostgreSQL Database
--

--
-- Table structure for table comments
--
CREATE SEQUENCE comment_id_seq START 9001;

CREATE TABLE IF NOT EXISTS [{prefix}]comments (
  id integer NOT NULL DEFAULT nextval('comment_id_seq'),
  content_id integer NOT NULL,
  user_id integer NOT NULL,
  author varchar(255) NOT NULL,
  content text NOT NULL,
  ip varchar(255) NOT NULL,
  ua varchar(255) NOT NULL,
  posted timestamp NOT NULL,
  approved integer NOT NULL,
  spam integer NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE comment_id_seq OWNED BY [{prefix}]comments.id;

--
-- Table structure for table content_types
--
CREATE SEQUENCE content_types_id_seq START 4001;

CREATE TABLE IF NOT EXISTS [{prefix}]content_types (
  id integer NOT NULL DEFAULT nextval('content_types_id_seq'),
  name varchar(255) NOT NULL,
  type varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE content_types_id_seq OWNED BY [{prefix}]content_types.id;

--
-- Dumping data for table content_types
--

INSERT INTO [{prefix}]content_types (id, name, type) VALUES
(4001, 'HTML', 'text/html'),
(4002, 'Text', 'text/plain'),
(4003, 'CSS', 'text/css'),
(4004, 'JavaScript', 'text/javascript'),
(4005, 'XML - Plain', 'text/xml'),
(4006, 'XML - Application', 'application/xml'),
(4007, 'RSS', 'application/rss+xml');

--
-- Table structure for table content
--
CREATE SEQUENCE content_id_seq START 6001;

CREATE TABLE IF NOT EXISTS [{prefix}]content (
  id integer NOT NULL DEFAULT nextval('content_id_seq'),
  site_id integer NOT NULL,
  user_id integer NOT NULL,
  section_id integer NOT NULL,
  uri varchar(255) NOT NULL,
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
  created_on timestamp NOT NULL,
  expire_on timestamp NOT NULL,
  updated_on timestamp NOT NULL,
  created_by integer NOT NULL,
  updated_by integer NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE content_id_seq OWNED BY [{prefix}]content.id;

--
-- Table structure for table events
--

CREATE TABLE IF NOT EXISTS [{prefix}]events (
  content_id integer NOT NULL,
  template_id integer NOT NULL,
  content text NOT NULL,
  recurring varchar(255) NOT NULL,
  address varchar(255) NOT NULL,
  city varchar(255) NOT NULL,
  state varchar(255) NOT NULL,
  zip varchar(255) NOT NULL,
  country varchar(255) NOT NULL,
  phone varchar(255) NOT NULL,
  link text NOT NULL,
  media_id integer NOT NULL,
  PRIMARY KEY (content_id)
) ;

--
-- Table structure for table feeds
--
CREATE SEQUENCE feed_id_seq START 8001;

CREATE TABLE IF NOT EXISTS [{prefix}]feeds (
  id integer NOT NULL DEFAULT nextval('feed_id_seq'),
  site_id integer NOT NULL,
  uri varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  template text NOT NULL,
  feed_limit integer NOT NULL,
  cache integer NOT NULL,
  access_id integer NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE feed_id_seq OWNED BY [{prefix}]feeds.id;

--
-- Table structure for table fields
--
CREATE SEQUENCE field_id_seq START 8001;

CREATE TABLE IF NOT EXISTS [{prefix}]fields (
  id integer NOT NULL DEFAULT nextval('field_id_seq'),
  name varchar(255) NOT NULL,
  type varchar(255) NOT NULL, -- input (text, file, etc), checkbox, radio, select, textarea, etc
  attributes varchar(255) NOT NULL, -- input attributes, i.e., size="40", rows="5", etc
  option_values varchar(255) NOT NULL, -- values for a selectable field type
  used_by varchar(255) NOT NULL, -- Use on pages, files, images, events, members and plugins
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE field_id_seq OWNED BY [{prefix}]fields.id;

--
-- Dumping data for table fields
--

INSERT INTO [{prefix}]fields (id, name, type, attributes, option_values, used_by) VALUES
(17001, 'keywords', 'text', 'size="80"', '', 'page'),
(17002, 'description', 'text', 'size="80"', '', 'page');

--
-- Table structure for table field_values
--

CREATE TABLE IF NOT EXISTS [{prefix}]field_values (
  field_id integer NOT NULL,
  object_id integer NOT NULL,
  object_type varchar(255) NOT NULL, -- page, file, image, event, member or plugin
  value text NOT NULL,
  UNIQUE (field_id, object_id)
) ;

--
-- Table structure for table members
--
CREATE SEQUENCE member_id_seq START 8001;

CREATE TABLE IF NOT EXISTS [{prefix}]members (
    id integer NOT NULL DEFAULT nextval('member_id_seq'),
    site_id integer NOT NULL,
    username varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    fname varchar(255) NOT NULL,
    lname varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    address varchar(255) NOT NULL,
    city varchar(255) NOT NULL,
    state varchar(255) NOT NULL,
    zip varchar(255) NOT NULL,
    country varchar(255) NOT NULL,
    phone varchar(255) NOT NULL,
    organization varchar(255) NOT NULL,
    position varchar(255) NOT NULL,
    birth_date date NOT NULL,
    gender varchar(255) NOT NULL,
    verified integer NOT NULL,
    approved integer NOT NULL,
    updates integer NOT NULL,
    access_id integer NOT NULL,
    last_login timestamp NOT NULL,
    last_ua varchar(255) NOT NULL,
    last_ip varchar(255) NOT NULL,
    failed_attempts integer NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE member_id_seq OWNED BY [{prefix}]members.id;

--
-- Table structure for table pages
--

CREATE TABLE IF NOT EXISTS [{prefix}]pages (
  content_id integer NOT NULL,
  parent_id integer NOT NULL,
  template_id integer NOT NULL,
  content text NOT NULL,
  media_id integer NOT NULL,
  PRIMARY KEY (content_id)
) ;

--
-- Table structure for table plugins
--
CREATE SEQUENCE plugin_id_seq START 15001;

CREATE TABLE IF NOT EXISTS [{prefix}]plugins (
  id integer NOT NULL DEFAULT nextval('plugin_id_seq'),
  site_id integer NOT NULL,
  name varchar(255) NOT NULL,
  author varchar(255) NOT NULL,
  version varchar(255) NOT NULL,
  description varchar(255) NOT NULL,
  file varchar(255) NOT NULL,
  folder varchar(255) NOT NULL,
  tables text NOT NULL,
  subfolders text NOT NULL,
  controller varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE plugin_id_seq OWNED BY [{prefix}]plugins.id;

--
-- Table structure for table sections
--
CREATE SEQUENCE section_id_seq START 7001;

CREATE TABLE IF NOT EXISTS [{prefix}]sections (
  id integer NOT NULL DEFAULT nextval('section_id_seq'),
  site_id integer NOT NULL,
  template_id integer NOT NULL,
  parent_id integer NOT NULL,
  uri varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  short_template text NOT NULL,
  short_template_container varchar(255) NOT NULL,
  short_limit integer NOT NULL,
  long_template text NOT NULL,
  long_template_container varchar(255) NOT NULL,
  long_limit integer NOT NULL,
  sort_order varchar(255) NOT NULL,
  paginate integer NOT NULL,
  requests integer NOT NULL,
  access_id integer NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE section_id_seq OWNED BY [{prefix}]sections.id;

--
-- Table structure for table sessions
--
CREATE SEQUENCE session_id_seq START 13001;

CREATE TABLE IF NOT EXISTS [{prefix}]sessions (
  id integer NOT NULL DEFAULT nextval('session_id_seq'),
  user_id integer NOT NULL,
  member_id integer NOT NULL,
  ip varchar(255) NOT NULL,
  ua varchar(255) NOT NULL,
  start timestamp NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE session_id_seq OWNED BY [{prefix}]sessions.id;

--
-- Table structure for table sites
--
CREATE SEQUENCE site_id_seq START 2001;

CREATE TABLE IF NOT EXISTS [{prefix}]sites (
  id integer NOT NULL DEFAULT nextval('site_id_seq'),
  domain varchar(255) NOT NULL,
  aliases varchar(255) NOT NULL,
  docroot varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  default_content_type_id integer NOT NULL,
  default_template_id integer NOT NULL,
  default_title varchar(255) NOT NULL,
  default_404 text NOT NULL,
  default_datetime_format varchar(255) NOT NULL,
  separator varchar(255) NOT NULL,
  allowed_content_types varchar(255) NOT NULL,
  feed_type varchar(255) NOT NULL,
  feed_limit integer NOT NULL,
  cache integer NOT NULL,
  pagination_limit integer NOT NULL,
  pagination_range integer NOT NULL,
  force_ssl integer NOT NULL,
  live integer NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE site_id_seq OWNED BY [{prefix}]sites.id;

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
  uri varchar(255) NOT NULL,
  requests integer NOT NULL,
  UNIQUE (site_id, uri)
) ;

--
-- Table structure for table site_config_comments
--

CREATE TABLE IF NOT EXISTS [{prefix}]site_config_comments (
  site_id integer NOT NULL,
  allow_comments integer NOT NULL,
  allow_anonymous integer NOT NULL,
  comment_approval integer NOT NULL,
  captcha integer NOT NULL,
  spam_filter text NOT NULL,
  PRIMARY KEY (site_id)
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
  site_id integer NOT NULL,
  large_action varchar(255) NOT NULL,
  large_size integer NOT NULL,
  medium_action varchar(255) NOT NULL,
  medium_size integer NOT NULL,
  small_action varchar(255) NOT NULL,
  small_size integer NOT NULL,
  allowed_image_formats text NOT NULL,
  image_max_filesize integer NOT NULL,
  allowed_file_formats text NOT NULL,
  file_max_filesize integer NOT NULL,
  PRIMARY KEY (site_id)
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
  site_id integer NOT NULL,
  allow_login integer NOT NULL,
  allow_registration integer NOT NULL,
  registration_type varchar(255) NOT NULL,
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
  PRIMARY KEY (site_id)
) ;

--
-- Dumping data for table site_config_members
--

INSERT INTO [{prefix}]site_config_members (site_id, allow_login, allow_registration, registration_type, verification, approval, redirects, force_ssl, password_encryption, multiple_sessions, mobile_access, ip_allowed, ip_blocked, email_as_username, default_access, login_attempts, session_expiration) VALUES
(2001, 0, 0, '', 1, 1, '', 0, 0, 0, 0, '', '', 0, 0, 0, 1800);

--
-- Table structure for table site_searches
--
CREATE SEQUENCE site_search_id_seq START 12001;

CREATE TABLE IF NOT EXISTS [{prefix}]site_searches (
  id integer NOT NULL DEFAULT nextval('site_search_id_seq'),
  site_id integer NOT NULL,
  keywords text NOT NULL,
  results integer NOT NULL,
  timestamp timestamp NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE site_search_id_seq OWNED BY [{prefix}]site_searches.id;

--
-- Table structure for table sys_access
--
CREATE SEQUENCE sys_access_id_seq START 3001;

CREATE TABLE IF NOT EXISTS [{prefix}]sys_access (
  id integer NOT NULL DEFAULT nextval('sys_access_id_seq'),
  type varchar(255) NOT NULL,
  name varchar(255) NOT NULL,
  level integer NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE sys_access_id_seq OWNED BY [{prefix}]sys_access.id;

--
-- Dumping data for table sys_access
--

INSERT INTO [{prefix}]sys_access (id, type, name, level) VALUES
(3001, 'user', 'Admin', 3),
(3002, 'user', 'Basic', 2),
(3003, 'user', 'Restricted', 1),
(3004, 'member', 'Full', 2),
(3005, 'member', 'Basic', 1);

--
-- Table structure for table sys_config
--

CREATE TABLE IF NOT EXISTS [{prefix}]sys_config (
  setting varchar(255) NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (setting)
) ;

--
-- Dumping data for table sys_config
--

INSERT INTO [{prefix}]sys_config (setting, value) VALUES
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
('default_system_template', '<!DOCTYPE html>\n<!-- Header //-->\n<html>\n\n<head>\n\n    <title>\n        [{page_title}]\n    </title>\n\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n\n</head>\n\n<body>\n    <h1>[{page_sub_title}]</h1>\n[{page_content}]\n</body>\n\n</html>'),
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
-- Table structure for table tags
--
CREATE SEQUENCE tag_id_seq START 11001;

CREATE TABLE IF NOT EXISTS [{prefix}]tags (
  id integer NOT NULL DEFAULT nextval('tag_id_seq'),
  site_id integer NOT NULL,
  tag varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE tag_id_seq OWNED BY [{prefix}]tags.id;

--
-- Table structure for table templates
--
CREATE SEQUENCE template_id_seq START 5001;

CREATE TABLE IF NOT EXISTS [{prefix}]templates (
  id integer NOT NULL DEFAULT nextval('template_id_seq'),
  site_id integer NOT NULL,
  parent_id integer NOT NULL,
  device varchar(255) NOT NULL,
  content_type_id integer NOT NULL,
  name varchar(255) NOT NULL,
  template text NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE template_id_seq OWNED BY [{prefix}]templates.id;

--
-- Table structure for table themes
--
CREATE SEQUENCE theme_id_seq START 14001;

CREATE TABLE IF NOT EXISTS [{prefix}]themes (
  id integer NOT NULL DEFAULT nextval('theme_id_seq'),
  site_id integer NOT NULL,
  name varchar(255) NOT NULL,
  author varchar(255) NOT NULL,
  version varchar(255) NOT NULL,
  description varchar(255) NOT NULL,
  file varchar(255) NOT NULL,
  folder varchar(255) NOT NULL,
  templates varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE theme_id_seq OWNED BY [{prefix}]themes.id;

--
-- Table structure for table users
--
CREATE SEQUENCE user_id_seq START 1001;

CREATE TABLE IF NOT EXISTS [{prefix}]users (
  id integer NOT NULL DEFAULT nextval('user_id_seq'),
  username varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  fname varchar(255) NOT NULL,
  lname varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  allowed_sites varchar(255) NOT NULL,
  access_id integer NOT NULL,
  last_login timestamp NOT NULL,
  last_ua varchar(255) NOT NULL,
  last_ip varchar(255) NOT NULL,
  failed_attempts integer NOT NULL,
  PRIMARY KEY (id)
) ;

ALTER SEQUENCE user_id_seq OWNED BY [{prefix}]users.id;
