ALTER TABLE polls ADD COLUMN date_start timestamp with time zone;
ALTER TABLE polls ADD COLUMN date_end timestamp with time zone;
ALTER TABLE polls ADD COLUMN deadline timestamp with time zone;
ALTER TABLE polls ADD COLUMN attendees text DEFAULT '' NOT NULL;
