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
    
//     protected static $c = 1;

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        if (empty($quizobj->get_quiz()->odrequired)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    public function prevent_access() {
        global $CFG, $PAGE, $DB, $USER;
        
        // User details.
        $sessionkey = sesskey();
        $userid     = $USER->id;
        $username   = $USER->username;
        
        $quiz       = $this->quizobj->get_quiz();
        $quizid     = $this->quizobj->get_quizid();
        $cmid       = $this->quizobj->get_cmid();
        
        echo '<br><br><br>-----------------<br>';
        
        if ($unfinishedattempt = quiz_get_user_attempt_unfinished($quiz->id, $USER->id)) {
            //                 print_object($unfinishedattempt);
            $unfinishedattemptid = $unfinishedattempt->id;
            $unfinished = $unfinishedattempt->state == quiz_attempt::IN_PROGRESS;
            echo '<br>b4 override - ' . date('d m Y g:i a',$unfinishedattempt->timecheckstate);
                if ($unfinished) {
                    echo '<br><br><br> Prevent access <br>'; 
                    $this->create_user_override($cmid, $quiz, $unfinishedattempt);
                }
//             }
        }
    }
    
    public function end_time($attempt) {
        echo '<br>tcs endtm : ' . $attempt->timecheckstate;
        //return $attempt->timecheckstate;
        return false;
    }

    public function time_left_display($attempt, $timenow) {
        
        $endtime = $this->end_time($attempt);
        if ($endtime === false) {
            return false;
        }
        return $endtime - $timenow;
        //echo '<br>tcs endtm : ' . $attempt->timecheckstate;
        //return $timenow;
    }

    protected function create_user_override($cmid, $quiz, $unfinishedattempt, $state = null) {
        global $DB, $CFG, $USER;

        $context    = context_module::instance($cmid);
        $groupmode  = null;
        $action     = null;
        $override   = null;
        $userid     = $USER->id;
       
        require_once($CFG->dirroot . '/mod/quiz/lib.php');
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $timelimit = $quiz->timelimit + 360;     
        
        echo '<br>before tcs - ' . $unfinishedattempt->timecheckstate;
        $quiza = $unfinishedattempt;
        
        
        //$timecheckstate = $quiza->timestart + $timelimit;
        
        /*
        if($quiz->timeclose == 0) {
//             echo 'in here 1';
            $timecheckstate = $quiza->timestart + $timelimit;
            
        } else if (($quiza->timestart + $timelimit) < $quiz->timeclose) {
//             echo 'in here 2';
            $timecheckstate = $quiza->timestart + $timelimit;
            
        } else if (($quiza->timestart + $timelimit) > $quiz->timeclose) {
            echo '<br>in here 3';
            $timecheckstate = $quiz->timeclose + ($quiz->timeclose - ($quiza->timestart + $timelimit));
            echo '<br>after tcs1 - ' . $timecheckstate;
            
        } else {
//             echo 'in here 4';
            $timecheckstate = $quiz->timeclose;
            
        }
            
        if($quiza->state == 'overdue') {
            $timecheckstate += $quiz->graceperiod;
        }
        */
         
        
        
        //$quiza->timecheckstate = $timecheckstate;
        $quiza->timecheckstate += 60;
        
        $DB->update_record('quiz_attempts', $quiza);
        echo '<br>after tcs2 - ' . date('d m Y g:i a',$quiza->timecheckstate);
                  
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
//                 $record->hbmonmode = $quiz->hbmonmode;
                $DB->insert_record('quizaccess_enable_od', $record);
            } else {
                $select = "quizid = $quiz->id";
                $id = $DB->get_field_select('quizaccess_enable_od', 'id', $select);
//                 $oldrecord = $DB->get_record('quizaccess_enable_od', $select);
                
                $record = new stdClass();
                $record->id = $id;
                $record->odrequired = $quiz->odrequired;
//                 $record->hbmonmode = $quiz->hbmonmode;
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
