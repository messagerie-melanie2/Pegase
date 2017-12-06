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
