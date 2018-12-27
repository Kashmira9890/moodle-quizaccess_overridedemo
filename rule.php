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
//         print_object($this);
//         $var = $userid . '_od';
// //         $_SESSION[$var] = 0;
// //         echo 'var-- ' . $var . ' -- sess --   ' . $_SESSION[$var] . ' --- isset ----  ' . isset($_SESSION[$var]);
//         if(!isset($_SESSION[$var])){
// //         if($_SESSION[$var] == 0){
//             $_SESSION[$var] = 1;

            if ($unfinishedattempt = quiz_get_user_attempt_unfinished($quiz->id, $USER->id)) {
//                 print_object($unfinishedattempt);
                $unfinishedattemptid = $unfinishedattempt->id;
                $unfinished = $unfinishedattempt->state == quiz_attempt::IN_PROGRESS;
                
                
//                 $script = " <script>
//     			            function findButtonText() {
//     					        $(document).ready(function() {
    			
//                     				if(document.getElementByClass('singlebutton quizstartbuttondiv')) {
                     				
//                     				}
//         				        });
//     					    }
//     				        </script>";
//                 $script .= "<script type='text/javascript'>countDownTimer($diffMilliSecs);</script>";   
                
                if ($unfinished) {
                    echo '<br><br><br> Prevent access <br>'; 
                    $this->create_user_override($cmid, $quiz, $unfinishedattempt);
                }
            }
//         }
        
    }
    
    public function is_preflight_check_required($attemptid) {
        
//         global $CFG, $PAGE, $DB, $USER;
//         echo '<br><br><br> In pre-flight <br>';
        
//         // User details.
//         $sessionkey = sesskey();
//         $userid     = $USER->id;
//         $username   = $USER->username;
        
//         $quiz       = $this->quizobj->get_quiz();
//         $quizid     = $this->quizobj->get_quizid();
//         $cmid       = $this->quizobj->get_cmid();
        
        
//         if ($unfinishedattempt = quiz_get_user_attempt_unfinished($quiz->id, $USER->id)) {
//             $unfinishedattemptid = $unfinishedattempt->id;
//             $unfinished = $unfinishedattempt->state == quiz_attempt::IN_PROGRESS;
            
//             if ($unfinished) {
//                 echo '<br><br><br> Creating override <br>';
//                 $this->create_user_override($cmid, $quiz);
//             }
//         }
        
        return false;
    }
/*
    public function setup_attempt_page($page) {
        global $CFG, $PAGE, $_SESSION, $DB, $USER, $HBCFG;

        // User details.
        $sessionkey = sesskey();
        $userid     = $USER->id;
        $username   = $USER->username;
        
        $quiz       = $this->quizobj->get_quiz();
        $quizid     = $this->quizobj->get_quizid();
        $cmid       = $this->quizobj->get_cmid();
        
        
        if ($unfinishedattempt = quiz_get_user_attempt_unfinished($quiz->id, $USER->id)) {
            $unfinishedattemptid = $unfinishedattempt->id;
            $unfinished = $unfinishedattempt->state == quiz_attempt::IN_PROGRESS;
            
            
            //             $script = " <script>
            // 			            function findButtonText() {
            // 					        $(document).ready(function() {
                
                //                 				if(document.getElementByClass('singlebutton quizstartbuttondiv')) {
                
                //                 				}
                //     				        });
                // 					    }
                // 				        </script>";
                //             $script .= "<script type='text/javascript'>countDownTimer($diffMilliSecs);</script>";
            
            if ($unfinished) {
                echo '<br><br><br> Setup attempt <br>';
                $this->create_user_override($cmid, $quiz);
            }
        }
    }
*/
    protected function create_user_override($cmid, $quiz, $unfinishedattempt, $state = null) {
        global $DB, $CFG, $USER;

        $context    = context_module::instance($cmid);
        $groupmode  = null;
        $action     = null;
        $override   = null;
        $userid     = $USER->id;
       
        
        // Add or edit an override.
//         require_capability('mod/quiz:manageoverrides', $context);

        // Creating a new override.
//         $data = new stdClass();

        // Merge quiz defaults with data.
//         $keys = array('timeopen', 'timeclose', 'timelimit', 'attempts', 'password');
//         foreach ($keys as $key) {
//             if (!isset($data->{$key}) || $reset) {
//                 $data->{$key} = $quiz->{$key};
//             }
//         }

        // True if group-based override.
        //         $action = null;require_once($CFG->dirroot . '/config.php');
        require_once($CFG->dirroot . '/mod/quiz/lib.php');
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
//         $groupmode = !empty($data->groupid) || ($action === 'addgroup' && empty($overrideid));

        // Setup the form data required for processing as in overrideedit.php file.
//         $override = new stdClass();
// //         $override->action = 'adduser';
//         $override->cmid = $cmid;
//         $override->quiz = $quiz->id;
// //         $override->_qf__quiz_override_form = 1;
// //         $override->mform_isexpanded_id_override = 1;
//         $override->userid = '';
//         $override->password = '';
//         $override->timeopen = $quiz->timeopen;
//         $override->timeclose = $quiz->timeclose;
//         $override->timelimit = 60;
//         $override->attempts = $quiz->attempts;
// //         $override->submitbutton = 'Save';

//         $dataobj = new timelimitoverride();

//         if($state === 'finished') {
//             // $dataobj->reset_timelimit_override($cmid, $roomid, $override, $quiz);
//         } else {
// //             echo '<br>-- in create ovrrde --<br>';
// //             print_object($quiz);
//             $dataobj->create_timelimit_override($cmid, $roomid, $override, $quiz);
//         }
        
        //=====================================================================
        
        // Setup the form data required for processing as in overrideedit.php file.
        $fromform = new stdClass();
        //         $fromform->action = 'adduser';
        $fromform->cmid = $cmid;
        $fromform->quiz = $quiz->id;
        //         $fromform->_qf__quiz_override_form = 1;
        //         $fromform->mform_isexpanded_id_override = 1;
        $fromform->userid = '';
        $fromform->password = '';
        $fromform->timeopen = $quiz->timeopen;
//         if($quiz->timeclose){
        $timeclose = $quiz->timeclose + 60;
        $fromform->timeclose = $timeclose;
//         } else {
//             $fromform->timeclose = $quiz->timeclose;
//         }
        $timelimit = $quiz->timelimit + 60;
        $fromform->timelimit = $timelimit;
        $fromform->attempts = $quiz->attempts;
        
//         $transaction = $DB->start_delegated_transaction();
        
        //=====================================================================
        // Process the data.
//         $fromform->quiz = $quiz->id;
        $fromform->userid = $userid;
        
        // Replace unchanged values with null.
        $keys = array('timeopen', 'timeclose', 'timelimit', 'attempts', 'password');
        foreach ($keys as $key) {
            if ($fromform->{$key} == $quiz->{$key}) {
                $fromform->{$key} = null;
            }
        }
        
        // See if we are replacing an existing override.
        $userorgroupchanged = false;
        if (empty($override->id)) {
            $userorgroupchanged = true;
        } else if (!empty($fromform->userid)) {
            $userorgroupchanged = $fromform->userid !== $override->userid;
        } else {
            $userorgroupchanged = $fromform->groupid !== $override->groupid;
        }
        
        if ($userorgroupchanged) {
            $conditions = array(
                'quiz' => $quiz->id,
                'userid' => empty($fromform->userid)? null : $fromform->userid,
                'groupid' => empty($fromform->groupid)? null : $fromform->groupid);
            if ($oldoverride = $DB->get_record('quiz_overrides', $conditions)) {
                // There is an old override, so we merge any new settings on top of
                // the older override.
                foreach ($keys as $key) {
                    if (is_null($fromform->{$key})) {
                        $fromform->{$key} = $oldoverride->{$key};
                    }
                }
                // Set the course module id before calling quiz_delete_override().
                $quiz->cmid = $cmid;
                
//                 $sql = "LOCK TABLE {quiz_overrides} WRITE,
//                                    {event} WRITE,
//                                    {context} WRITE
//                                     ";
//                 $DB->execute($sql);
                quiz_delete_override($quiz, $oldoverride->id);
//                 $sql = "UNLOCK TABLES";
//                 $DB->execute($sql);
            }
        }
        
        // Set the common parameters for one of the events we may be triggering.
        $params = array(
            'context' => $context,
            'other' => array(
                'quizid' => $quiz->id
            )
        );
        
        if (!empty($override->id)) {
                        
            $fromform->id = $override->id;
      
//             $sql = "LOCK TABLE {quiz_overrides} WRITE";
//             $DB->execute($sql);
            $DB->update_record('quiz_overrides', $fromform);
//             $sql = "UNLOCK TABLES";
//             $DB->execute($sql);
                    
            // Determine which override updated event to fire.
            $params['objectid'] = $override->id;
            if (!$groupmode) {
                $params['relateduserid'] = $fromform->userid;
                $event = \mod_quiz\event\user_override_updated::create($params);
            } else {
                $params['other']['groupid'] = $fromform->groupid;
                $event = \mod_quiz\event\group_override_updated::create($params);
            }
            
            // Trigger the override updated event.
            $event->trigger();
        } 
        else {
            unset($fromform->id);

//             $sql = "LOCK TABLE {quiz_overrides} WRITE";
//             $DB->execute($sql);
            $fromform->id = $DB->insert_record('quiz_overrides', $fromform);
//             $sql = "UNLOCK TABLES";
//             $DB->execute($sql);
            
            // Determine which override created event to fire.
            $params['objectid'] = $fromform->id;
            if (!$groupmode) {
                $params['relateduserid'] = $fromform->userid;
                $event = \mod_quiz\event\user_override_created::create($params);
            } else {
                $params['other']['groupid'] = $fromform->groupid;
                $event = \mod_quiz\event\group_override_created::create($params);
            }
            
            // Trigger the override created event.
            $event->trigger();
        }
        
//         $sql = "SET autocommit=0";
//         $DB->execute($sql);
//          $sql = "LOCK TABLES {quiz_attempts} quiza WRITE, 
//                              {quiz_attempts} iquiza WRITE, 
//                              {quiz} quiz WRITE, 
//                              {quiz} iquiz WRITE,
//                              {quiz_overrides} quo WRITE,
//                              {groups_members} gm WRITE,
//                              {quiz_overrides} qgo1 WRITE,
//                              {quiz_overrides} qgo2 WRITE,
//                              {quiz_overrides} qgo3 WRITE,
//                              {quiz_overrides} qgo4 WRITE
//                              ";
//          $DB->execute($sql);
        //quiz_update_open_attempts(array('userid'=>$userid, 'quizid'=>$quiz->id));
        
        /*
        $sql = "UPDATE {quiz_attempts} quiza
                        JOIN {quiz} quiz ON quiz.id = quiza.quiz
                        JOIN ( 
          SELECT iquiza.id,
           COALESCE(MAX(quo.timeclose), MAX(qgo1.timeclose), MAX(qgo2.timeclose), iquiz.timeclose) AS usertimeclose,
           COALESCE(MAX(quo.timelimit), MAX(qgo3.timelimit), MAX(qgo4.timelimit), iquiz.timelimit) AS usertimelimit

           FROM {quiz_attempts} iquiza
           JOIN {quiz} iquiz ON iquiz.id = iquiza.quiz
      LEFT JOIN {quiz_overrides} quo ON quo.quiz = iquiza.quiz AND quo.userid = iquiza.userid
      LEFT JOIN {quiz_overrides} qgo1 ON qgo1.quiz = iquiza.quiz AND qgo1.timeclose = 0
      LEFT JOIN {quiz_overrides} qgo2 ON qgo2.quiz = iquiza.quiz AND qgo2.timeclose > 0
      LEFT JOIN {quiz_overrides} qgo3 ON qgo3.quiz = iquiza.quiz AND qgo3.timelimit = 0
      LEFT JOIN {quiz_overrides} qgo4 ON qgo4.quiz = iquiza.quiz AND qgo4.timelimit > 0
          WHERE iquiza.state IN ('inprogress', 'overdue')
                AND iquiza.userid = :iuid3
                AND iquiza.quiz = :iqid5
       GROUP BY iquiza.id, iquiz.id, iquiz.timeclose, iquiz.timelimit ) quizauser ON quizauser.id = quiza.id
                         SET quiza.timecheckstate = 
          CASE WHEN quizauser.usertimelimit = 0 AND quizauser.usertimeclose = 0 THEN NULL
               WHEN quizauser.usertimelimit = 0 THEN quizauser.usertimeclose
               WHEN quizauser.usertimeclose = 0 THEN quiza.timestart + quizauser.usertimelimit
               WHEN quiza.timestart + quizauser.usertimelimit < quizauser.usertimeclose THEN quiza.timestart + quizauser.usertimelimit
               ELSE quizauser.usertimeclose END +
          CASE WHEN quiza.state = 'overdue' THEN quiz.graceperiod ELSE 0 END
                       WHERE quiza.state IN ('inprogress', 'overdue')
                         AND quiza.userid = :uid2
                         AND quiza.quiz = :qid4";
        
        $params = array("uid2" => $userid,
                        "iuid3" => $userid,
                        "qid4" => $quiz->id,
                        "iqid5" => $quiz->id);        
        $DB->execute($sql, $params);
        */
        
        
        
//         $newsql = "UPDATE {quiz_attempts} quiza
//                         SET quiza.timecheckstate = 
//                         CASE WHEN $quiz->timeclose = 0 THEN quiza.timestart + $timelimit
//                              WHEN quiza.timestart + $timelimit < $quiz->timeclose THEN quiza.timestart + $timelimit
//                              WHEN quiza.timestart + $timelimit > $quiz->timeclose THEN $quiz->timeclose + ($quiz->timeclose - ($quiz->timestart + $timelimit))
//                              ELSE $quiz->timeclose END +
//                         CASE WHEN quiza.state = 'overdue' THEN $quiz->graceperiod ELSE 0 END
//                             WHERE quiza.state IN ('inprogress', 'overdue')
//                               AND quiza.userid = $userid
//                               AND quiza.quiz = $quiz->id ORDER BY quiza.id desc LIMIT 1";
//         $DB->execute($newsql);

        
//         echo '<br>-----------quiz-------------';
//         print_object($quiz);
        //===================================================================
        /*
        echo '<br>before tcs - ' . $unfinishedattempt->timecheckstate;
        $quiza = $unfinishedattempt;
        
        if($quiz->timeclose == 0) {
//             echo 'in here 1';
            $timecheckstate = $quiza->timestart + $timelimit;
            
        } else if (($quiza->timestart + $timelimit) < $quiz->timeclose) {
//             echo 'in here 2';
            $timecheckstate = $quiza->timestart + $timelimit;
            
        } else if (($quiza->timestart + $timelimit) > $quiz->timeclose) {
            echo '<br>in here 3';
//             echo '<br>ts - ' . $quiza->timestart;
//             echo '<br>tc - ' . $quiza->timefinish;
//             echo '<br>tl - ' . $timelimit;
//             echo '<br>tclose - ' . $quiz->timeclose;
//             echo '<br>topen - ' . $quiz->timeopen;
//             $timecheckstate = $quiza->timestart + $timelimit;
            $timecheckstate = $quiz->timeclose + ($quiz->timeclose - ($quiza->timestart + $timelimit));
            echo '<br>after tcs1 - ' . $timecheckstate;
            
        } else {
//             echo 'in here 4';
            $timecheckstate = $quiz->timeclose;
            
        }
            
        if($quiza->state == 'overdue') {
            $timecheckstate += $quiz->graceperiod;
        }
         
        $quiza->timecheckstate = $timecheckstate;
        $DB->update_record('quiz_attempts', $quiza);
        echo '<br>after tcs2 - ' . date('d m Y g:i a',$quiza->timecheckstate);
        */
        //======================================================================
        $timecheckstate = $quiza->timestart + $timelimit;
        $DB->set_field('quiz_attempts', 'timecheckstate', $time, array('id' => $unfinishedattempt->id));
        
//          $sql = "COMMIT";
//          $DB->execute($sql);
//          $sql = "UNLOCK TABLES";
//          $DB->execute($sql);
         
//         if ($groupmode) {
//             // Priorities may have shifted, so we need to update all of the calendar events for group overrides.
//             quiz_update_events($quiz);
//         } else {
            // User override. We only need to update the calendar event for this user override.
            quiz_update_events($quiz, $fromform);
//         }
        
//         if (!empty($fromform->submitbutton)) {
//             redirect($overridelisturl);
//         }
        
//         // The user pressed the 'again' button, so redirect back to this page.
//         $url->remove_params('cmid');
//         $url->param('action', 'duplicate');
//         $url->param('id', $fromform->id);
//         redirect($url);

//         $transaction->allow_commit();
                   
    }
    
   
    public static function add_settings_form_fields(
        mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
            $odsettingsarray   = array();
            
            $odsettingsarray[] = $mform->createElement('select', 'odrequired',
                get_string('odrequired', 'quizaccess_overridedemo'), array(
                    0 => get_string('notrequired', 'quizaccess_overridedemo'),
                    1 => get_string('odrequiredoption', 'quizaccess_overridedemo')
                ));
            
            //         $hbmonsettingsarray[] = $mform->createElement('advcheckbox', 'allowifunassigned', '', 'Allow Unmapped', '', array(0, 1));
            //         $mform->disabledIf('allowifunassigned', 'hbmonrequired', 'neq', 1);
            
//             $radioarray = array();
//             $hbmonsettingsarray[]= $mform->createElement('radio', 'hbmonmode', '', get_string('automatic', 'quizaccess_heartbeatmonitor'), 1);
//             $hbmonsettingsarray[]= $mform->createElement('radio', 'hbmonmode', '', get_string('manual', 'quizaccess_heartbeatmonitor'), 0);
//             //         $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
//             //         $hbmonsettingsarray[] = $radioarray;
//             $mform->disabledIf('hbmonmode', 'hbmonrequired', 'neq', 1);
            
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
