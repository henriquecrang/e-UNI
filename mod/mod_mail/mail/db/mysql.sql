#
# Table structure for table prefix_mail
#

CREATE TABLE prefix_mail (
  id int(10) unsigned NOT NULL auto_increment,
  course int(10) unsigned NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  summary text NOT NULL default '',
  maxbytes int(10) unsigned NOT NULL default '100000',
  timemodified int(10) unsigned NOT NULL default '0',
   PRIMARY KEY  (id),
   UNIQUE KEY course (course)
) TYPE=MyISAM COMMENT='Defines mail';

# --------------------------------------------------------

#
# Table structure for table prefix_mail_folder
#

CREATE TABLE prefix_mail_folder (
  id int(10) unsigned NOT NULL auto_increment,
  mailid int(10) unsigned NOT NULL default '0',
  userid int(10) unsigned NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  type varchar(1) NOT NULL default 'O',
  timemodified int(10) unsigned NOT NULL default '0',
   PRIMARY KEY  (id),
   KEY mailid (mailid),
   KEY userid (userid)
) TYPE=MyISAM COMMENT='Defines folder';

# --------------------------------------------------------

#
# Table structure for table prefix_mail_messages
#

CREATE TABLE prefix_mail_messages (
  id int(10) unsigned NOT NULL auto_increment,
  mailid int(10) unsigned NOT NULL default '0',
  userid int(10) unsigned NOT NULL default '0',
  fromid int(10) unsigned NOT NULL default '0',
  folderid int(10) unsigned NOT NULL default '0',
  subject varchar(255) NOT NULL default '',
  message text NOT NULL default '',
  archivo varchar(255) NOT NULL default '',
  leido INT(3) UNSIGNED NOT NULL default '0',
  responded INT(3) UNSIGNED NOT NULL default '0',
  borrado INT(3) UNSIGNED NOT NULL default '0',
  timemodified int(10) unsigned NOT NULL default '0',
   PRIMARY KEY  (id),
   KEY mailid (mailid),
   KEY userid (userid),
   KEY fromid (fromid),
   KEY folderid (folderid)
) TYPE=MyISAM COMMENT='Defines mail messages';

# --------------------------------------------------------

#
# Table structure for table prefix_mail_to_messages
#

CREATE TABLE prefix_mail_to_messages (
  id int(10) unsigned NOT NULL auto_increment,
  messageid int(10) unsigned NOT NULL default '0',
  toid int(10) unsigned NOT NULL default '0',
  timemodified int(10) unsigned NOT NULL default '0',
   PRIMARY KEY  (id),
   KEY messageid (messageid),
   KEY toid (toid)
) TYPE=MyISAM COMMENT='Defines mail to messsages';

# --------------------------------------------------------

#
# Table structure for table prefix_mail_groups
#

CREATE TABLE prefix_mail_groups (
  id int(10) unsigned NOT NULL auto_increment,
  mailid int(10) unsigned NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  timemodified int(10) unsigned NOT NULL default '0',
   PRIMARY KEY  (id),
   KEY mailid (mailid)
) TYPE=MyISAM COMMENT='Defines mail groups';

# --------------------------------------------------------

#
# Table structure for table prefix_mail_members_groups
#

CREATE TABLE prefix_mail_members_groups (
  id int(10) unsigned NOT NULL auto_increment,
  groupid int(10) unsigned NOT NULL default '0',
  userid int(10) unsigned NOT NULL default '0',
  timemodified int(10) unsigned NOT NULL default '0',
   PRIMARY KEY  (id),
   KEY groupid (groupid),
   KEY userid (userid)
) TYPE=MyISAM COMMENT='Defines members groups';

# --------------------------------------------------------

#
# Table structure for table prefix_mail_statistics
#

CREATE TABLE prefix_mail_statistics (
  id int(10) unsigned NOT NULL auto_increment,
  course int(10) unsigned NOT NULL default '0',
  userid int(10) unsigned NOT NULL default '0',
  received int(10) unsigned NOT NULL default '0',
  send int(10) unsigned NOT NULL default '0',
  timemodified int(10) unsigned NOT NULL default '0',
   PRIMARY KEY  (id),
   UNIQUE KEY courseuserid (course, userid)
) TYPE=MyISAM COMMENT='Defines statistics';

# --------------------------------------------------------


INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('mail', 'add', 'mail', 'name');
INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('mail', 'update', 'mail', 'name');
INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('mail', 'view', 'mail', 'name');

