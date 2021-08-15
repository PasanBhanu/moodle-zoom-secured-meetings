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
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once(dirname(__FILE__).'/locallib.php');

// Log ID.
$id = required_param('id', PARAM_INT);
$log_id = required_param('log', PARAM_INT);
if ($id) {
    $cm         = get_coursemodule_from_id('zoom', $id, 0, false, MUST_EXIST);
    $course     = get_course($cm->course);
    $zoom       = $DB->get_record('zoom', array('id' => $cm->instance), '*', MUST_EXIST);
    $zoom_log   = $DB->get_record('zoom_secured_meetings', array('id' => $log_id), '*', MUST_EXIST);
} else {
    print_error('Unauthorized access');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_context($context);

require_capability('mod/zoom:view', $context);

$PAGE->set_pagelayout('standard');

$PAGE->set_url('/mod/zoom/loadregistrant.php', array('id' => $cm->id, 'log' => $log_id));
$PAGE->set_title(format_string($zoom->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string('Connecting to Zoom'), 2);


$link = html_writer::tag('img', '', array('src' => 'loading.gif'));
echo $OUTPUT->box_start('generalbox text-center');
echo $link;
echo $OUTPUT->box_end();
$link = html_writer::tag('div', 'Please wait! We are registering you with Zoom. Please do not close this window.', array('class' => ''));
echo $OUTPUT->box_start('generalbox text-center');
echo $link;
echo $OUTPUT->box_end();

if ($zoom_log->join_url) {
    redirect($zoom_log->join_url);
}

$PAGE->requires->js_call_amd("mod_zoom/reload");

echo $OUTPUT->footer();
// --- END MOD ZOOM --- //