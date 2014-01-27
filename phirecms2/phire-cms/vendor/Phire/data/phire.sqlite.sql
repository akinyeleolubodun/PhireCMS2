--
-- Phire CMS 2.0 SQLite Database
--

-- --------------------------------------------------------

--
-- Set database encoding
--

PRAGMA encoding = "UTF-8";
PRAGMA foreign_keys = ON;

-- --------------------------------------------------------

--
-- Table structure for table "config"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]config" (
  "setting" varchar NOT NULL PRIMARY KEY,
  "value" text NOT NULL,
  UNIQUE ("setting")
) ;

--
-- Dumping data for table "config"
--

INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('system_version', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('system_document_root', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('server_operating_system', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('server_software', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('database_version', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('php_version', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('installed_on', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('updated_on', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('system_title', 'Phire CMS 2.0');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('system_email', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('site_title', 'Default Site Title');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('separator', '>');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('default_language', 'en_US');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('error_message', 'Sorry. That page was not found.');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('datetime_format', 'M j Y g:i A');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('media_allowed_types', 'a:27:{i:0;s:2:"ai";i:1;s:3:"bz2";i:2;s:3:"csv";i:3;s:3:"doc";i:4;s:4:"docx";i:5;s:3:"eps";i:6;s:3:"gif";i:7;s:2:"gz";i:8;s:4:"html";i:9;s:3:"htm";i:10;s:3:"jpe";i:11;s:3:"jpg";i:12;s:4:"jpeg";i:13;s:3:"pdf";i:14;s:3:"png";i:15;s:3:"ppt";i:16;s:4:"pptx";i:17;s:3:"psd";i:18;s:3:"svg";i:19;s:3:"swf";i:20;s:3:"tar";i:21;s:3:"txt";i:22;s:3:"xls";i:23;s:4:"xlsx";i:24;s:5:"xhtml";i:25;s:3:"xml";i:26;s:3:"zip";}');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('media_max_filesize', '25000000');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('media_actions', 'a:4:{s:5:"large";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:720;s:7:"quality";i:60;}s:6:"medium";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:480;s:7:"quality";i:60;}s:5:"small";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:240;s:7:"quality";i:60;}s:5:"thumb";a:3:{s:6:"action";s:9:"cropThumb";s:6:"params";i:60;s:7:"quality";i:60;}}');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('media_image_adapter', 'Gd');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('feed_type', '9');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('feed_limit', '20');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('open_authoring', '1');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('incontent_editing', '0');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('pagination_limit', '25');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('pagination_range', '10');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('force_ssl', '0');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('live', '1');

-- --------------------------------------------------------

--
-- Table structure for table "user_types"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_types" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type" varchar NOT NULL,
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
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]user_types', 2000);

--
-- Dumping data for table "user_types"
--

INSERT INTO "[{prefix}]user_types" ("id", "type", "default_role_id", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "email_verification", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "timeout_warning", "password_encryption", "ip_allowed", "ip_blocked", "log_emails", "log_exclude", "controller", "sub_controllers") VALUES
(2001, 'user', 3001, 1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 30, 0, 2, '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table "user_roles"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_roles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type_id" integer NOT NULL,
  "name" varchar NOT NULL,
  "permissions" text,
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
-- Table structure for table "users"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type_id" integer,
  "role_id" integer,
  "username" varchar NOT NULL,
  "password" varchar NOT NULL,
  "email" varchar NOT NULL,
  "verified" integer,
  "logins" text,
  "failed_attempts" integer,
  "site_ids" text,
  UNIQUE ("id"),
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]users', 1000);

--
-- Dumping data for table "users"
--

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

--
-- Dumping data for table "user_sessions"
--

-- --------------------------------------------------------

--
-- Table structure for table "content_types"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]content_types" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "uri" integer NOT NULL,
  "order" integer NOT NULL,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]content_types', 5000);

--
-- Dumping data for table "content_types"
--

INSERT INTO "[{prefix}]content_types" ("id", "name", "uri", "order") VALUES (5001, 'Page', 1, 1);
INSERT INTO "[{prefix}]content_types" ("id", "name", "uri", "order") VALUES (5002, 'Media', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table "content"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]content" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "site_id" integer,
  "type_id" integer,
  "parent_id" integer,
  "template" varchar,
  "title" varchar NOT NULL,
  "uri" varchar NOT NULL,
  "slug" varchar NOT NULL,
  "feed" integer,
  "force_ssl" integer,
  "status" integer,
  "roles" text,
  "created" datetime,
  "updated" datetime,
  "published" datetime,
  "expired" datetime,
  "created_by" integer,
  "updated_by" integer,
  UNIQUE ("id"),
  CONSTRAINT "fk_content_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]content" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_content_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]content_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_created_by" FOREIGN KEY ("created_by") REFERENCES "[{prefix}]users" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_updated_by" FOREIGN KEY ("updated_by") REFERENCES "[{prefix}]users" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]content', 6000);

--
-- Dumping data for table "content"
--

INSERT INTO "[{prefix}]content" ("id", "site_id", "type_id", "parent_id", "template", "title", "uri", "slug", "feed", "force_ssl", "status") VALUES (6001, 0, 5001, NULL, 'index.phtml', 'Home', '/', '', 1, 0, 2);
INSERT INTO "[{prefix}]content" ("id", "site_id", "type_id", "parent_id", "template", "title", "uri", "slug", "feed", "force_ssl", "status") VALUES (6002, 0, 5001, NULL, 'sub.phtml', 'About', '/about', 'about', 1, 0, 2);
INSERT INTO "[{prefix}]content" ("id", "site_id", "type_id", "parent_id", "template", "title", "uri", "slug", "feed", "force_ssl", "status") VALUES (6003, 0, 5001, 6002, 'sub.phtml', 'Sample Page', '/about/sample-page', 'sample-page', 1, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table "navigation"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]navigation" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "navigation" varchar NOT NULL,
  "spaces" integer,
  "top_node" varchar,
  "top_id" varchar,
  "top_class" varchar,
  "top_attributes" varchar,
  "parent_node" varchar,
  "parent_id" varchar,
  "parent_class" varchar,
  "parent_attributes" varchar,
  "child_node" varchar,
  "child_id" varchar,
  "child_class" varchar,
  "child_attributes" varchar,
  "on_class" varchar,
  "off_class" varchar,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]navigation', 7000);

--
-- Dumping data for table "navigation"
--

INSERT INTO "[{prefix}]navigation" ("id", "navigation", "spaces", "top_node", "top_id") VALUES (7001, 'Main Nav', 4, 'ul', 'main-nav');

-- --------------------------------------------------------

--
-- Table structure for table "categories"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]categories" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "title" varchar NOT NULL,
  "uri" varchar NOT NULL,
  "slug" varchar NOT NULL,
  "order" integer NOT NULL,
  "total" integer NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_category_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]categories" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]categories', 8000);

--
-- Dumping data for table "categories"
--

INSERT INTO "[{prefix}]categories" ("id", "parent_id", "title", "uri", "slug", "order", "total") VALUES (8001, NULL, 'My Favorites', '/my-favorites', 'my-favorites', 0, 1);

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

--
-- Dumping data for table "content_to_categories"
--

INSERT INTO "[{prefix}]content_to_categories" ("content_id", "category_id") VALUES (6002, 8001);
INSERT INTO "[{prefix}]content_to_categories" ("content_id", "category_id") VALUES (6003, 8001);

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

INSERT INTO "[{prefix}]navigation_tree" ("navigation_id", "content_id", "category_id", "order") VALUES (7001, 6001, NULL, 1);
INSERT INTO "[{prefix}]navigation_tree" ("navigation_id", "content_id", "category_id", "order") VALUES (7001, 6002, NULL, 2);
INSERT INTO "[{prefix}]navigation_tree" ("navigation_id", "content_id", "category_id", "order") VALUES (7001, 6003, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table "templates"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]templates" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "name" varchar NOT NULL,
  "content_type" varchar NOT NULL,
  "device" varchar NOT NULL,
  "template" text NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_template_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]templates" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]templates', 9000);

-- --------------------------------------------------------

--
-- Table structure for table "extensions"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]extensions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "file" varchar NOT NULL,
  "type" integer NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]extensions', 10000);

--
-- Dumping data for table "extensions"
--

INSERT INTO "[{prefix}]extensions" ("id", "name", "file", "type", "active", "assets") VALUES (10001, 'default', 'default.tar.gz', 0, 1, 'a:2:{s:9:"templates";a:9:{i:0;s:10:"date.phtml";i:1;s:11:"error.phtml";i:2;s:13:"sidebar.phtml";i:3;s:14:"category.phtml";i:4;s:11:"index.phtml";i:5;s:12:"header.phtml";i:6;s:12:"search.phtml";i:7;s:9:"sub.phtml";i:8;s:12:"footer.phtml";}s:4:"info";a:4:{s:10:"Theme Name";s:13:"Default Theme";s:6:"Author";s:11:"Nick Sagona";s:11:"Description";s:41:"This is a default theme for Phire CMS 2.0";s:7:"Version";s:3:"1.0";}}');

-- --------------------------------------------------------

--
-- Table structure for table "field_groups"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]field_groups" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar,
  "order" integer,
  "dynamic" integer,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]field_groups', 12000);

-- --------------------------------------------------------

--
-- Table structure for table "fields"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]fields" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "group_id" integer,
  "type" varchar,
  "name" varchar,
  "label" varchar,
  "values" varchar,
  "default_values" varchar,
  "attributes" varchar,
  "validators" varchar,
  "encryption" integer NOT NULL,
  "order" integer NOT NULL,
  "required" integer NOT NULL,
  "editor" varchar,
  "models" text,
  UNIQUE ("id"),
  CONSTRAINT "fk_group_id" FOREIGN KEY ("group_id") REFERENCES "[{prefix}]field_groups" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]fields', 11000);

--
-- Dumping data for table "fields"
--

INSERT INTO "[{prefix}]fields" ("group_id", "type", "name", "label", "values", "default_values", "attributes", "validators", "encryption", "order", "required", "editor", "models") VALUES (NULL, 'text', 'description', 'Description:', '', '', 'size="80"', NULL, 0, 1, 0, 'source', 'a:1:{i:0;a:2:{s:5:"model";s:19:"Phire\Model\Content";s:7:"type_id";i:5001;}}');
INSERT INTO "[{prefix}]fields" ("group_id", "type", "name", "label", "values", "default_values", "attributes", "validators", "encryption", "order", "required", "editor", "models") VALUES (NULL, 'text', 'keywords', 'Keywords:', '', '', 'size="80"', NULL, 0, 2, 0, 'source', 'a:1:{i:0;a:2:{s:5:"model";s:19:"Phire\Model\Content";s:7:"type_id";i:5001;}}');
INSERT INTO "[{prefix}]fields" ("group_id", "type", "name", "label", "values", "default_values", "attributes", "validators", "encryption", "order", "required", "editor", "models") VALUES (NULL, 'textarea-history', 'content', 'Content:', '', '', 'rows="20" cols="110" style="display: block;"', NULL, 0, 3, 0, 'source', 'a:1:{i:0;a:2:{s:5:"model";s:19:"Phire\Model\Content";s:7:"type_id";i:5001;}}');

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

--
-- Dumping data for table "field_values"
--

INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11001, 6001, 's:41:"This is the welcome page for Phire CMS 2.";', 1390841886, NULL);
INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11002, 6001, 's:36:"default site, phire cms 2, home page";', 1390841886, NULL);
INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11003, 6001, 's:955:"<p>This is the home page for Phire CMS 2.</p><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede.</p><p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat.</p>";', 1390841886, NULL);
INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11001, 6002, 's:39:"This is the about page for Phire CMS 2.";', 1390841914, NULL);
INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11002, 6002, 's:37:"default site, phire cms 2, about page";', 1390841914, NULL);
INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11003, 6002, 's:956:"<p>This is the about page for Phire CMS 2.</p><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede.</p><p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat.</p>";', 1390841914, NULL);
INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11001, 6003, 's:40:"This is the sample page for Phire CMS 2.";', 1390841937, NULL);
INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11002, 6003, 's:38:"default site, phire cms 2, sample page";', 1390841937, NULL);
INSERT INTO [{prefix}]field_values ("field_id", "model_id", "value", "timestamp", "history") VALUES (11003, 6003, 's:957:"<p>This is the sample page for Phire CMS 2.</p><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede.</p><p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat.</p>";', 1390841938, NULL);

-- --------------------------------------------------------

--
-- Table structure for table "sites"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]sites" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "domain" varchar(255) NOT NULL,
  "document_root" varchar(255) NOT NULL,
  "base_path" varchar(255) NOT NULL,
  "title" varchar(255) NOT NULL,
  "force_ssl" integer NOT NULL,
  "live" integer NOT NULL,
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('[{prefix}]sites', 13000);