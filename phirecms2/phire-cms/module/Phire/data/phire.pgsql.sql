--
-- Phire CMS 2.0 PostgreSQL Database
--

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_types"
--

CREATE SEQUENCE type_id_seq START 2001;

CREATE TABLE IF NOT EXISTS "ph_user_types" (
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
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE type_id_seq OWNED BY "ph_user_types"."id";

--
-- Dumping data for table "ph_user_types"
--

INSERT INTO "ph_user_types" ("id", "type", "login", "registration", "multiple_sessions", "mobile_access", "email_as_username", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "password_encryption", "password_salt", "ip_allowed", "ip_blocked", "log_emails", "log_exclude") VALUES
(2001, 'User', 1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 30, 2, '', '', '', '', ''),
(2002, 'Member', 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 30, 2, '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_roles"
--

CREATE SEQUENCE role_id_seq START 3001;

CREATE TABLE IF NOT EXISTS "ph_user_roles" (
  "id" integer NOT NULL DEFAULT nextval('role_id_seq'),
  "type_id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_role_type" FOREIGN KEY ("type_id") REFERENCES "ph_user_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE role_id_seq OWNED BY "ph_user_roles"."id";

--
-- Dumping data for table "ph_user_roles"
--

INSERT INTO "ph_user_roles" ("id", "type_id", "name") VALUES
(3001, 2001, 'Admin'),
(3003, 2002, 'Full');

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_permissions"
--

CREATE SEQUENCE permission_id_seq START 4001;

CREATE TABLE IF NOT EXISTS "ph_user_permissions" (
  "id" integer NOT NULL DEFAULT nextval('permission_id_seq'),
  "role_id" integer NOT NULL,
  "resource" varchar(255),
  "permissions" varchar(255),
  UNIQUE ("role_id", "resource"),
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_permission_role" FOREIGN KEY ("role_id") REFERENCES "ph_user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE permission_id_seq OWNED BY "ph_user_permissions"."id";

-- --------------------------------------------------------

--
-- Table structure for table "ph_users"
--

CREATE SEQUENCE user_id_seq START 1001;

CREATE TABLE IF NOT EXISTS "ph_users" (
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
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "ph_user_types" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "ph_user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE user_id_seq OWNED BY "ph_users"."id";

-- --------------------------------------------------------

--
-- Table structure for table "ph_user_sessions"
--

CREATE SEQUENCE session_id_seq START 4001;

CREATE TABLE IF NOT EXISTS "ph_user_sessions" (
  "id" integer NOT NULL DEFAULT nextval('session_id_seq'),
  "user_id" integer,
  "ip" varchar(255) NOT NULL,
  "ua" varchar(255) NOT NULL,
  "start" timestamp NOT NULL,
  "last" timestamp NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_session_user" FOREIGN KEY ("user_id") REFERENCES "ph_users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE session_id_seq OWNED BY "ph_user_sessions"."id";
