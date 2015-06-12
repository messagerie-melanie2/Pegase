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
	user_id int AUTO_INCREMENT,
	username varchar(255) DEFAULT '' NOT NULL,
  	password varchar(255),
	email varchar(255),
	fullname text,
	created TIMESTAMP,
	modified TIMESTAMP,
	last_login TIMESTAMP,
	auth smallint DEFAULT 0 NOT NULL,
	language varchar(5),
	preferences text DEFAULT '' NOT NULL,
	PRIMARY KEY (user_id)
);



--
-- Table "polls"
-- Name: polls; Type: TABLE;
--

CREATE TABLE polls (
	poll_id int AUTO_INCREMENT,
	poll_uid varchar(128) DEFAULT '' NOT NULL UNIQUE,
	title varchar(255) DEFAULT '' NOT NULL, 
	location varchar(255),
	description text,
	organizer_id int NOT NULL
        	REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	created TIMESTAMP,
	modified TIMESTAMP,	
	locked smallint DEFAULT 0 NOT NULL,
	deleted smallint DEFAULT 0 NOT NULL,
	type varchar(10),
	proposals text DEFAULT '' NOT NULL,
	settings text DEFAULT '' NOT NULL,
	date_start TIMESTAMP,
	date_end TIMESTAMP,
	deadline TIMESTAMP,
	attendees text DEFAULT '' NOT NULL,
	PRIMARY KEY (poll_id)
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
	response_time TIMESTAMP
);

--
-- Table "eventslist"
-- Name: eventslist; Type: TABLE;
--

CREATE TABLE eventslist (
	user_id integer NOT NULL
        	REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	poll_id integer NOT NULL
        	REFERENCES polls (poll_id) ON DELETE CASCADE ON UPDATE CASCADE,
	events text DEFAULT '' NOT NULL,
	events_status varchar(255) DEFAULT '' NOT NULL,
	settings text DEFAULT '' NOT NULL,
	modified_time TIMESTAMP
);

-- Table: session

-- DROP TABLE session;

CREATE TABLE session
(
  session_id varchar(128) DEFAULT '' NOT NULL,
  created integer,
  changed integer,
  ip_address varchar(41) NOT NULL,
  vars text NOT NULL,
  PRIMARY KEY (session_id)
);

-- Index: users_username_auth_idx

-- DROP INDEX users_username_auth_idx;

CREATE INDEX users_username_auth_idx
  ON users (username, auth)
  USING btree;


-- Index: public.polls_poll_uid_idx

-- DROP INDEX polls_poll_uid_idx;

CREATE INDEX polls_poll_uid_idx
  ON polls (poll_uid)
  USING btree;


-- Index: responses_user_id_idx

-- DROP INDEX responses_user_id_idx;

CREATE INDEX responses_user_id_idx
  ON responses (user_id)
  USING btree;


-- Index: responses_poll_id_idx

-- DROP INDEX responses_poll_id_idx;

CREATE INDEX responses_poll_id_idx
  ON responses (poll_id)
  USING btree;

-- Index: eventslist_user_id_idx

-- DROP INDEX eventslist_user_id_idx;

CREATE INDEX eventslist_user_id_idx
  ON eventslist (user_id)
  USING btree;


-- Index: eventslist_poll_id_idx

-- DROP INDEX eventslist_poll_id_idx;

CREATE INDEX eventslist_poll_id_idx
  ON eventslist (poll_id)
  USING btree;

-- Index: session_changed_idx

-- DROP INDEX session_changed_idx;

CREATE INDEX session_changed_idx
  ON session (changed)
  USING btree;
