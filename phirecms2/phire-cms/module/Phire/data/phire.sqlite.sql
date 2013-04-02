--
-- Phire CMS 2.0 SQLite Database
--

--
-- Set database encoding
--

PRAGMA encoding = "UTF-8";
PRAGMA foreign_keys = ON;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS "[{prefix}]config" (
  "setting" varchar NOT NULL PRIMARY KEY,
  "value" text NOT NULL,
  UNIQUE ("setting")
) ;

--
-- Dumping data for table `config`
--

INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('system_version', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('server_os', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('server_software', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('db_version', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('php_version', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('installed_on', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('updated_on', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('table_optimization', '0');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('optimization_period', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('last_optimization', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('pagination_limit', '25');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('pagination_range', '10');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('default_editor', 'Source');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('default_template', '<!DOCTYPE html>\n<!-- Header //-->\n<html>\n\n<head>\n\n    <title>\n        [{page_title}]\n    </title>\n\n    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n\n</head>\n\n<body>\n    <h1>[{page_sub_title}]</h1>\n[{page_content}]\n</body>\n\n</html>');

-- --------------------------------------------------------

--
-- Table structure for table "plugins"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]plugins" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "author" varchar,
  "version" varchar,
  "description" varchar,
  "file" varchar NOT NULL,
  "folder" varchar NOT NULL,
  "tables" text NOT NULL,
  "active" integer,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]plugins', 10000);

-- --------------------------------------------------------

--
-- Table structure for table "sites"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]sites" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "domain" varchar NOT NULL,
  "aliases" varchar,
  "docroot" varchar,
  "default_content_type" varchar,
  "default_template_id" integer,
  "default_title" text,
  "default_404" text,
  "default_datetime_format" varchar,
  "separator" varchar,
  "media_formats" text,
  "media_filesize" integer,
  "media_actions" text,
  "history_limit" integer,
  "feed_limit" integer,
  "pagination_limit" integer,
  "pagination_range" integer,
  "force_ssl" integer,
  "cache_type" varchar,
  "cache_limit" integer,
  "live" integer,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]sites', 6000);

--
-- Dumping data for table "sites"
--

INSERT INTO "[{prefix}]sites" ("id", "domain", "aliases", "docroot", "default_content_type", "default_template_id", "default_title", "default_404", "default_datetime_format", "separator", "media_formats", "media_filesize", "media_actions", "history_limit", "feed_limit", "pagination_limit", "pagination_range", "force_ssl", "cache_type", "cache_limit", "live") VALUES
(6001, '', '', '', 'text/html', 0, 'My Default Site', '<p>We''re sorry. That page was not found.</p>\n', 'M j Y g:i A', ' > ', 'a:24:{s:3:"bz2";s:17:"application/bzip2";s:3:"csv";s:8:"text/csv";s:3:"doc";s:18:"application/msword";s:4:"docx";s:18:"application/msword";s:3:"gif";s:9:"image/gif";s:2:"gz";s:18:"application/x-gzip";s:3:"jpe";s:10:"image/jpeg";s:3:"jpg";s:10:"image/jpeg";s:4:"jpeg";s:10:"image/jpeg";s:3:"pdf";s:15:"application/pdf";s:3:"png";s:9:"image/png";s:3:"ppt";s:18:"application/msword";s:4:"pptx";s:18:"application/msword";s:3:"svg";s:13:"image/svg+xml";s:3:"swf";s:29:"application/x-shockwave-flash";s:3:"tar";s:17:"application/x-tar";s:3:"tgz";s:18:"application/x-gzip";s:3:"tif";s:10:"image/tiff";s:4:"tiff";s:10:"image/tiff";s:3:"tsv";s:8:"text/tsv";s:3:"txt";s:10:"text/plain";s:3:"xls";s:18:"application/msword";s:4:"xlsx";s:18:"application/msword";s:3:"zip";s:17:"application/x-zip";}', 10000000, 'a:4:{s:5:"large";a:1:{s:6:"resize";i:800;}s:6:"medium";a:1:{s:6:"resize";i:400;}s:5:"small";a:1:{s:6:"resize";i:120;}s:5:"thumb";a:1:{s:9:"cropThumb";i:60;}}', 5, 0, 25, 10, 0, '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table "site_objects"

--
CREATE TABLE IF NOT EXISTS "[{prefix}]site_objects" (
  "id" integer NOT NULL,
  "site_id" integer NOT NULL,
  "object" varchar NOT NULL,  -- content, plugin, section, template, theme, user, user_type, etc.
  UNIQUE ("id", "site_id", "object"),
  CONSTRAINT "fk_site_object" FOREIGN KEY ("site_id") REFERENCES "[{prefix}]sites" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "templates"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]templates" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "content_type" varchar,
  "device" varchar,
  "name" varchar,
  "template" text,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]templates', 8000);

-- --------------------------------------------------------

--
-- Table structure for table "themes"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]themes" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "author" varchar,
  "version" varchar,
  "description" varchar,
  "file" varchar NOT NULL,
  "folder" varchar NOT NULL,
  "active" integer,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]themes', 9000);

-- --------------------------------------------------------

--
-- Table structure for table "user_types"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_types" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type" varchar NOT NULL,
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
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]user_types', 2000);

--
-- Dumping data for table "user_types"
--

INSERT INTO "[{prefix}]user_types" ("id", "type", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude", "controller", "sub_controllers") VALUES
(2001, 'user', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 2, '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table "user_roles"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_roles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type_id" integer NOT NULL,
  "name" varchar NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_role_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]user_roles', 3000);

--
-- Dumping data for table "user_roles"
--

INSERT INTO "[{prefix}]user_roles" ("id", "type_id", "name") VALUES
(3001, 2001, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table "user_permissions"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_permissions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer NOT NULL,
  "resource" varchar,
  "permissions" varchar,
  UNIQUE ("role_id", "resource"),
  UNIQUE ("id"),
  CONSTRAINT "fk_permission_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]user_permissions', 4000);

-- --------------------------------------------------------

--
-- Table structure for table "users"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type_id" integer,
  "role_id" integer,
  "first_name" varchar NOT NULL,
  "last_name" varchar NOT NULL,
  "email" varchar NOT NULL,
  "username" varchar NOT NULL,
  "password" varchar NOT NULL,
  "address" varchar,
  "city" varchar,
  "state" varchar,
  "zip" varchar,
  "country" varchar,
  "phone" varchar,
  "organization" varchar,
  "position" varchar,
  "birth_date" date,
  "gender" varchar,
  "updates" integer,
  "verified" integer,
  "last_login" datetime,
  "last_ua" varchar,
  "last_ip" varchar,
  "failed_attempts" integer,
  UNIQUE ("id"),
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]users', 1000);

-- --------------------------------------------------------

--
-- Table structure for table "user_sessions"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_sessions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer,
  "ip" varchar NOT NULL,
  "ua" varchar NOT NULL,
  "start" datetime NOT NULL,
  "last" datetime NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_session_user" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]user_sessions', 4000);

-- --------------------------------------------------------

--
-- Table structure for table "content"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]content" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "template_id" integer,
  "media_id" integer,
  "content_type" varchar,
  "uri" text NOT NULL,
  "title" text NOT NULL,
  "description" text,
  "content" text,
  "requests" integer,
  "comments" integer,
  "feed" integer,
  "force_ssl" integer,
  "order" integer,
  "role_id" integer,
  "live" integer,
  "created" datetime,
  "published" datetime,
  "expires" datetime,
  "updated" datetime,
  "created_by" integer,
  "updated_by" integer,
  UNIQUE ("id"),
  CONSTRAINT "fk_content_template" FOREIGN KEY ("template_id") REFERENCES "[{prefix}]templates" ("id") ON DELETE SET NULL ON UPDATE CASCADE
  CONSTRAINT "fk_content_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]content', 7000);
