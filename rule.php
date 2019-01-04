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
 * Implementaton of the quizaccess_heartbeatmonitor plugin.
 *
 * @package    quizaccess
 * @subpackage heartbeatmonitor
 * @author     Prof. P Sunthar, Amrata Ramchandani <ramchandani.amrata@gmail.com>, Kashmira Nagwekar
 * @copyright  2017 IIT Bombay, India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
require_once($CFG->dirroot . '/config.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');


/**
 * A rule implementing heartbeat monitor.
 *
 * @copyright  2017 IIT Bombay, India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_overridedemo extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        if (empty($quizobj->get_quiz()->odrequired)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    public function description() {
        return get_string('quiztimelimit', 'quizaccess_timelimit',
                format_time($this->quiz->timelimit));
    }


//     public function is_preflight_check_required($attemptid) {
//         Warning only required if the attempt is not already started.
//                 return $attemptid === null;
//         return true;
//     }
//     public function add_preflight_check_form_fields(mod_quiz_preflight_check_form $quizform,
//             MoodleQuickForm $mform, $attemptid) {


    public function prevent_access() {
//     public function end_time($attempt) {
        global $CFG, $PAGE, $DB, $USER;
//         echo '<br><br><br> Prevent access <br>';
        echo '<br><br><br> in prev acc <br>';
        // User details.
        $sessionkey = sesskey();
        $userid     = $USER->id;
        $username   = $USER->username;

        $quiz       = $this->quizobj->get_quiz();
        $quizid     = $this->quizobj->get_quizid();
        $cmid       = $this->quizobj->get_cmid();

        echo '<br> time() ' . time();

    //         if(isset($attempt->hite)) {                  // for end_time() .. no use ..
//             echo '<br> hite ' . time();

            if ($unfinishedattempt = quiz_get_user_attempt_unfinished($quiz->id, $USER->id)) {
                echo '<br> in unfin prev ac ';
//                 if(isset($unfinishedattempt->hite)) {    // for end_time() .. no use .. end_time is called twice
                if (!empty($_SESSION['contod'])) {                      // for prev_acc() .. coz cr_u_ovrd() needs to be executed only once
                    echo '<br> sess pa ' . $_SESSION['contod'];         // for prev_acc() ..
                    echo '<br> create ovrd ';
                print_object($unfinishedattempt);
                $unfinishedattemptid = $unfinishedattempt->id;
                $unfinished = $unfinishedattempt->state == quiz_attempt::IN_PROGRESS;

                if ($unfinished) {
                    echo '<br><br><br> Prevent access <br>';
                    $this->create_user_override($cmid, $quiz, $unfinishedattempt);
                    $_SESSION['contod'] = false;
                }
            }
            //======================
            /*  // for end_time() ..
            $timelimit1 = time() + $quiz->timelimit - $unfinishedattempt->timemodified;
            $timeclose1 = $unfinishedattempt->timestart + $timelimit1;
//             return (time() + 60);
            return $timeclose1;
//             return false;
        }
//         return false;
        return $unfinishedattempt->timestart + $this->quiz->timelimit;
        //======================*/
        }
//         return false;                                                // for is_prefl() ..
    }

    protected function create_user_override($cmid, $quiz, $unfinishedattempt, $state = null) {
        global $DB, $CFG, $USER;

        $context    = context_module::instance($cmid);
        $userid     = $USER->id;

        require_once($CFG->dirroot . '/mod/quiz/lib.php');
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        // Setup the form data required for processing as in overrideedit.php file.
        $override = new stdClass();
        $override->cmid = $cmid;

        $override->timeopen = null;

//         $timelimit = $quiz->timelimit + 180;
//         $timelimit2 = (time() + $quiz->timelimit) - ($unfinishedattempt->timemodified);
//         $override->timelimit = $timelimit2;
//         echo '<br> new timelimit ' . $timelimit2;
        $timelimit2 = $quiz->timelimit;
        $override->timelimit = $quiz->timelimit;
        echo '<br> new timelimit ' . $quiz->timelimit;


        if (($unfinishedattempt->timestart + $timelimit2) > $quiz->timeclose) {
//             $timeclose = $quiz->timeclose + 60;
            $timeclose = $unfinishedattempt->timestart + $timelimit2;
            $override->timeclose = $timeclose;
        } else {
            $override->timeclose = null;
        }

        $override->attempts = null;
        $override->password = null;

        // Process the data.
        $override->quiz = $quiz->id;
        $override->userid = $userid;

        // See if we are replacing an existing override.
        $conditions = array(
            'quiz' => $quiz->id,
            'userid' => empty($override->userid)? null : $override->userid);
        if ($oldoverride = $DB->get_record('quiz_overrides', $conditions)) {
            // There is an old override, so we merge any new settings on top of
            // the older override.
            $keys = array('timeopen', 'timeclose', 'timelimit', 'attempts', 'password');
            foreach ($keys as $key) {
                if (is_null($override->{$key})) {
                    $override->{$key} = $oldoverride->{$key};
                }
            }
            // Set the course module id before calling quiz_delete_override().
            $quiz->cmid = $cmid;

            quiz_delete_override($quiz, $oldoverride->id);
        }

        //unset($override->id);
        $override->id = $DB->insert_record('quiz_overrides', $override);

        // Parameters for events we may be triggering.
        $params = array(
            'context' => $context,
            'other' => array(
                'quizid' => $quiz->id
            ),
            'objectid' => $override->id,
            'relateduserid' => $override->userid
        );

        $event = \mod_quiz\event\user_override_created::create($params);

        // Trigger the override created event.
        $event->trigger();

        // Update timecheckstate (as in quiz_update_open_attempts()).
        $timecheckstate = $unfinishedattempt->timestart + $timelimit2;
        echo '<br> tcs prev acc ' . $timecheckstate;
        $DB->set_field('quiz_attempts', 'timecheckstate', $timecheckstate, array('id' => $unfinishedattempt->id));

        // User override. We only need to update the calendar event for this user override.
        quiz_update_events($quiz, $override);
//         $timeclose = $unfinishedattempt->timestart + $timelimit;
//         return $timeclose;
    }

    public function end_time($attempt) {
        echo '<br>ts ' . $attempt->timestart;
        echo '<br>tl ' . $this->quiz->timelimit;
        echo '<br>tm ' . $attempt->timemodified;
        echo '<br><br><br> end time <br>';

        /*
        if(isset($attempt->timecheckstate)){
            echo '<br>tcs ' . $attempt->timecheckstate;

            echo '<br>===========atmpt============== ';
            print_object($attempt);
//             echo '<br>===========this=============== ';
//             print_object($this);

            echo '<br>diff ' . ($attempt->timecheckstate - $attempt->timemodified);
            echo '<br>time() ' . time();
//             $attempt->timestart + $this->quiz->timelimit
//             time() + ($attempt->timecheckstate - $attempt->timemodified)
            return $extratime = time() + ($attempt->timecheckstate - $attempt->timemodified);
        }
        echo '<br>add ' . ($attempt->timestart + $this->quiz->timelimit);
        return $attempt->timestart + $this->quiz->timelimit;

//         $time = 1546439400;
//         return $time;
*/
        //============================================================
//         $attempt->odflag = false;
        echo '<br><br><br>----------before hite end time';
        if (isset($attempt->hite)) {
            echo '<br><br><br>----------in hite end time';
            $timelimit1 = (time() + $this->quiz->timelimit) - $attempt->timemodified;
            //=================check if this works=======
            //do it separately : set $this->qtl n createovd() also
            //either here or immdtly after createovd() .. for displaying new tl during contatmpt is rendered ..
            $this->quiz->timelimit = $timelimit1;
            //===========================================
            $timeclose1 = $attempt->timestart + $timelimit1;
//             $attempt->odflag = true;
            return $timeclose1;
        }
        return $attempt->timestart + $this->quiz->timelimit;
    }
/*
    public function time_left_display($attempt, $timenow) {
        // If this is a teacher preview after the time limit expires, don't show the time_left
        echo '<br>tcs2 ' . $attempt->timecheckstate;
        $endtime = $this->end_time($attempt);
        if ($attempt->preview && $timenow > $endtime) {
            return false;
        }
        return $endtime - $timenow;
    }
*/
    public function get_superceded_rules() {
        return array('timelimit');
    }

    public static function add_settings_form_fields(
        mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
            $odsettingsarray   = array();

            $odsettingsarray[] = $mform->createElement('select', 'odrequired',
                get_string('odrequired', 'quizaccess_overridedemo'), array(
                    0 => get_string('notrequired', 'quizaccess_overridedemo'),
                    1 => get_string('odrequiredoption', 'quizaccess_overridedemo')
                ));

            $mform->addGroup($odsettingsarray, 'enableod', get_string('odrequired', 'quizaccess_overridedemo'), array(' '), false);
            $mform->addHelpButton('enableod', 'odrequired', 'quizaccess_overridedemo');
            $mform->setAdvanced('enableod', true);
    }

    public static function validate_settings_form_fields(array $errors,
        array $data, $files, mod_quiz_mod_form $quizform) {
            return $errors;
    }

    public static function get_browser_security_choices() {
        return array();
    }

    public static function save_settings($quiz) {
        global $DB;
        if (empty($quiz->odrequired)) {
            $DB->delete_records('quizaccess_enable_od', array('quizid' => $quiz->id));
        } else {
            if (!$DB->record_exists('quizaccess_enable_od', array('quizid' => $quiz->id))) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->odrequired = 1;
                $DB->insert_record('quizaccess_enable_od', $record);
            } else {
                $select = "quizid = $quiz->id";
                $id = $DB->get_field_select('quizaccess_enable_od', 'id', $select);

                $record = new stdClass();
                $record->id = $id;
                $record->odrequired = $quiz->odrequired;
                $DB->update_record('quizaccess_enable_od', $record);
            }
        }
    }

    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_enable_od', array('quizid' => $quiz->id));
    }

    public static function get_settings_sql($quizid) {
        return array(
            'odrequired',
            'LEFT JOIN {quizaccess_enable_od} enable_od ON enable_od.quizid = quiz.id',
            array()
        );
    }

    public static function get_extra_settings($quizid) {
        return array();
    }

}
