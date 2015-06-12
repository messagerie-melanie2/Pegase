ALTER TABLE polls ADD date_start TIMESTAMP;
ALTER TABLE polls ADD date_end TIMESTAMP;
ALTER TABLE polls ADD deadline TIMESTAMP;
ALTER TABLE polls ADD attendees text DEFAULT '' NOT NULL;
