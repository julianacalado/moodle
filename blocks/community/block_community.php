<?PHP
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

/*
 * @package    blocks
 * @subpackage community
 * @author     Jerome Mouneyrac <jerome@mouneyrac.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * The community block
*/

require_once($CFG->dirroot . '/blocks/community/locallib.php');

class block_community extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_community');
        $this->version = 2010042701;
    }

    function get_content() {
        global $CFG, $OUTPUT, $USER;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!isloggedin()) {
            return $this->content;
        }

        $searchlink = html_writer::tag('a', get_string('addcourse', 'block_community'),
                array('href' => $CFG->wwwroot.'/blocks/community/communitycourse.php?add=true'));
        $this->content->items[] = $searchlink;
        $icon = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/group'),
            'class' => 'icon', 'alt' => get_string('addcourse', 'block_community')));
        $this->content->icons[] = $icon;

        $community = new community();
        $courses = $community->get_community_courses($USER->id);
        if ($courses) {
            $this->content->items[] = html_writer::empty_tag('hr');
            $this->content->icons[] = '';
            $this->content->items[] = get_string('mycommunities', 'block_community');
            $this->content->icons[] = '';
            foreach ($courses as $course) {
                //delete link
                $deleteicon = html_writer::empty_tag('img', 
                        array('src' => $OUTPUT->pix_url('i/cross_red_small'), 
                            'alt' => get_string('removecommunitycourse', 'block_community')));
                $deleteurl = new moodle_url($CFG->wwwroot.'/blocks/community/communitycourse.php',
                        array('remove'=>true, 'communityid'=> $course->id, 'sesskey' => sesskey()));
                $deleteatag = html_writer::tag('a', $deleteicon, array('href' => $deleteurl));

                $courselink = html_writer::tag('a', $course->coursename,
                array('href' => $course->courseurl));
                $this->content->items[] = $courselink .$deleteatag;
                $this->content->icons[] = '';
            }
        }

        return $this->content;
    }

    function applicable_formats() {
        return array('all' => true);
    }

}
