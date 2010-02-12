#
# Table structure for table prefix_mail
#

CREATE TABLE prefix_mail (
  id SERIAL PRIMARY KEY,
  course integer  NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  summary text NOT NULL default '',
  maxbytes integer NOT NULL default '100000',
  timemodified integer NOT NULL default '0'
);

CREATE UNIQUE INDEX prefix_mail_course_idx ON prefix_mail (course);

# --------------------------------------------------------

#
# Table structure for table prefix_mail_folder
#

CREATE TABLE prefix_mail_folder (
  id SERIAL PRIMARY KEY,
  mailid integer NOT NULL default '0',
  userid integer  NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  type varchar(1) NOT NULL default 'O',
  timemodified integer NOT NULL default '0'
);

CREATE INDEX prefix_mail_folder_mailid_idx ON prefix_mail_folder (mailid);
CREATE INDEX prefix_mail_folder_userid_idx ON prefix_mail_folder (userid);

# --------------------------------------------------------

#
# Table structure for table prefix_mail_messages
#

CREATE TABLE prefix_mail_messages (
  id SERIAL PRIMARY KEY,
  mailid integer NOT NULL default '0',
  userid integer  NOT NULL default '0',
  fromid integer NOT NULL default '0',
  folderid integer NOT NULL default '0',
  subject varchar(255) NOT NULL default '',
  message text NOT NULL default '',
  archivo varchar(255) NOT NULL default '',
  leido integer NOT NULL default '0',
  responded integer NOT NULL default '0',
  borrado integer NOT NULL default '0',
  timemodified integer NOT NULL default '0'
);

CREATE INDEX prefix_mail_messages_mailid_idx ON prefix_mail_messages (mailid);
CREATE INDEX prefix_mail_messages_userid_idx ON prefix_mail_messages (userid);
CREATE INDEX prefix_mail_messages_fromid_idx ON prefix_mail_messages (fromid);
CREATE INDEX prefix_mail_messages_folderid_idx ON prefix_mail_messages (folderid);

# --------------------------------------------------------

#
# Table structure for table prefix_mail_to_messages
#

CREATE TABLE prefix_mail_to_messages (
  id SERIAL PRIMARY KEY,
  messageid integer NOT NULL default '0',
  toid integer  NOT NULL default '0',
  timemodified integer NOT NULL default '0'
);

CREATE INDEX prefix_mail_to_messages_messageid_idx ON prefix_mail_to_messages (messageid);
CREATE INDEX prefix_mail_to_messages_toid_idx ON prefix_mail_to_messages (toid);

# --------------------------------------------------------

#
# Table structure for table prefix_mail_groups
#

CREATE TABLE prefix_mail_groups (
  id SERIAL PRIMARY KEY,
  mailid integer NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  timemodified integer NOT NULL default '0'
);

CREATE INDEX prefix_mail_groups_mailid_idx ON prefix_mail_groups (mailid);

# --------------------------------------------------------

#
# Table structure for table prefix_mail_members_groups
#

CREATE TABLE prefix_mail_members_groups (
  id SERIAL PRIMARY KEY,
  groupid integer NOT NULL default '0',
  userid integer  NOT NULL default '0',
  timemodified integer NOT NULL default '0'
);

CREATE INDEX prefix_mail_members_groups_groupid_idx ON prefix_mail_members_groups (groupid);
CREATE INDEX prefix_mail_members_groups_userid_idx ON prefix_mail_members_groups (userid);

# --------------------------------------------------------

#
# Table structure for table prefix_mail_statistics
#

CREATE TABLE prefix_mail_statistics (
  id SERIAL PRIMARY KEY,
  course integer  NOT NULL default '0',
  userid integer  NOT NULL default '0',
  received integer NOT NULL default '0',
  send integer NOT NULL default '0',
  timemodified integer NOT NULL default '0'
);

CREATE UNIQUE INDEX prefix_mail_statistics_courseuserid_idx ON prefix_mail_statistics (course, userid);

# --------------------------------------------------------

INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('mail', 'add', 'mail', 'name');
INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('mail', 'update', 'mail', 'name');
INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('mail', 'view', 'mail', 'name');
