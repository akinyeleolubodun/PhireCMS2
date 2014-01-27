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
('system_title', 'Phire CMS 2.0'),
('system_email', ''),
('site_title', 'Default Site Title'),
('separator', '&gt;'),
('default_language', 'en_US'),
('error_message', 'Sorry. That page was not found.'),
('datetime_format', 'M j Y g:i A'),
('media_allowed_types', 'a:27:{i:0;s:2:"ai";i:1;s:3:"bz2";i:2;s:3:"csv";i:3;s:3:"doc";i:4;s:4:"docx";i:5;s:3:"eps";i:6;s:3:"gif";i:7;s:2:"gz";i:8;s:4:"html";i:9;s:3:"htm";i:10;s:3:"jpe";i:11;s:3:"jpg";i:12;s:4:"jpeg";i:13;s:3:"pdf";i:14;s:3:"png";i:15;s:3:"ppt";i:16;s:4:"pptx";i:17;s:3:"psd";i:18;s:3:"svg";i:19;s:3:"swf";i:20;s:3:"tar";i:21;s:3:"txt";i:22;s:3:"xls";i:23;s:4:"xlsx";i:24;s:5:"xhtml";i:25;s:3:"xml";i:26;s:3:"zip";}'),
('media_max_filesize', '25000000'),
('media_actions', 'a:4:{s:5:"large";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:720;s:7:"quality";i:60;}s:6:"medium";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:480;s:7:"quality";i:60;}s:5:"small";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:240;s:7:"quality";i:60;}s:5:"thumb";a:3:{s:6:"action";s:9:"cropThumb";s:6:"params";i:60;s:7:"quality";i:60;}}'),
('media_image_adapter', 'Gd'),
('feed_type', '9'),
('feed_limit', '20'),
('open_authoring', '1'),
('incontent_editing', '0'),
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
  "timeout_warning" integer,
  "password_encryption" integer,
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

INSERT INTO "[{prefix}]user_types" ("type", "default_role_id", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "email_verification", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "timeout_warning", "password_encryption", "ip_allowed", "ip_blocked", "log_emails", "log_exclude", "controller", "sub_controllers") VALUES
('user', 3001, 1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 30, 0, 2, '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table "user_roles"
--

CREATE SEQUENCE role_id_seq START 3001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_roles" (
  "id" integer NOT NULL DEFAULT nextval('role_id_seq'),
  "type_id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "permissions" text,
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
  "site_ids" text,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
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
  "site_id" integer,
  "type_id" integer,
  "parent_id" integer,
  "template" varchar(255),
  "title" varchar(255) NOT NULL,
  "uri" varchar(255) NOT NULL,
  "slug" varchar(255) NOT NULL,
  "feed" integer,
  "force_ssl" integer,
  "status" integer,
  "roles" text,
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

INSERT INTO "[{prefix}]content" ("site_id", type_id", "parent_id", "title", "uri", "slug", "feed", "force_ssl", "status") VALUES
(0, 5001, NULL, 'Welcome', '/', '', 1, 0, 2),
(0, 5001, 6001, 'About', '/about', 'about', 1, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table "navigation"
--

CREATE SEQUENCE navigation_id_seq START 7001;

CREATE TABLE IF NOT EXISTS "[{prefix}]navigation" (
  "id" integer NOT NULL DEFAULT nextval('navigation_id_seq'),
  "navigation" varchar(255) NOT NULL,
  "spaces" integer,
  "top_node" varchar(255),
  "top_id" varchar(255),
  "top_class" varchar(255),
  "top_attributes" varchar(255),
  "parent_node" varchar(255),
  "parent_id" varchar(255),
  "parent_class" varchar(255),
  "parent_attributes" varchar(255),
  "child_node" varchar(255),
  "child_id" varchar(255),
  "child_class" varchar(255),
  "child_attributes" varchar(255),
  "on_class" varchar(255),
  "off_class" varchar(255),
  PRIMARY KEY ("id")
) ;

--
-- Dumping data for table "navigation"
--

INSERT INTO "[{prefix}]navigation" ("navigation", "spaces", "top_node", "top_id") VALUES
('Main Nav', 4, 'ul', 'main-nav');

-- --------------------------------------------------------

--
-- Table structure for table "categories"
--

CREATE SEQUENCE category_id_seq START 8001;

CREATE TABLE IF NOT EXISTS "[{prefix}]categories" (
  "id" integer NOT NULL DEFAULT nextval('category_id_seq'),
  "parent_id" integer,
  "title" varchar(255) NOT NULL,
  "uri" varchar(255) NOT NULL,
  "slug" varchar(255) NOT NULL,
  "order" integer NOT NULL,
  "total" integer NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_category_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]categories" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE category_id_seq OWNED BY "[{prefix}]categories"."id";

-- --------------------------------------------------------

--
-- Table structure for table "content_to_categories"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]content_to_categories" (
  "content_id" integer NOT NULL,
  "category_id" integer NOT NULL,
  UNIQUE ("content_id", "category_id"),
  CONSTRAINT "fk_category_content_id" FOREIGN KEY ("content_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_content_category_id" FOREIGN KEY ("category_id") REFERENCES "[{prefix}]categories" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "navigation_tree"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]navigation_tree" (
  "navigation_id" integer NOT NULL,
  "content_id" integer,
  "category_id" integer,
  "order" integer NOT NULL,
  UNIQUE ("navigation_id", "content_id", "category_id"),
  CONSTRAINT "fk_navigation_id" FOREIGN KEY ("navigation_id") REFERENCES "[{prefix}]navigation" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_navigation_content_id" FOREIGN KEY ("content_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

--
-- Dumping data for table "navigation_tree"
--

INSERT INTO "[{prefix}]navigation_tree" ("navigation_id", "content_id", "category_id", "order") VALUES
(7001, 6001, NULL, 1),
(7001, 6002, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table "templates"
--

CREATE SEQUENCE template_id_seq START 9001;

CREATE TABLE IF NOT EXISTS "[{prefix}]templates" (
  "id" integer NOT NULL DEFAULT nextval('template_id_seq'),
  "parent_id" integer,
  "name" varchar(255) NOT NULL,
  "content_type" varchar(255) NOT NULL,
  "device" varchar(255) NOT NULL,
  "template" text NOT NULL,
PRIMARY KEY ("id"),
CONSTRAINT "fk_template_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]templates" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE template_id_seq OWNED BY "[{prefix}]templates"."id";

-- --------------------------------------------------------

--
-- Table structure for table "extensions"
--

CREATE SEQUENCE extension_id_seq START 10001;

CREATE TABLE IF NOT EXISTS "[{prefix}]extensions" (
  "id" integer NOT NULL DEFAULT nextval('extension_id_seq'),
  "name" varchar(255) NOT NULL,
  "file" varchar(255) NOT NULL,
  "type" integer NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE extension_id_seq OWNED BY "[{prefix}]extensions"."id";

--
-- Dumping data for table "extensions"
--

INSERT INTO "[{prefix}]extensions" ("name", "file", "type", "active", "assets") VALUES
('default', 'default.tar.gz', 0, 1, 'a:2:{s:9:"templates";a:7:{i:0;s:12:"header.phtml";i:1;s:11:"error.phtml";i:2;s:12:"search.phtml";i:3;s:10:"date.phtml";i:4;s:14:"category.phtml";i:5;s:12:"footer.phtml";i:6;s:11:"index.phtml";}s:4:"info";a:4:{s:10:"Theme Name";s:13:"Default Theme";s:6:"Author";s:11:"Nick Sagona";s:11:"Description";s:41:"This is a default theme for Phire CMS 2.0";s:7:"Version";s:3:"1.0";}}');

-- --------------------------------------------------------

--
-- Table structure for table "field_groups"
--

CREATE SEQUENCE group_id_seq START 12001;

CREATE TABLE IF NOT EXISTS "[{prefix}]field_groups" (
  "id" integer NOT NULL DEFAULT nextval('group_id_seq'),
  "name" varchar(255),
  "order" integer,
  "dynamic" integer,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE group_id_seq OWNED BY "[{prefix}]field_groups"."id";

-- --------------------------------------------------------

--
-- Table structure for table "fields"
--

CREATE SEQUENCE field_id_seq START 11001;

CREATE TABLE IF NOT EXISTS "[{prefix}]fields" (
  "id" integer NOT NULL DEFAULT nextval('field_id_seq'),
  "group_id" integer,
  "type" varchar(255),
  "name" varchar(255),
  "label" varchar(255),
  "values" varchar(255),
  "default_values" varchar(255),
  "attributes" varchar(255),
  "validators" varchar(255),
  "encryption" integer NOT NULL,
  "order" integer NOT NULL,
  "required" integer NOT NULL,
  "editor" varchar(255),
  "models" text,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_group_id" FOREIGN KEY ("group_id") REFERENCES "[{prefix}]field_groups" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE field_id_seq OWNED BY "[{prefix}]fields"."id";

-- --------------------------------------------------------

--
-- Table structure for table "field_values"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]field_values" (
  "field_id" integer NOT NULL,
  "model_id" integer NOT NULL,
  "value" text,
  "timestamp" integer,
  "history" text,
  UNIQUE ("field_id", "model_id"),
  CONSTRAINT "fk_field_id" FOREIGN KEY ("field_id") REFERENCES "[{prefix}]fields" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "sites"
--

CREATE SEQUENCE site_id_seq START 13001;

CREATE TABLE IF NOT EXISTS "[{prefix}]sites" (
  "id" integer NOT NULL DEFAULT nextval('site_id_seq'),
  "domain" varchar(255) NOT NULL,
  "document_root" varchar(255) NOT NULL,
  "base_path" varchar(255) NOT NULL,
  "title" varchar(255) NOT NULL,
  "force_ssl" integer NOT NULL,
  "live" integer NOT NULL,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE site_id_seq OWNED BY "[{prefix}]sites"."id";