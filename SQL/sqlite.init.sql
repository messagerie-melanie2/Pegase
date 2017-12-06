-- Poll App initial database structure
-- DROP INDEX users_username_auth_idx;
-- DROP INDEX polls_poll_uid_idx;
-- DROP INDEX responses_user_id_idx;
-- DROP INDEX responses_poll_id_idx;
-- DROP INDEX eventslist_user_id_idx;
-- DROP INDEX eventslist_poll_id_idx;
-- DROP TABLE users;
-- DROP TABLE polls;
-- DROP TABLE responses;
-- DROP TABLE eventslist;


--
-- Table "users"
-- Name: users; Type: TABLE;
--

CREATE TABLE users (
  user_id integer PRIMARY KEY AUTOINCREMENT,
  username varchar(255) DEFAULT '' NOT NULL,
  password varchar(255),
  email varchar(255),
  fullname text,
  created timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  modified timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  last_login timestamp with time zone DEFAULT NULL,
  auth smallint DEFAULT 0 NOT NULL,
  "language" varchar(5),
  preferences text DEFAULT '' NOT NULL
);


--
-- Table "polls"
-- Name: polls; Type: TABLE;
--

CREATE TABLE polls (
  poll_id integer PRIMARY KEY AUTOINCREMENT,
  poll_uid varchar(128) DEFAULT '' NOT NULL UNIQUE,
  title varchar(255) DEFAULT '' NOT NULL,
  location varchar(255),
  description text,
  organizer_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  created timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  modified timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
  locked smallint DEFAULT 0 NOT NULL,
  deleted smallint DEFAULT 0 NOT NULL,
  type varchar(10),
  proposals text DEFAULT '' NOT NULL,
  settings text DEFAULT '' NOT NULL,
  date_start timestamp with time zone,
  date_end timestamp with time zone,
  deadline timestamp with time zone,
  attendees text DEFAULT '' NOT NULL
);

--
-- Table "responses"
-- Name: responses; Type: TABLE;
--

CREATE TABLE responses (
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  poll_id integer NOT NULL
    REFERENCES polls (poll_id) ON DELETE CASCADE ON UPDATE CASCADE,
  response text DEFAULT '' NOT NULL,
  settings text DEFAULT '' NOT NULL,
  response_time timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);

--
-- Table "responses"
-- Name: responses; Type: TABLE;
--

CREATE TABLE eventslist (
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  poll_id integer NOT NULL
    REFERENCES polls (poll_id) ON DELETE CASCADE ON UPDATE CASCADE,
  events text DEFAULT '' NOT NULL,
  events_status varchar(255) DEFAULT '' NOT NULL,
  settings text DEFAULT '' NOT NULL,
  modified_time timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);

-- Index: users_username_auth_idx

-- DROP INDEX users_username_auth_idx;

CREATE INDEX users_username_auth_idx
ON users
(username, auth);


-- Index: polls_poll_uid_idx

-- DROP INDEX polls_poll_uid_idx;

CREATE INDEX polls_poll_uid_idx
ON polls
(poll_uid);


-- Index: responses_user_id_idx

-- DROP INDEX responses_user_id_idx;

CREATE INDEX responses_user_id_idx
ON responses
(user_id);


-- Index: responses_poll_id_idx

-- DROP INDEX responses_poll_id_idx;

CREATE INDEX responses_poll_id_idx
ON responses
(poll_id);

-- Index: eventslist_user_id_idx

-- DROP INDEX eventslist_user_id_idx;

CREATE INDEX eventslist_user_id_idx
ON eventslist
(user_id);


-- Index: eventslist_poll_id_idx

-- DROP INDEX eventslist_poll_id_idx;

CREATE INDEX eventslist_poll_id_idx
ON eventslist
(poll_id);

