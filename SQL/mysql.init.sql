-- Poll App initial database structure
-- DROP INDEX users_username_auth_idx;
-- DROP INDEX polls_poll_uid_idx;
-- DROP INDEX responses_user_id_idx;
-- DROP INDEX responses_poll_id_idx;
-- DROP TABLE users;
-- DROP TABLE polls;
-- DROP TABLE responses;


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

-- Index: public.users_username_auth_idx

-- DROP INDEX public.users_username_auth_idx;

CREATE INDEX users_username_auth_idx
  ON users (username, auth)
  USING btree;


-- Index: public.polls_poll_uid_idx

-- DROP INDEX public.polls_poll_uid_idx;

CREATE INDEX polls_poll_uid_idx
  ON polls (poll_uid)
  USING btree;


-- Index: public.responses_user_id_idx

-- DROP INDEX public.responses_user_id_idx;

CREATE INDEX responses_user_id_idx
  ON responses (user_id)
  USING btree;


-- Index: public.responses_poll_id_idx

-- DROP INDEX public.responses_poll_id_idx;

CREATE INDEX responses_poll_id_idx
  ON responses (poll_id)
  USING btree;
