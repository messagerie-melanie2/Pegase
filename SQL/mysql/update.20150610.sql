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

-- Index: session_changed_idx

-- DROP INDEX session_changed_idx;

CREATE INDEX session_changed_idx
  ON session (changed)
  USING btree;


