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
-- Table structure for table "content_types"
--

CREATE SEQUENCE content_type_id_seq START 7001;

CREATE TABLE IF NOT EXISTS "[{prefix}]content_types" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "type" varchar(255) NOT NULL,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE content_type_id_seq OWNED BY "[{prefix}]content_types"."id";

--
-- Dumping data for table "content_types"
--

INSERT INTO "[{prefix}]content_types" ("id", "name", "type") VALUES
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
-- Table structure for table "plugins"
--

CREATE SEQUENCE plugin_id_seq START 14001;

CREATE TABLE IF NOT EXISTS "[{prefix}]plugins" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "author" varchar(255),
  "version" varchar(255),
  "description" varchar(255),
  "file" varchar(255) NOT NULL,
  "folder" varchar(255) NOT NULL,
  "tables" text NOT NULL,
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
  "default_content_type_id" integer,
  "default_template_id" integer,
  "default_title" text,
  "default_404" text,
  "default_datetime_format" varchar(255),
  "separator" varchar(255),
  "media_formats" text,
  "media_filesize" integer,
  "media_actions" varchar(255),
  "media_sizes" varchar(255),
  "comments" integer,
  "anonymous_comments" integer,
  "comment_approval" integer,
  "captcha_type" varchar(255),
  "spam_filter" text,
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

INSERT INTO "[{prefix}]sites" ("id", "domain", "aliases", "docroot", "default_content_type_id", "default_template_id", "default_title", "default_404", "default_datetime_format", "separator", "media_formats", "media_filesize", "media_actions", "media_sizes", "comments", "anonymous_comments", "captcha_type", "spam_filter", "feed_limit", "pagination_limit", "pagination_range", "force_ssl", "cache_type", "cache_limit", "live") VALUES
(6001, '', '', '', 7001, 0, 'My Default Site', '<p>We''re sorry. That page was not found.</p>\n', 'M j Y g:i A', ' > ', 'jpg|jpe|jpeg|gif|png', 10000000, 'resize|resize|resize|cropThumb', '800|400|120|60', 0, 0, '', '', 0, 25, 10, 0, '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table "site_relationships"

--
CREATE TABLE IF NOT EXISTS "[{prefix}]site_relationships" (
  "id" integer NOT NULL,
  "site_id" integer NOT NULL,
  "relationship" varchar(255) NOT NULL,  -- content, plugin, section, template, theme, user, user_type, etc.
  UNIQUE ("id", "site_id", "relationship"),
  CONSTRAINT "fk_site_relationship" FOREIGN KEY ("site_id") REFERENCES "[{prefix}]sites" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "templates"
--

CREATE SEQUENCE template_id_seq START 10001;

CREATE TABLE IF NOT EXISTS "[{prefix}]templates" (
  "id" integer NOT NULL,
  "parent_id" integer,
  "content_type_id" integer,
  "device" varchar(255),
  "name" varchar(255),
  "template" text,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_template_content_type" FOREIGN KEY ("content_type_id") REFERENCES "[{prefix}]content_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE template_id_seq OWNED BY "[{prefix}]templates"."id";

-- --------------------------------------------------------

--
-- Table structure for table "themes"
--

CREATE SEQUENCE theme_id_seq START 13001;

CREATE TABLE IF NOT EXISTS "[{prefix}]themes" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "author" varchar(255),
  "version" varchar(255),
  "description" varchar(255),
  "file" varchar(255) NOT NULL,
  "folder" varchar(255) NOT NULL,
  "active" integer,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE theme_id_seq OWNED BY "[{prefix}]themes"."id";

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
(2001, 'user', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 2, '', '', '', '', '', '', ''),
(2002, 'member', 1, 1, 1, 1, 1, 0, 1, 1, 1, 0, 0, 0, 0, 2, '', '', '', '', '', '', '');

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
(3001, 2001, 'Admin'),
(3002, 2002, 'Full');

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

CREATE SEQUENCE session_id_seq START 4001;

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
-- Table structure for table "sections"
--

CREATE SEQUENCE section_id_seq START 11001;

CREATE TABLE IF NOT EXISTS "[{prefix}]sections" (
  "id" integer NOT NULL,
  "uri" varchar(255) NOT NULL,
  "title" varchar(255) NOT NULL,
  "parent_id" integer,
  "short_template_id" integer,
  "long_template_id" integer,
  "short_limit" integer,
  "long_limit" integer,
  "sort_order" varchar(255),
  "paginate" integer,
  "requests" integer,
  "role_id" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_section_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE section_id_seq OWNED BY "[{prefix}]sections"."id";

-- --------------------------------------------------------

--
-- Table structure for table "content"
--

CREATE SEQUENCE content_id_seq START 8001;

CREATE TABLE IF NOT EXISTS "[{prefix}]content" (
  "id" integer NOT NULL,
  "parent_id" integer,
  "template_id" integer,
  "section_id" integer,
  "media_id" integer,
  "content_type_id" integer,
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
  "created" timestamp,
  "published" timestamp,
  "expires" timestamp,
  "updated" timestamp,
  "created_by" integer,
  "updated_by" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_content_template" FOREIGN KEY ("template_id") REFERENCES "[{prefix}]templates" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_content_section" FOREIGN KEY ("section_id") REFERENCES "[{prefix}]sections" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_content_content_type" FOREIGN KEY ("content_type_id") REFERENCES "[{prefix}]content_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_content_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE content_id_seq OWNED BY "[{prefix}]content"."id";

-- --------------------------------------------------------

--
-- Table structure for table "comments"
--

CREATE SEQUENCE comment_id_seq START 12001;

CREATE TABLE IF NOT EXISTS "[{prefix}]comments" (
  "id" integer NOT NULL,
  "content_id" integer NOT NULL,
  "parent_id" integer,
  "user_id" integer,
  "name" varchar(255),
  "email" varchar(255),
  "content" text NOT NULL,
  "ip" varchar(255) NOT NULL,
  "ua" varchar(255) NOT NULL,
  "posted" timestamp NOT NULL,
  "approved" integer NOT NULL,
  "spam" integer NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_comment_content" FOREIGN KEY ("content_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_comment_user" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE comment_id_seq OWNED BY "[{prefix}]comments"."id";

-- --------------------------------------------------------

--
-- Table structure for table "fields"
--

CREATE SEQUENCE field_id_seq START 15001;

CREATE TABLE IF NOT EXISTS "[{prefix}]fields" (
  "id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "type" varchar(255) NOT NULL, -- input (text, file, etc), checkbox, radio, select, textarea, etc
  "attributes" varchar(255),    -- field attributes, i.e., size="40", rows="5", etc
  "values" varchar(255),        -- values for a selectable field type
  "default" varchar(255),       -- default value or values
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE field_id_seq OWNED BY "[{prefix}]fields"."id";

--
-- Dumping data for table "fields"
--

INSERT INTO "[{prefix}]fields" ("id", "name", "type", "attributes") VALUES
(15001, 'keywords', 'text', 'size="80"'),
(15002, 'description', 'text', 'size="80"');

-- --------------------------------------------------------

--
-- Table structure for table "field_values"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]field_values" (
  "content_id" integer NOT NULL,
  "field_id" integer NOT NULL,
  "value" text NOT NULL,
  UNIQUE ("content_id", "field_id"),
  CONSTRAINT "fk_field_content" FOREIGN KEY ("content_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "feeds"
--

CREATE SEQUENCE feed_id_seq START 16001;

CREATE TABLE IF NOT EXISTS "[{prefix}]feeds" (
  "id" integer NOT NULL,
  "uri" varchar(255) NOT NULL,
  "title" varchar(255) NOT NULL,
  "template_id" integer,
  "feed_limit" integer,
  "cache_type" varchar(255),
  "cache_limit" integer,
  "role_id" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_feed_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE feed_id_seq OWNED BY "[{prefix}]feeds"."id";

-- --------------------------------------------------------

--
-- Table structure for table "tags"
--

CREATE SEQUENCE tag_id_seq START 17001;

CREATE TABLE IF NOT EXISTS "[{prefix}]tags" (
  "id" integer NOT NULL,
  "tag" varchar(255) NOT NULL,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE tag_id_seq OWNED BY "[{prefix}]tags"."id";

-- --------------------------------------------------------

--
-- Table structure for table "tagged_content"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]tagged_content" (
  "tag_id" integer NOT NULL,
  "content_id" integer NOT NULL,
  UNIQUE ("tag_id", "content_id"),
  CONSTRAINT "fk_tag_id" FOREIGN KEY ("tag_id") REFERENCES "[{prefix}]tags" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_tag_content_id" FOREIGN KEY ("content_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;
