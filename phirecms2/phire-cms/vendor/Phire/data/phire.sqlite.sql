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
-- Table structure for table "ph_config"
--

CREATE TABLE IF NOT EXISTS "ph_config" (
  "setting" varchar NOT NULL PRIMARY KEY,
  "value" text NOT NULL,
  UNIQUE ("setting")
) ;

--
-- Dumping data for table "ph_config"
--

INSERT INTO "ph_config" ("setting", "value") VALUES ('system_version', '');
INSERT INTO "ph_config" ("setting", "value") VALUES ('system_document_root', '');
INSERT INTO "ph_config" ("setting", "value") VALUES ('server_operating_system', '');
INSERT INTO "ph_config" ("setting", "value") VALUES ('server_software', '');
INSERT INTO "ph_config" ("setting", "value") VALUES ('database_version', '');
INSERT INTO "ph_config" ("setting", "value") VALUES ('php_version', '');
INSERT INTO "ph_config" ("setting", "value") VALUES ('installed_on', '0000-00-00 00:00:00');
INSERT INTO "ph_config" ("setting", "value") VALUES ('updated_on', '0000-00-00 00:00:00');
INSERT INTO "ph_config" ("setting", "value") VALUES ('site_title', 'Default Site Title');
INSERT INTO "ph_config" ("setting", "value") VALUES ('separator', ' > ');
INSERT INTO "ph_config" ("setting", "value") VALUES ('default_language', 'en_US');
INSERT INTO "ph_config" ("setting", "value") VALUES ('error_message', 'Sorry. That page was not found.');
INSERT INTO "ph_config" ("setting", "value") VALUES ('datetime_format', 'M j Y g:i A');
INSERT INTO "ph_config" ("setting", "value") VALUES ('media_allowed_types', 'a:27:{i:0;s:2:"ai";i:1;s:3:"bz2";i:2;s:3:"csv";i:3;s:3:"doc";i:4;s:4:"docx";i:5;s:3:"eps";i:6;s:3:"gif";i:7;s:2:"gz";i:8;s:4:"html";i:9;s:3:"htm";i:10;s:3:"jpe";i:11;s:3:"jpg";i:12;s:4:"jpeg";i:13;s:3:"pdf";i:14;s:3:"png";i:15;s:3:"ppt";i:16;s:4:"pptx";i:17;s:3:"psd";i:18;s:3:"svg";i:19;s:3:"swf";i:20;s:3:"tar";i:21;s:3:"txt";i:22;s:3:"xls";i:23;s:4:"xlsx";i:24;s:5:"xhtml";i:25;s:3:"xml";i:26;s:3:"zip";}');
INSERT INTO "ph_config" ("setting", "value") VALUES ('media_max_filesize', '25000000');
INSERT INTO "ph_config" ("setting", "value") VALUES ('media_actions', 'a:4:{s:5:"large";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:720;s:7:"quality";i:60;}s:6:"medium";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:480;s:7:"quality";i:60;}s:5:"small";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:240;s:7:"quality";i:60;}s:5:"thumb";a:3:{s:6:"action";s:9:"cropThumb";s:6:"params";i:60;s:7:"quality";i:60;}}');
INSERT INTO "ph_config" ("setting", "value") VALUES ('media_image_adapter', 'Gd');
INSERT INTO "ph_config" ("setting", "value") VALUES ('category_totals', '1');
INSERT INTO "ph_config" ("setting", "value") VALUES ('feed_type', '10');
INSERT INTO "ph_config" ("setting", "value") VALUES ('feed_limit', '20');
INSERT INTO "ph_config" ("setting", "value") VALUES ('open_authoring', '1');
INSERT INTO "ph_config" ("setting", "value") VALUES ('pagination_limit', '25');
INSERT INTO "ph_config" ("setting", "value") VALUES ('pagination_range', '10');
INSERT INTO "ph_config" ("setting", "value") VALUES ('force_ssl', '0');
INSERT INTO "ph_config" ("setting", "value") VALUES ('live', '1');

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_types"
--

CREATE TABLE IF NOT EXISTS "ph_user_types" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type" varchar NOT NULL,
  "default_role_id" integer,
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

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_user_types', 2000);

--
-- Dumping data for table "ph_user_types"
--

INSERT INTO "ph_user_types" ("id", "type", "default_role_id", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude", "controller", "sub_controllers") VALUES
(2001, 'user', 3001, 1, 0, 1, 1, 0, 0, 1, 0, 0, 1, 1, 0, 0, 2, '', '', '', '', '', '', '');;

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_roles"
--

CREATE TABLE IF NOT EXISTS "ph_user_roles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type_id" integer NOT NULL,
  "name" varchar NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_role_type" FOREIGN KEY ("type_id") REFERENCES "ph_user_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_user_roles', 3000);

--
-- Dumping data for table "ph_user_roles"
--

INSERT INTO "ph_user_roles" ("id", "type_id", "name") VALUES
(3001, 2001, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_permissions"
--

CREATE TABLE IF NOT EXISTS "ph_user_permissions" (
  "role_id" integer NOT NULL,
  "resource" varchar,
  "permission" varchar,
  UNIQUE ("role_id", "resource", "permission"),
  CONSTRAINT "fk_permission_role" FOREIGN KEY ("role_id") REFERENCES "ph_user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "ph_users"
--

CREATE TABLE IF NOT EXISTS "ph_users" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type_id" integer,
  "role_id" integer,
  "username" varchar NOT NULL,
  "password" varchar NOT NULL,
  "email" varchar NOT NULL,
  "verified" integer,
  "logins" text,
  "failed_attempts" integer,
  UNIQUE ("id"),
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "ph_user_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "ph_user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_users', 1000);

--
-- Dumping data for table "ph_users"
--

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_sessions"
--

CREATE TABLE IF NOT EXISTS "ph_user_sessions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer,
  "ip" varchar NOT NULL,
  "ua" varchar NOT NULL,
  "start" datetime NOT NULL,
  "last" datetime NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_session_user" FOREIGN KEY ("user_id") REFERENCES "ph_users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_user_sessions', 4000);

--
-- Dumping data for table "ph_user_sessions"
--

-- --------------------------------------------------------

--
-- Table structure for table "ph_content_types"
--

CREATE TABLE IF NOT EXISTS "ph_content_types" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "uri" integer NOT NULL,
  "order" integer NOT NULL,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_content_types', 5000);

-- --------------------------------------------------------

--
-- Table structure for table "ph_content"
--

CREATE TABLE IF NOT EXISTS "ph_content" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type_id" integer,
  "parent_id" integer,
  "template" varchar,
  "title" varchar NOT NULL,
  "uri" varchar NOT NULL,
  "slug" varchar NOT NULL,
  "order" integer NOT NULL,
  "include" integer,
  "feed" integer,
  "status" integer,
  "created" datetime,
  "updated" datetime,
  "published" datetime,
  "expired" datetime,
  "created_by" integer,
  "updated_by" integer,
  UNIQUE ("id"),
  CONSTRAINT "fk_content_parent_id" FOREIGN KEY ("parent_id") REFERENCES "ph_content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_content_type" FOREIGN KEY ("type_id") REFERENCES "ph_content_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_created_by" FOREIGN KEY ("created_by") REFERENCES "ph_users" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_updated_by" FOREIGN KEY ("updated_by") REFERENCES "ph_users" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_content', 6000);

-- --------------------------------------------------------

--
-- Table structure for table "ph_content_categories"
--

CREATE TABLE IF NOT EXISTS "ph_content_categories" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "category" varchar NOT NULL,
  "uri" varchar NOT NULL,
  "slug" varchar NOT NULL,
  "order" integer NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_category_parent_id" FOREIGN KEY ("parent_id") REFERENCES "ph_content_categories" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_content_categories', 7000);

-- --------------------------------------------------------

--
-- Table structure for table "ph_content_to_categories"
--

CREATE TABLE IF NOT EXISTS "ph_content_to_categories" (
  "content_id" integer NOT NULL,
  "category_id" integer NOT NULL,
  UNIQUE ("content_id", "category_id"),
  CONSTRAINT "fk_category_content_id" FOREIGN KEY ("content_id") REFERENCES "ph_content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_content_category_id" FOREIGN KEY ("category_id") REFERENCES "ph_content_categories" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "ph_content_to_roles"
--

CREATE TABLE IF NOT EXISTS "ph_content_to_roles" (
  "content_id" integer NOT NULL,
  "role_id" integer NOT NULL,
  UNIQUE ("content_id", "role_id"),
  CONSTRAINT "fk_role_content_id" FOREIGN KEY ("content_id") REFERENCES "ph_content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_content_role_id" FOREIGN KEY ("role_id") REFERENCES "ph_user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "ph_content_templates"
--

CREATE TABLE IF NOT EXISTS "ph_content_templates" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "name" varchar NOT NULL,
  "content_type" varchar NOT NULL,
  "device" varchar NOT NULL,
  "template" text NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_template_parent_id" FOREIGN KEY ("parent_id") REFERENCES "ph_content_templates" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_content_templates', 8000);
