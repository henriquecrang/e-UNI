<?php

// This file keeps track of upgrades to
// the email
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_email_list_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $result = true;

    // If is set upgrade_blocks_savepoint function
    $existfunction = false;
    if (!function_exists('upgrade_blocks_savepoint') ) {
        $existfunction = true;
    }

/// And upgrade begins here. For each one, you'll need one
/// block of code similar to the next one. Please, delete
/// this comment lines once this file start handling proper
/// upgrade code.

	if ($result && $oldversion < 2007062205) {
		$fields = array(
						'mod/email:viewmail',
						'mod/email:addmail',
						'mod/email:reply',
						'mod/email:replyall',
						'mod/email:forward',
						'mod/email:addsubfolder',
						'mod/email:updatesubfolder',
						'mod/email:removesubfolder'	);

		/// Remove no more used fields
        $table = new XMLDBTable('capabilities');

        foreach ($fields as $name) {

            $field = new XMLDBField($name);
            $result = $result && drop_field($table, $field);
        }

        // Active cron block of email_list
        if ( $result ) {
        	if ( $email_list = get_record('block', 'name', 'email_list') ) {
        		$email_list->cron = 1;
        		update_record('block',$email_list);
        	}
        }

        if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2007062205, 'email_list');
        }

	}

	// force
	$result = true;

	if ($result && $oldversion < 2007072003) {
		// Add marriedfolder2courses flag on email_preferences
		$table = new XMLDBTable('email_preference');

		$field = new XMLDBField('marriedfolders2courses');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', null);

        $result = $result && add_field($table, $field);


        // Add course ID on email_folder
        $table = new XMLDBTable('email_folder');

		$field = new XMLDBField('course');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', null);

        $result = $result && add_field($table, $field);

		// Add index
        $key = new XMLDBKey('course');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));

        $result = $result && add_key($table, $key);

        if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2007072003, 'email_list');
        }

	}

	if ($result && $oldversion < 2008061400 ) {

		// Add reply and forwarded info field on email_mail.
		$table = new XMLDBTable('email_send');

		$field = new XMLDBField('answered');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', null);

        $result = $result && add_field($table, $field);

        if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2008061400, 'email_list');
        }
	}

	// Solve old problems
	if ($result && $oldversion < 2008061600 ) {
		$table = new XMLDBTable('email_preference');
		$field = new XMLDBField('marriedfolders2courses');

		if ( !field_exists($table, $field) ) {
			$field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', null);

        	$result = $result && add_field($table, $field);
		}

		$table = new XMLDBTable('email_folder');

		$field = new XMLDBField('course');

		if ( !field_exists($table, $field) ) {
	        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', null);

	        $result = $result && add_field($table, $field);

			// Add index
	        $key = new XMLDBKey('course');
	        $key->setAttributes(XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));

	        $result = $result && add_key($table, $key);
		}

		if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2008061600, 'email_list');
		}

	}

	// Add new index
	if ( $result and $oldversion < 2008081602 ) {
		// Add combine key on foldermail
        $table = new XMLDBTable('email_foldermail');
        $index = new XMLDBIndex('folderid-mailid');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('folderid', 'mailid'));

        if (!index_exists($table, $index)) {
        /// Launch add index
            $result = $result && add_index($table, $index);
        }

        if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2008081602, 'email_list');
        }

	}

	// Upgrading to Moodle 2.0
	if ( $result and $oldversion < 2009040200 ) {

		// Portable SQL staments to Oracle, MySQL and PostgreSQL NOT APPLYCABLE to MSSQL
		if ( $CFG->dbname != 'mssql' ) {
			// Moodle 1.9 or prior
			if ($CFG->version < '2009011541') {
				// Filter
				$result = execute_sql("ALTER TABLE {$CFG->prefix}email_filter RENAME TO {$CFG->prefix}block_email_list_filter");

				// Folder
				$result = execute_sql("ALTER TABLE {$CFG->prefix}email_folder RENAME TO {$CFG->prefix}block_email_list_folder") && $result;

				// Foldermail
				$result = execute_sql("ALTER TABLE {$CFG->prefix}email_foldermail RENAME TO {$CFG->prefix}block_email_list_foldermail") && $result;

				// Mail
				$result = execute_sql("ALTER TABLE {$CFG->prefix}email_mail RENAME TO {$CFG->prefix}block_email_list_mail") && $result;

				// Preference
				$result = execute_sql("ALTER TABLE {$CFG->prefix}email_preference RENAME TO {$CFG->prefix}block_email_list_preference") && $result;

				// Send
				$result = execute_sql("ALTER TABLE {$CFG->prefix}email_send RENAME TO {$CFG->prefix}block_email_list_send") && $result;

				// Subfolder
				$result = execute_sql("ALTER TABLE {$CFG->prefix}email_subfolder RENAME TO {$CFG->prefix}block_email_list_subfolder") && $result;
			} else {
				// Filter
				$DB->execute("ALTER TABLE {$CFG->prefix}email_filter RENAME TO {$CFG->prefix}block_email_list_filter");

				// Folder
				$DB->execute("ALTER TABLE {$CFG->prefix}email_folder RENAME TO {$CFG->prefix}block_email_list_folder");

				// Foldermail
				$DB->execute("ALTER TABLE {$CFG->prefix}email_foldermail RENAME TO {$CFG->prefix}block_email_list_foldermail");

				// Mail
				$DB->execute("ALTER TABLE {$CFG->prefix}email_mail RENAME TO {$CFG->prefix}block_email_list_mail");

				// Preference
				$DB->execute("ALTER TABLE {$CFG->prefix}email_preference RENAME TO {$CFG->prefix}block_email_list_preference");

				// Send
				$DB->execute("ALTER TABLE {$CFG->prefix}email_send RENAME TO {$CFG->prefix}block_email_list_send");

				// Subfolder
				$DB->execute("ALTER TABLE {$CFG->prefix}email_subfolder RENAME TO {$CFG->prefix}block_email_list_subfolder");
			}
		}

		// Change module name to Standard eMail name.
		if ( $logs = get_records('log_display', 'module', 'email') ) {
			foreach ( $logs as $log ) {
				set_field('log_display', 'module', 'email_list', 'id', $log->id);
			}
		}

		// Only compatible with 1.9 or prior versions
		if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2009040200, 'email_list');
		}
	}

    return $result;
}

?>