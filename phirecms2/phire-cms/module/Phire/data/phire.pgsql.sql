--
-- Phire CMS 2.0 PostgreSQL Database
--

-- --------------------------------------------------------

--
-- Table structure for table "config"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]config" (
  "setting" varchar(255) NOT NULL,
  "value" text NOT NULL,
  PRIMARY KEY ("setting")
) ;

--
-- Dumping data for table "config"
--

INSERT INTO "[{prefix}]config" ("setting", "value") VALUES
('system_version', ''),
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('pagination_limit', '25'),
('pagination_range', '10'),
('default_editor', 'Source'),
('default_template', '<!DOCTYPE html>\n<!-- Header //-->\n<html>\n\n<head>\n\n    <title>\n        [{title}]\n    </title>\n\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n\n</head>\n\n<body>\n    <h1>[{title}]</h1>\n[{content}]\n</body>\n\n</html>');

-- --------------------------------------------------------

--
-- Table structure for table "plugins"
--

CREATE SEQUENCE plugin_id_seq START 10001;

CREATE TABLE IF NOT EXISTS "[{prefix}]plugins" (
  "id" integer NOT NULL,
  "folder" varchar(255) NOT NULL,
  "active" integer,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE plugin_id_seq OWNED BY "[{prefix}]plugins"."id";

-- --------------------------------------------------------

--
-- Table structure for table "sites"
--

CREATE SEQUENCE site_id_seq START 6001;

CREATE TABLE IF NOT EXISTS "[{prefix}]sites" (
  "id" integer NOT NULL,
  "domain" varchar(255) NOT NULL,
  "aliases" varchar(255),
  "docroot" varchar(255),
  "content_type" varchar(255),
  "template_id" integer,
  "title" text,
  "error" text,
  "datetime_format" varchar(255),
  "separator" varchar(255),
  "media_formats" text,
  "media_filesize" integer,
  "media_actions" text,
  "history_limit" integer,
  "feed_limit" integer,
  "pagination_limit" integer,
  "pagination_range" integer,
  "force_ssl" integer,
  "cache_type" varchar(255),
  "cache_limit" integer,
  "live" integer,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE site_id_seq OWNED BY "[{prefix}]sites"."id";

--
-- Dumping data for table "sites"
--

INSERT INTO "[{prefix}]sites" ("id", "domain", "aliases", "docroot", "content_type", "template_id", "title", "error", "datetime_format", "separator", "media_formats", "media_filesize", "media_actions", "history_limit", "feed_limit", "pagination_limit", "pagination_range", "force_ssl", "cache_type", "cache_limit", "live") VALUES
(6001, '', '', '', 'text/html', 0, 'My Default Site', '<p>We''re sorry. That page was not found.</p>\n', 'M j Y g:i A', ' > ', 'a:24:{s:3:"bz2";s:17:"application/bzip2";s:3:"csv";s:8:"text/csv";s:3:"doc";s:18:"application/msword";s:4:"docx";s:18:"application/msword";s:3:"gif";s:9:"image/gif";s:2:"gz";s:18:"application/x-gzip";s:3:"jpe";s:10:"image/jpeg";s:3:"jpg";s:10:"image/jpeg";s:4:"jpeg";s:10:"image/jpeg";s:3:"pdf";s:15:"application/pdf";s:3:"png";s:9:"image/png";s:3:"ppt";s:18:"application/msword";s:4:"pptx";s:18:"application/msword";s:3:"svg";s:13:"image/svg+xml";s:3:"swf";s:29:"application/x-shockwave-flash";s:3:"tar";s:17:"application/x-tar";s:3:"tgz";s:18:"application/x-gzip";s:3:"tif";s:10:"image/tiff";s:4:"tiff";s:10:"image/tiff";s:3:"tsv";s:8:"text/tsv";s:3:"txt";s:10:"text/plain";s:3:"xls";s:18:"application/msword";s:4:"xlsx";s:18:"application/msword";s:3:"zip";s:17:"application/x-zip";}', 10000000, 'a:4:{s:5:"large";a:1:{s:6:"resize";i:800;}s:6:"medium";a:1:{s:6:"resize";i:400;}s:5:"small";a:1:{s:6:"resize";i:120;}s:5:"thumb";a:1:{s:9:"cropThumb";i:60;}}', 5, 0, 25, 10, 0, '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table "site_objects"

--
CREATE TABLE IF NOT EXISTS "[{prefix}]site_objects" (
  "id" integer NOT NULL,
  "site_id" integer NOT NULL,
  "object" varchar(255) NOT NULL,  -- content, plugin, section, template, theme, user, user_type, etc.
  UNIQUE ("id", "site_id", "object"),
  CONSTRAINT "fk_site_object" FOREIGN KEY ("site_id") REFERENCES "[{prefix}]sites" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "themes"
--

CREATE SEQUENCE theme_id_seq START 9001;

CREATE TABLE IF NOT EXISTS "[{prefix}]themes" (
  "id" integer NOT NULL,
  "folder" varchar(255) NOT NULL,
  "active" integer,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE theme_id_seq OWNED BY "[{prefix}]themes"."id";

-- --------------------------------------------------------

--
-- Table structure for table "templates"
--

CREATE SEQUENCE template_id_seq START 8001;

CREATE TABLE IF NOT EXISTS "[{prefix}]templates" (
  "id" integer NOT NULL,
  "theme_id" integer,
  "file" varchar(255),
  "parent_id" integer,
  "device" varchar(255),
  "name" varchar(255),
  "template" text,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_theme_template" FOREIGN KEY ("theme_id") REFERENCES "[{prefix}]themes" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE template_id_seq OWNED BY "[{prefix}]templates"."id";

-- --------------------------------------------------------

--
-- Table structure for table "user_types"
--

CREATE SEQUENCE type_id_seq START 2001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_types" (
  "id" integer NOT NULL DEFAULT nextval('type_id_seq'),
  "type" varchar(255) NOT NULL,
  "login" integer,
  "registration" integer,
  "multiple_sessions" integer,
  "mobile_access" integer,
  "email_as_username" integer,
  "force_ssl" integer,
  "track_sessions" integer,
  "verification" integer,
  "approval" integer,
  "unsubscribe_login" integer,
  "global_access" integer,
  "allowed_attempts" integer,
  "session_expiration" integer,
  "password_encryption" integer,
  "password_salt" text,
  "ip_allowed" text,
  "ip_blocked" text,
  "log_emails" text,
  "log_exclude" text,
  "controller" text,
  "sub_controllers" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE type_id_seq OWNED BY "[{prefix}]user_types"."id";

--
-- Dumping data for table "user_types"
--

INSERT INTO "[{prefix}]user_types" ("id", "type", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude", "controller", "sub_controllers") VALUES
(2001, 'user', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 2, '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table "user_roles"
--

CREATE SEQUENCE role_id_seq START 3001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_roles" (
  "id" integer NOT NULL DEFAULT nextval('role_id_seq'),
  "type_id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_role_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE role_id_seq OWNED BY "[{prefix}]user_roles"."id";

--
-- Dumping data for table "user_roles"
--

INSERT INTO "[{prefix}]user_roles" ("id", "type_id", "name") VALUES
(3001, 2001, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table "user_permissions"
--

CREATE SEQUENCE permission_id_seq START 4001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_permissions" (
  "id" integer NOT NULL DEFAULT nextval('permission_id_seq'),
  "role_id" integer NOT NULL,
  "resource" varchar(255),
  "permissions" varchar(255),
  UNIQUE ("role_id", "resource"),
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_permission_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE permission_id_seq OWNED BY "[{prefix}]user_permissions"."id";

-- --------------------------------------------------------

--
-- Table structure for table "users"
--

CREATE SEQUENCE user_id_seq START 1001;

CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL DEFAULT nextval('user_id_seq'),
  "type_id" integer,
  "role_id" integer,
  "first_name" varchar(255) NOT NULL,
  "last_name" varchar(255) NOT NULL,
  "email" varchar(255) NOT NULL,
  "username" varchar(255) NOT NULL,
  "password" varchar(255) NOT NULL,
  "address" varchar(255),
  "city" varchar(255),
  "state" varchar(255),
  "zip" varchar(255),
  "country" varchar(255),
  "phone" varchar(255),
  "organization" varchar(255),
  "position" varchar(255),
  "birth_date" date,
  "gender" varchar(1),
  "updates" integer,
  "verified" integer,
  "last_login" timestamp,
  "last_ua" varchar(255),
  "last_ip" varchar(255),
  "failed_attempts" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE user_id_seq OWNED BY "[{prefix}]users"."id";

-- --------------------------------------------------------

--
-- Table structure for table "user_sessions"
--

CREATE SEQUENCE session_id_seq START 5001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_sessions" (
  "id" integer NOT NULL DEFAULT nextval('session_id_seq'),
  "user_id" integer,
  "ip" varchar(255) NOT NULL,
  "ua" varchar(255) NOT NULL,
  "start" timestamp NOT NULL,
  "last" timestamp NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_session_user" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE session_id_seq OWNED BY "[{prefix}]user_sessions"."id";

-- --------------------------------------------------------

--
-- Table structure for table "media"
--

CREATE SEQUENCE media_id_seq START 11001;

CREATE TABLE IF NOT EXISTS "[{prefix}]media" (
  "id" integer NOT NULL,
  "content_type" varchar(255),
  "file" text NOT NULL,
  "title" text,
  "caption" text,
  "description" text,
  "order" integer,
  "uploaded" timestamp,
  "updated" timestamp,
  "created_by" integer,
  "updated_by" integer,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE media_id_seq OWNED BY "[{prefix}]media"."id";

-- --------------------------------------------------------

--
-- Table structure for table "content"
--

CREATE SEQUENCE content_id_seq START 7001;

CREATE TABLE IF NOT EXISTS "[{prefix}]content" (
  "id" integer NOT NULL,
  "parent_id" integer,
  "template_id" integer,
  "media_id" integer,
  "content_type" varchar(255),
  "uri" text NOT NULL,
  "title" text NOT NULL,
  "description" text,
  "content" text,
  "requests" integer,
  "comments" integer,
  "feed" integer,
  "force_ssl" integer,
  "order" integer,
  "roles" text,
  "private" integer,
  "live" integer,
  "created" timestamp,
  "published" timestamp,
  "expires" timestamp,
  "updated" timestamp,
  "created_by" integer,
  "updated_by" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_content_template" FOREIGN KEY ("template_id") REFERENCES "[{prefix}]templates" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_content_media" FOREIGN KEY ("media_id") REFERENCES "[{prefix}]media" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE content_id_seq OWNED BY "[{prefix}]content"."id";

--
-- Dumping data for table "[{prefix}]content"
--

INSERT INTO "[{prefix}]content" ("id", "parent_id", "template_id", "media_id", "content_type", "uri", "title", "description", "content", "requests", "feed", "force_ssl", "order", "roles", "private", "live", "created", "published", "expires", "updated", "created_by", "updated_by") VALUES
(7001, 0, NULL, NULL, 'text/html', '/', 'My Home Page', 'My home page description', '        <p>This is my home page.</p>\n', 0, 1, 0, 0, '', 0, 1, NULL, NULL, NULL, NULL, 1001, NULL),
(7002, 0, NULL, NULL, 'text/html', '/about', 'My About Page', 'My about page description', '        <p>This is my about page.</p>\n', 0, 1, 0, 0, '', 0, 1, NULL, NULL, NULL, NULL, 1001, NULL);
