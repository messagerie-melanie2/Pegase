-- Poll App initial database structure
-- DROP INDEX users_username_auth_idx;
-- DROP INDEX polls_poll_uid_idx;
-- DROP INDEX responses_user_id_idx;
-- DROP INDEX responses_poll_id_idx;
-- DROP TABLE users;
-- DROP TABLE polls;
-- DROP TABLE responses;
-- DROP SEQUENCE users_seq;
-- DROP SEQUENCE polls_seq;



--
-- Sequence "users_seq"
-- Name: users_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--
--

CREATE SEQUENCE users_seq
	INCREMENT BY 1
	NO MAXVALUE
	NO MINVALUE
	CACHE 1;


--
-- Table "users"
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE users (
	user_id integer DEFAULT nextval('users_seq'::text) PRIMARY KEY,
	username varchar(255) DEFAULT '' NOT NULL,
	email varchar(255),
	fullname text,
	created timestamp with time zone DEFAULT now() NOT NULL,
	modified timestamp with time zone DEFAULT now() NOT NULL,
	last_login timestamp with time zone DEFAULT NULL,
	auth smallint DEFAULT 0 NOT NULL,
	"language" varchar(5),
	preferences text DEFAULT ''::text NOT NULL
);

--
-- Sequence "polls_seq"
-- Name: polls_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--
--

CREATE SEQUENCE polls_seq
	INCREMENT BY 1
	NO MAXVALUE
	NO MINVALUE
	CACHE 1;


--
-- Table "polls"
-- Name: polls; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE polls (
	poll_id integer DEFAULT nextval('polls_seq'::text) PRIMARY KEY,
	poll_uid varchar(128) DEFAULT '' NOT NULL UNIQUE,
	title varchar(255) DEFAULT '' NOT NULL, 
	location varchar(255),
	description text,
	organizer_id integer NOT NULL
        	REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp with time zone DEFAULT now() NOT NULL,
	modified timestamp with time zone DEFAULT now() NOT NULL,
	locked smallint DEFAULT 0 NOT NULL,
	deleted smallint DEFAULT 0 NOT NULL,
	type varchar(10),
	proposals text DEFAULT ''::text NOT NULL,
	settings text DEFAULT ''::text NOT NULL
);

--
-- Table "responses"
-- Name: responses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE responses (
	user_id integer NOT NULL
        	REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	poll_id integer NOT NULL
        	REFERENCES polls (poll_id) ON DELETE CASCADE ON UPDATE CASCADE,
	response text DEFAULT ''::text NOT NULL,
	settings text DEFAULT ''::text NOT NULL,
	response_time timestamp with time zone DEFAULT now() NOT NULL
);

-- Index: public.users_username_auth_idx

-- DROP INDEX public.users_username_auth_idx;

CREATE INDEX users_username_auth_idx
  ON public.users
  USING btree
  (username, auth);


-- Index: public.polls_poll_uid_idx

-- DROP INDEX public.polls_poll_uid_idx;

CREATE INDEX polls_poll_uid_idx
  ON public.polls
  USING btree
  (poll_uid);


-- Index: public.responses_user_id_idx

-- DROP INDEX public.responses_user_id_idx;

CREATE INDEX responses_user_id_idx
  ON public.responses
  USING btree
  (user_id);


-- Index: public.responses_poll_id_idx

-- DROP INDEX public.responses_poll_id_idx;

CREATE INDEX responses_poll_id_idx
  ON public.responses
  USING btree
  (poll_id);





