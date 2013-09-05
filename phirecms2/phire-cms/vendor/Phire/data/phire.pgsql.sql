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
('system_document_root', ''),
('server_operating_system', ''),
('server_software', ''),
('database_version', ''),
('php_version', ''),
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('site_title', 'Default Site Title'),
('separator', '&gt;'),
('default_language', 'en_US'),
('error_message', 'Sorry. That page was not found.'),
('datetime_format', 'M j Y g:i A'),
('media_allowed_types', 'a:27:{i:0;s:2:"ai";i:1;s:3:"bz2";i:2;s:3:"csv";i:3;s:3:"doc";i:4;s:4:"docx";i:5;s:3:"eps";i:6;s:3:"gif";i:7;s:2:"gz";i:8;s:4:"html";i:9;s:3:"htm";i:10;s:3:"jpe";i:11;s:3:"jpg";i:12;s:4:"jpeg";i:13;s:3:"pdf";i:14;s:3:"png";i:15;s:3:"ppt";i:16;s:4:"pptx";i:17;s:3:"psd";i:18;s:3:"svg";i:19;s:3:"swf";i:20;s:3:"tar";i:21;s:3:"txt";i:22;s:3:"xls";i:23;s:4:"xlsx";i:24;s:5:"xhtml";i:25;s:3:"xml";i:26;s:3:"zip";}'),
('media_max_filesize', '25000000'),
('media_actions', 'a:4:{s:5:"large";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:720;s:7:"quality";i:60;}s:6:"medium";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:480;s:7:"quality";i:60;}s:5:"small";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:240;s:7:"quality";i:60;}s:5:"thumb";a:3:{s:6:"action";s:9:"cropThumb";s:6:"params";i:60;s:7:"quality";i:60;}}'),
('media_image_adapter', 'Gd'),
('category_totals', '1'),
('feed_type', '10'),
('feed_limit', '20'),
('open_authoring', '1'),
('pagination_limit', '25'),
('pagination_range', '10'),
('force_ssl', '0'),
('live', '1');

-- --------------------------------------------------------

--
-- Table structure for table "user_types"
--

CREATE SEQUENCE type_id_seq START 2001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_types" (
  "id" integer NOT NULL DEFAULT nextval('type_id_seq'),
  "type" varchar(255) NOT NULL,
  "default_role_id" integer,
  "login" integer,
  "registration" integer,
  "multiple_sessions" integer,
  "mobile_access" integer,
  "email_as_username" integer,
  "email_verification" integer,
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

INSERT INTO "[{prefix}]user_types" ("type", "default_role_id", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "email_verification", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude", "controller", "sub_controllers") VALUES
('user', 3001, 1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 30, 2, '', '', '', '', '', '', '');

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

INSERT INTO "[{prefix}]user_roles" ("type_id", "name") VALUES
(2001, 'Admin');

ALTER TABLE "[{prefix}]user_types" ADD CONSTRAINT "fk_default_role" FOREIGN KEY ("default_role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table "user_permissions"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_permissions" (
  "role_id" integer NOT NULL,
  "resource" varchar(255),
  "permission" varchar(255),
  "allow" integer,
  UNIQUE ("role_id", "resource", "permission"),
  CONSTRAINT "fk_permission_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "users"
--

CREATE SEQUENCE user_id_seq START 1001;

CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL DEFAULT nextval('user_id_seq'),
  "type_id" integer,
  "role_id" integer,
  "username" varchar(255) NOT NULL,
  "password" varchar(255) NOT NULL,
  "email" varchar(255) NOT NULL,
  "verified" integer,
  "logins" text,
  "failed_attempts" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE user_id_seq OWNED BY "[{prefix}]users"."id";

--
-- Dumping data for table "users"
--

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

--
-- Dumping data for table "user_sessions"
--

-- --------------------------------------------------------

--
-- Table structure for table "content_types"
--

CREATE SEQUENCE content_type_id_seq START 5001;

CREATE TABLE IF NOT EXISTS "[{prefix}]content_types" (
  "id" integer NOT NULL DEFAULT nextval('content_type_id_seq'),
  "name" varchar(255) NOT NULL,
  "uri" integer NOT NULL,
  "order" integer NOT NULL,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE content_type_id_seq OWNED BY "[{prefix}]content_types"."id";

--
-- Dumping data for table "content_types"
--

INSERT INTO "[{prefix}]content_types" ("name", "uri", "order") VALUES
('Page', 1, 1),
('Media', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table "content"
--

CREATE SEQUENCE content_id_seq START 6001;

CREATE TABLE IF NOT EXISTS "[{prefix}]content" (
  "id" integer NOT NULL DEFAULT nextval('content_id_seq'),
  "type_id" integer,
  "parent_id" integer,
  "template" varchar(255),
  "title" varchar(255) NOT NULL,
  "uri" varchar(255) NOT NULL,
  "slug" varchar(255) NOT NULL,
  "order" integer NOT NULL,
  "include" integer,
  "feed" integer,
  "force_ssl" integer,
  "status" integer,
  "created" timestamp,
  "updated" timestamp,
  "published" timestamp,
  "expired" timestamp,
  "created_by" integer,
  "updated_by" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_content_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_content_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]content_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_created_by" FOREIGN KEY ("created_by") REFERENCES "[{prefix}]users" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_updated_by" FOREIGN KEY ("updated_by") REFERENCES "[{prefix}]users" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE content_id_seq OWNED BY "[{prefix}]content"."id";

--
-- Dumping data for table "content"
--

INSERT INTO "[{prefix}]content" ("type_id", "title", "uri", "slug", "order", "include", "feed", "force_ssl", "status") VALUES
(5001, 'Home Page', '/', '', 0, 1, 1, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table "content_categories"
--

CREATE SEQUENCE category_id_seq START 7001;

CREATE TABLE IF NOT EXISTS "[{prefix}]content_categories" (
  "id" integer NOT NULL DEFAULT nextval('category_id_seq'),
  "parent_id" integer,
  "category" varchar(255) NOT NULL,
  "uri" varchar(255) NOT NULL,
  "slug" varchar(255) NOT NULL,
  "order" integer NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_category_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]content_categories" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE category_id_seq OWNED BY "[{prefix}]content_categories"."id";

-- --------------------------------------------------------

--
-- Table structure for table "content_to_categories"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]content_to_categories" (
  "content_id" integer NOT NULL,
  "category_id" integer NOT NULL,
  UNIQUE ("content_id", "category_id"),
  CONSTRAINT "fk_category_content_id" FOREIGN KEY ("content_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_content_category_id" FOREIGN KEY ("category_id") REFERENCES "[{prefix}]content_categories" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;


-- --------------------------------------------------------

--
-- Table structure for table "content_to_roles"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]content_to_roles" (
  "content_id" integer NOT NULL,
  "role_id" integer NOT NULL,
  UNIQUE ("content_id", "role_id"),
  CONSTRAINT "fk_role_content_id" FOREIGN KEY ("content_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_content_role_id" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "content_templates"
--

CREATE SEQUENCE template_id_seq START 8001;

CREATE TABLE IF NOT EXISTS "[{prefix}]content_templates" (
  "id" integer NOT NULL DEFAULT nextval('template_id_seq'),
  "parent_id" integer,
  "name" varchar(255) NOT NULL,
  "content_type" varchar(255) NOT NULL,
  "device" varchar(255) NOT NULL,
  "template" text NOT NULL,
PRIMARY KEY ("id"),
CONSTRAINT "fk_template_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]content_templates" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE template_id_seq OWNED BY "[{prefix}]content_templates"."id";

-- --------------------------------------------------------

--
-- Table structure for table "extensions"
--

CREATE SEQUENCE extension_id_seq START 9001;

CREATE TABLE IF NOT EXISTS "[{prefix}]extensions" (
  "id" integer NOT NULL DEFAULT nextval('extension_id_seq'),
  "name" varchar(255) NOT NULL,
  "type" integer NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE extension_id_seq OWNED BY "[{prefix}]extensions"."id";
