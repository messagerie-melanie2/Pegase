--
-- Table "eventslist"
-- Name: eventslist; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE eventslist (
	user_id integer NOT NULL
        	REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	poll_id integer NOT NULL
        	REFERENCES polls (poll_id) ON DELETE CASCADE ON UPDATE CASCADE,
	events text DEFAULT ''::text NOT NULL,
	events_status varchar(255) DEFAULT '' NOT NULL,
	settings text DEFAULT ''::text NOT NULL,
	modified_time timestamp with time zone DEFAULT now() NOT NULL
);

-- Index: public.eventslist_user_id_idx

-- DROP INDEX public.eventslist_user_id_idx;

CREATE INDEX eventslist_user_id_idx
  ON public.eventslist
  USING btree
  (user_id);


-- Index: public.eventslist_poll_id_idx

-- DROP INDEX public.eventslist_poll_id_idx;

CREATE INDEX eventslist_poll_id_idx
  ON public.eventslist
  USING btree
  (poll_id);
