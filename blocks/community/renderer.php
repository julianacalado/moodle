<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Block community renderer.
 * @package    blocks
 * @subpackage community
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_community_renderer extends plugin_renderer_base {

    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->page->requires->css('/lib/gallery/assets/skins/sam/gallery-lightbox-skin.css');
    }

    /**
     * Display a list of courses
     * @param array $courses
     * @param boolean $withwriteaccess
     * @return string
     */
    public function course_list($courses, $huburl) {
        global $OUTPUT, $CFG;

        $renderedhtml = '';

        $table = new html_table();


        $table->head = array(get_string('coursename', 'block_community'),

            get_string('coursedesc', 'block_community'),
             get_string('screenshots', 'block_community'),
            get_string('courselang', 'block_community'),
            get_string('operation', 'block_community'));

        $table->align = array('center', 'left', 'center', 'left', 'center');
        $table->size = array('20%', '45%', '5%', '5%', '5%');



        if (empty($courses)) {
            if (isset($courses)) {
                $renderedhtml .= get_string('nocourse', 'block_community');
            }
        } else {

            $table->width = '100%';
            $table->data = array();
            $table->attributes['class'] = 'sitedirectory';

            // iterate through sites and add to the display table
            foreach ($courses as $course) {

                if (is_array($course)) {
                    $course = (object) $course;
                }

                //create site name with link
                if (!empty($course->courseurl)) {
                    $courseurl = new moodle_url($course->courseurl);
                } else {
                    $courseurl = new moodle_url($course->demourl);
                }
                $courseatag = html_writer::tag('a', $course->fullname, array('href' => $courseurl));

                $coursenamehtml = html_writer::tag('span', $courseatag, array());


                //create description to display
                $course->subject = get_string($course->subject, 'edufields');
                $course->audience = get_string('audience' . $course->audience, 'hub');
                $course->educationallevel = get_string('edulevel' . $course->educationallevel, 'hub');
                if (!empty($course->contributornames)) {
                    $course->contributorname = get_string('contributors', 'block_community', $course->contributorname);
                }
                if (!empty($course->coverage)) {
                    $course->coverage = get_string('coverage', 'block_community', $course->coverage);
                }
                $deschtml = $course->description; //the description
                /// courses and sites number display under the description, in smaller
                $deschtml .= html_writer::empty_tag('br');
                $additionaldesc = get_string('additionalcoursedesc', 'block_community', $course);
                $deschtml .= html_writer::tag('span', $additionaldesc, array('class' => 'additionaldesc'));
                //add content to the course description
                if (!empty($course->contents)) {
                    $activitieshtml = '';
                    $blockhtml = '';
                    foreach ($course->contents as $content) {
                        if ($content['moduletype'] == 'block') {
                            $blockhtml .= ' - ' . $content['modulename'] . " (" . $content['contentcount'] . ")";
                        } else {
                            $activitieshtml .= ' - ' . $content['modulename'] . " (" . $content['contentcount'] . ")";
                        }
                    }
                    $deschtml .= html_writer::empty_tag('br') . html_writer::tag('span',
                                    get_string('blocks', 'block_community') . " : " . $blockhtml, array('class' => 'blockdescription'));
                    $deschtml .= html_writer::empty_tag('br') . html_writer::tag('span',
                                    get_string('activities', 'block_community') . " : " . $activitieshtml, array('class' => 'activitiesdescription'));
                }

                //retrieve language string
                //construct languages array
                if (!empty($course->language)) {
                    $languages = get_string_manager()->get_list_of_languages();
                    $language = $languages[$course->language];
                } else {
                    $language = '';
                }

                if ($course->enrollable) {
                    $params = array('sesskey' => sesskey(), 'add' => 1, 'confirmed' => 1,
                        'coursefullname' => $course->fullname, 'courseurl' => $courseurl,
                        'coursedescription' => $course->description);
                    $addurl = new moodle_url("/blocks/community/communitycourse.php", $params);
                    $addbutton = new single_button($addurl, get_string('addtocommunityblock', 'block_community'));
                    $addbutton->class = 'centeredbutton';
                    $addbuttonhtml = $OUTPUT->render($addbutton);
                } else {
                    $params = array('sesskey' => sesskey(), 'download' => 1, 'confirmed' => 1,
                        'courseid' => $course->id, 'huburl' => $huburl, 'coursefullname' => $course->fullname);
                    $addurl = new moodle_url("/blocks/community/communitycourse.php", $params);
                    $downloadbutton = new single_button($addurl, get_string('download', 'block_community'));
                    $downloadbutton->class = 'centeredbutton';
                    $addbuttonhtml = $OUTPUT->render($downloadbutton);
                }

                // add a row to the table
                $screenshothtml = '';
                if (!empty($course->screenshotsids)) {

                    //include gallery lightbox js
                    $this->page->requires->js('/lib/gallery/gallery-lightbox-min.js');

                    for ($i = 1; $i <= $course->screenshotsids; $i = $i + 1) {
                        if ($i == 1) {
                            $params = array('courseid' => $course->id,
                                'filetype' => SCREENSHOT_FILE_TYPE, 'screenshotnumber' => $i);
                            $imgurl = new moodle_url($huburl . "/local/hub/webservice/download.php", $params);
                        } else {
                            //empty image
                            $imgurl = new moodle_url($CFG->wwwroot . "/pix/spacer.gif");
                        }
                        $ascreenshothtml = html_writer::empty_tag('img', array('src' => $imgurl, 'alt' => $course->fullname));
                        $originalparams = array('courseid' => $course->id,
                            'filetype' => SCREENSHOT_FILE_TYPE, 'screenshotnumber' => $i, 'imagewidth' => 'original');
                        $originalimgurl = new moodle_url($huburl . "/local/hub/webservice/download.php", $originalparams);
                        $screenshothtml .= html_writer::tag('a', $ascreenshothtml,
                                        array('rel' => 'lightbox[' . $course->shortname . ']', 'title' => $course->fullname,
                                            'href' => $originalimgurl));
                    }

                    // run the JS
                    $js = "Y.use(\"gallery-lightbox\", function (Y) { Y.Lightbox.init(); });";
                    $this->page->requires->js_init_code($js, true);
                }

                $cells = array($coursenamehtml, $deschtml, $screenshothtml, $language, $addbuttonhtml);


                $row = new html_table_row($cells);

                $table->data[] = $row;
            }
            $renderedhtml .= html_writer::table($table);
        }
        return $renderedhtml;
    }

}