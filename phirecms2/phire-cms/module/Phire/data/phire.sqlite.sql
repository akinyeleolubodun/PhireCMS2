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
-- Table structure for table "ph_types"
--

CREATE TABLE IF NOT EXISTS "ph_types" (
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

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_types', 2000);

--
-- Dumping data for table "ph_types"
--

INSERT INTO "ph_types" ("id", "type", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude") VALUES
(2001, 'User', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 2, '', '', '', '', '');
INSERT INTO "ph_types" ("id", "type", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude") VALUES
(2002, 'Member', 1, 1, 1, 1, 1, 0, 1, 1, 1, 0, 0, 0, 0, 2, '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table "ph_roles"
--

CREATE TABLE IF NOT EXISTS "ph_roles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "type_id" integer NOT NULL,
  "name" varchar NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_role_type" FOREIGN KEY ("type_id") REFERENCES "ph_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_roles', 3000);

--
-- Dumping data for table "ph_roles"
--

INSERT INTO "ph_roles" ("id", "type_id", "name") VALUES
(3001, 2001, 'Admin');
INSERT INTO "ph_roles" ("id", "type_id", "name") VALUES
(3002, 2001, 'Restricted');
INSERT INTO "ph_roles" ("id", "type_id", "name") VALUES
(3003, 2002, 'Full');
INSERT INTO "ph_roles" ("id", "type_id", "name") VALUES
(3004, 2002, 'Basic');

-- --------------------------------------------------------

--
-- Table structure for table "ph_permissions"
--

CREATE TABLE IF NOT EXISTS "ph_permissions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer NOT NULL,
  "resource" varchar,
  "permissions" varchar,
  UNIQUE ("role_id", "resource"),
  UNIQUE ("id"),
  CONSTRAINT "fk_permission_role" FOREIGN KEY ("role_id") REFERENCES "ph_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_permissions', 4000);

--
-- Dumping data for table "ph_permissions"
--

INSERT INTO "ph_permissions" ("id", "role_id", "resource", "permissions") VALUES
(4001, 3002, 'users', 'read,add,edit');
INSERT INTO "ph_permissions" ("id", "role_id", "resource", "permissions") VALUES
(4002, 3004, 'profile', 'read');

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
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "ph_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "ph_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_users', 1000);

--
-- Dumping data for table "ph_users"
--

INSERT INTO "ph_users" ("id", "type_id", "role_id", "first_name", "last_name", "email", "username", "password", "updates", "verified") VALUES
(1001, 2001, 3001, 'System', 'Admin', 'test@admin.com', 'admin', 'babfd5547a2ee2692ee03d3f0d973dc8ce7297d4', 1, 1);
INSERT INTO "ph_users" ("id", "type_id", "role_id", "first_name", "last_name", "email", "username", "password", "updates", "verified") VALUES
(1002, 2001, 3002, 'Test', 'User', 'test@user.com', 'testuser', 'c214105243281cf6147b81fde537bc2769200211', 1, 1);
INSERT INTO "ph_users" ("id", "type_id", "role_id", "first_name", "last_name", "email", "username", "password", "updates", "verified") VALUES
(1003, 2002, 3003, 'Test', 'Member', 'test@member.com', 'test@member.com', '7c4a8d09ca3762af61e59520943dc26494f8941b', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table "ph_sessions"
--

CREATE TABLE IF NOT EXISTS "ph_sessions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer,
  "ip" varchar NOT NULL,
  "ua" varchar NOT NULL,
  "start" datetime NOT NULL,
  "last" datetime NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_session_user" FOREIGN KEY ("user_id") REFERENCES "ph_users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO sqlite_sequence ("name", "seq") VALUES ('ph_sessions', 4000);

--
-- Dumping data for table "ph_sessions"
--
