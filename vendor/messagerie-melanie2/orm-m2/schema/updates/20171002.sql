ALTER TABLE kronolith_events 
	ADD COLUMN event_realuid varchar(255),
	ADD COLUMN event_created integer,
	ADD COLUMN event_modified_json integer,
	ADD COLUMN event_timezone TEXT,
	ADD COLUMN event_all_day smallint,
	ADD COLUMN event_is_deleted smallint,
	ADD COLUMN event_is_exception smallint,
	ADD COLUMN event_recurrence_id TEXT,
	ADD COLUMN event_organizer_json TEXT,
	ADD COLUMN organizer_calendar_id varchar(255),
	ADD COLUMN event_transparency varchar(10),
	ADD COLUMN event_sequence integer,
	ADD COLUMN event_priority smallint,
	ADD COLUMN event_alarm_json TEXT,
	ADD COLUMN event_recurrence_json TEXT,
	ADD COLUMN event_attachments_json TEXT,
	ADD COLUMN event_properties_json TEXT;