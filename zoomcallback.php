<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
 * Load zoom meeting and assign grade to the user join the meeting.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_zoom
 * @copyright  2021 Softink Lab
 * @license    Licensed
 */

// --- MOD ZOOM --- //
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$data = json_decode(file_get_contents('php://input'), true);

if ($data['event'] == 'meeting.registration_created') {
    $participantid = $data['payload']['object']['registrant']['id'];
    $zoom_log   = $DB->get_record('zoom_secured_meetings', array('participantid' => $participantid), '*', IGNORE_MISSING);

    if ($zoom_log) {
        $dtUpdate['id'] = $zoom_log->id;
        $dtUpdate['join_url'] = $data['payload']['object']['registrant']['join_url'];
        $DB->update_record('zoom_secured_meetings', $dtUpdate, $bulk=false);
    }
}
// --- END MOD ZOOM --- //