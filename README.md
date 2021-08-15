# Intro

This is a modded version of Zoom Moodle Extention to enable Secured Meetings in Zoom

# Prerequisites

This plugin is designed for Educational or Business Zoom accounts.

To connect to the Zoom APIs this plugin requires an account level JWT app to be
created. To create an account-level JWT app the Developer Role Permission is
required.

## Installation

1. Install plugin to mod/zoom. More details at https://docs.moodle.org/39/en/Installing_plugins#Installing_a_plugin
2. Do the modifications as below.
   - Add loading.gif to root folder
   - Add loadregistrant.php to root folder
   - Add zoomcallback.php to root folder
   - Add reload.min.js to amd/build folder
4. Add `option_multiple_devices` and `option_secured_meeting` to mdl_zoom table as boolean fields.
5. Add the secure meetings table to database.
6. Do modifications to below files.

#### lang/en/zoom.php
Add below code to bottom of the file
```
// --- MOD ZOOM --- //
$string['secured'] = 'Secured Meeting';
$string['option_secured_meeting'] = 'Only the users from the LMS will be allowed to join';
$string['option_secured_meeting_help'] = 'Enabling this option requires all attendees to sign in with their LMS account to be able to join the meeting. Sharing link and joining the meeting is not possible.';
$string['multiple_devices'] = 'Multiple Devices';
$string['option_multiple_devices'] = 'Allow registrants to join from multiple devices';
$string['option_multiple_devices_help'] = 'Enabling this option allow registrants to join from multiple devices using the same link.';
// --- END MOD ZOOM --- //
```
#### mod_form.php
Add below code at 308 line
```
// --- MOD ZOOM --- //
// Add secure meeting widget.
$mform->addElement('advcheckbox', 'option_secured_meeting', get_string('secured', 'zoom'),
       get_string('option_secured_meeting', 'zoom'));
$mform->addHelpButton('option_secured_meeting', 'option_secured_meeting', 'zoom');

// Allow registrants to join from multiple devices widget.
$mform->addElement('advcheckbox', 'option_multiple_devices', get_string('multiple_devices', 'zoom'),
       get_string('option_multiple_devices', 'zoom'));
$mform->addHelpButton('option_multiple_devices', 'option_multiple_devices', 'zoom');
// --- END MOD ZOOM --- //
```
#### classes/webservice.php
Add below code at line 528
```
// --- MOD ZOOM --- //
if (isset($zoom->option_multiple_devices)) {
   $data['settings']['allow_multiple_devices'] = (bool) $zoom->option_multiple_devices;
}
if (isset($zoom->option_secured_meeting)) {
   $data['settings']['approval_type'] = (int) $zoom->option_secured_meeting;
}
// --- END MOD ZOOM --- //
```
Add below code to the bottom of the file
```
// --- MOD ZOOM --- //
/**
* Create a meeting registrant on Zoom and approve.
* Take a $zoom object as returned from the Moodle form and respond with an object that can be saved to the database.
*
* @param stdClass $zoom The meeting to create.
* @return stdClass The call response.
*/
public function create_registrant($USER, $zoom) {
  // Provide license if needed.
  $this->provide_license($zoom->host_id);
  $url = (!empty($zoom->webinar) ? 'webinars' : 'meetings') . "/" . $zoom->meeting_id . "/registrants";
  $data = array(
      'email' => $USER->email,
      'first_name' => $USER->firstname,
      'last_name' => $USER->lastname,
  );
  $registrant = $this->_make_call($url, $data, 'post');

  $url = (!empty($zoom->webinar) ? 'webinars' : 'meetings') . "/" . $zoom->meeting_id . "/registrants/status";
  $approveRegistrant = array(
      'id' => $registrant->registrant_id,
      'email' => $USER->email
  );
  $data = array(
      'action' => 'approve',
      'registrants' => array($approveRegistrant)
  );
  $this->_make_call($url, $data, 'put');

  return $registrant;
}
// --- END MOD ZOOM --- //
```
#### view.php
Add below code at line 126
```
// --- MOD ZOOM --- //
$strsecuredmeeting = get_string('secured', 'mod_zoom');
$strallowmultipledevices = get_string('option_multiple_devices', 'mod_zoom');
// --- END MOD ZOOM --- //
```
Add below code at line 381
```
// --- MOD ZOOM --- //
$table->data[] = array($strallowmultipledevices, ($zoom->option_multiple_devices) ? $stryes : $strno);

$table->data[] = array($strsecuredmeeting, ($zoom->option_secured_meeting) ? $stryes : $strno);
// --- END MOD ZOOM --- //
```
#### loadmeeting.php
Add below code at line 88 replacing existing join url redirection
```
// --- MOD ZOOM --- //
if ($zoom->option_secured_meeting) {
  $zoom_log = $DB->get_record('zoom_secured_meetings', array ('userid' => $USER->id, 'meeting_id' => $zoom->meeting_id), $fields='*', $strictness=IGNORE_MISSING);
  if ($zoom_log) {
      if ($zoom_log->join_url) {
          $nexturl = new moodle_url($zoom_log->join_url, array('uname' => fullname($USER)));
      }else{
          $nexturl = new moodle_url('/mod/zoom/loadregistrant.php', array('id' => $id, 'log' => $zoom_log->id));
      }
  }else{
      $service = new mod_zoom_webservice();
      $registrant = $service->create_registrant($USER, $zoom);

      $data['userid'] = $USER->id;
      $data['meeting_id'] = $zoom->meeting_id;
      $data['participantid'] = $registrant->registrant_id;

      $logid = $DB->insert_record('zoom_secured_meetings', $data, $returnid=true, $bulk=false);

      $nexturl = new moodle_url('/mod/zoom/loadregistrant.php', array('id' => $id, 'log' => $logid));
  }
}else{
  $nexturl = new moodle_url($zoom->join_url, array('uname' => fullname($USER)));
}
// --- END MOD ZOOM --- //
```
