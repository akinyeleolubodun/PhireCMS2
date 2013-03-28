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
-- Table structure for table "ph_user_types"
--

CREATE TABLE IF NOT EXISTS "ph_user_types" (
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
  UNIQUE ("id")
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_user_types', 2000);

--
-- Dumping data for table "ph_user_types"
--

INSERT INTO "ph_user_types" ("id", "type", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude") VALUES
(2001, 'User', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 30, 2, '', '', '', '', '');
INSERT INTO "ph_user_types" ("id", "type", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude") VALUES
(2002, 'Member', 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 30, 2, '', '', '', '', '');

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
INSERT INTO "ph_user_roles" ("id", "type_id", "name") VALUES
(3003, 2002, 'Full');

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_permissions"
--

CREATE TABLE IF NOT EXISTS "ph_user_permissions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer NOT NULL,
  "resource" varchar,
  "permissions" varchar,
  UNIQUE ("role_id", "resource"),
  UNIQUE ("id"),
  CONSTRAINT "fk_permission_role" FOREIGN KEY ("role_id") REFERENCES "ph_user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_user_permissions', 4000);

-- --------------------------------------------------------

--
-- Table structure for table "ph_users"
--

CREATE TABLE IF NOT EXISTS "ph_users" (
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
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "ph_user_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "ph_user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_users', 1000);

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
