<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to the subscriptions block
 *
 * @since 3.8
 * @package block_subscriptions
 * @copyright 2019 Jake Dallimore <jrhdallimore@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->dirroot}/my/lib.php");
require_once("{$CFG->libdir}/db/upgradelib.php");

/**
 * Upgrade code for the MyOverview block.
 *
 * @param int $oldversion
 */
function xmldb_block_subscriptions_upgrade($oldversion) {
    global $DB, $CFG, $OUTPUT;

    $dbman = $DB->get_manager();

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2023063003) {

        // Define field id to be added to journals.
        $table = new xmldb_table('block_subscriptions');

        if(!$dbman->table_exists($table)){
            $dbman->create_table($table);
        }
        
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('target', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('source', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'target');

        // Conditionally launch add field subscription.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        upgrade_plugin_savepoint(true, 2023063003, 'blocks', 'subscriptions');
    }
    // Automatically generated Moodle v4.0.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    
    return true;
}
