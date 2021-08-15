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
 * Join url checker page logic
 *
 * @package    mod_zoom
 * @copyright  2021 Softink Lab
 * @author     Pasan Bhanu Guruge
 * @license    Licensed to Softink Lab
 */

define(['jquery'], function($) {
    timer = setInterval(checkMeeting, 3000);
    console.log('Zoom Meeting Checking...');

    function checkMeeting() {
        location.reload();
    }
});
