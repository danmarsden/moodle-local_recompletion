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
 * Contains participants class copied from core partcipants_table, copyright 2017 Mark Nelson <markn@moodle.com>
 *
 * @package    local_recompletion
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion;

use context;
use core_user\output\status_field;
use DateTime;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');

/**
 * Class for the displaying the participants table.
 *
 * @package    local_recompletion
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class participants_table extends \table_sql {

    /**
     * @var int $courseid The course id
     */
    protected $courseid;

    /**
     * @var int|false False if groups not used, int if groups used, 0 for all groups.
     */
    protected $currentgroup;

    /**
     * @var int $accesssince The time the user last accessed the site
     */
    protected $accesssince;

    /**
     * @var int $roleid The role we are including, 0 means all enrolled users
     */
    protected $roleid;

    /**
     * @var int $enrolid The applied filter for the user enrolment ID.
     */
    protected $enrolid;

    /**
     * @var int $status The applied filter for the user's enrolment status.
     */
    protected $status;

    /**
     * @var string $search The string being searched.
     */
    protected $search;

    /**
     * @var bool $selectall Has the user selected all users on the page?
     */
    protected $selectall;

    /**
     * @var string[] The list of countries.
     */
    protected $countries;

    /**
     * @var \stdClass[] The list of groups with membership info for the course.
     */
    protected $groups;

    /**
     * @var string[] Extra fields to display.
     */
    protected $extrafields;

    /**
     * @var \stdClass $course The course details.
     */
    protected $course;

    /**
     * @var  context $context The course context.
     */
    protected $context;

    /**
     * @var \stdClass[] List of roles indexed by roleid.
     */
    protected $allroles;

    /**
     * @var \stdClass[] List of roles indexed by roleid.
     */
    protected $allroleassignments;

    /**
     * @var \stdClass[] Assignable roles in this course.
     */
    protected $assignableroles;

    /**
     * @var \stdClass[] Profile roles in this course.
     */
    protected $profileroles;

    /**
     * @var bool|mixed Is recompletion enabled in this course.
     */
    protected $recompletionenabled;

    /** @var \stdClass[] $viewableroles */
    private $viewableroles;

    /**
     * Sets up the table.
     *
     * @param int $courseid
     * @param int|false $currentgroup False if groups not used, int if groups used, 0 all groups, USERSWITHOUTGROUP for no group
     * @param int $accesssince The time the user last accessed the site
     * @param int $roleid The role we are including, 0 means all enrolled users
     * @param int $enrolid The applied filter for the user enrolment ID.
     * @param int $status The applied filter for the user's enrolment status.
     * @param string|array $search The search string(s)
     * @param bool $bulkoperations Is the user allowed to perform bulk operations?
     * @param bool $selectall Has the user selected all users on the page?
     */
    public function __construct($courseid, $currentgroup, $accesssince, $roleid, $enrolid, $status, $search,
                                $bulkoperations, $selectall) {
        global $CFG, $DB;

        parent::__construct('user-index-participants-' . $courseid);

        // Get the context.
        $this->course = get_course($courseid);
        $context = \context_course::instance($courseid, MUST_EXIST);
        $this->context = $context;

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        if ($bulkoperations) {
            $headers[] = get_string('select');
            $columns[] = 'select';
        }

        $headers[] = get_string('fullname');
        $columns[] = 'fullname';

        $extrafields = get_extra_user_fields($context);
        foreach ($extrafields as $field) {
            $headers[] = get_user_field_name($field);
            $columns[] = $field;
        }

        // Get the list of fields we have to hide.
        $hiddenfields = array();
        if (!has_capability('moodle/course:viewhiddenuserfields', $context)) {
            $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
        }

        // Add column for groups if the user can view them.
        $canseegroups = !isset($hiddenfields['groups']);
        if ($canseegroups) {
            $headers[] = get_string('groups');
            $columns[] = 'groups';
        }

        // Do not show the columns if it exists in the hiddenfields array.
        if (!isset($hiddenfields['lastaccess'])) {
            if ($courseid == SITEID) {
                $headers[] = get_string('lastsiteaccess');
            } else {
                $headers[] = get_string('lastcourseaccess');
            }
            $columns[] = 'lastaccess';
        }

        $headers[] = get_string('coursecompletion');
        $columns[] = 'coursecompletion';

        $this->define_columns($columns);
        $this->define_headers($headers);

        // The name column is a header.
        if (method_exists($this, 'define_header_column')) { // Remove when only 3.7+ supported.
            $this->define_header_column('fullname');
        }

        // Make this table sorted by last name by default.
        $this->sortable(true, 'lastname');

        $this->no_sorting('select');
        $this->no_sorting('roles');
        if ($canseegroups) {
            $this->no_sorting('groups');
        }

        $this->set_attribute('id', 'participants');

        // Set the variables we need to use later.
        $this->currentgroup = $currentgroup;
        $this->accesssince = $accesssince;
        $this->search = $search;
        $this->enrolid = $enrolid;
        $this->status = $status;
        $this->selectall = $selectall;
        $this->countries = get_string_manager()->get_list_of_countries(true);
        $this->extrafields = $extrafields;
        $this->context = $context;
        $this->recompletionenabled = $DB->get_field('local_recompletion_config',
            'value', array('course' => $this->course->id, 'name' => 'enable'));
        if ($canseegroups) {
            $this->groups = groups_get_all_groups($courseid, 0, 0, 'g.*', true);
        }
    }

    /**
     * Render the participants table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Whether to use the initials bar which will only be used if there is a fullname column defined.
     * @param string $downloadhelpbutton
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        global $PAGE;

        parent::out($pagesize, $useinitialsbar, $downloadhelpbutton);

        if (has_capability('moodle/course:enrolreview', $this->context)) {
            $params = ['contextid' => $this->context->id, 'courseid' => $this->course->id];
            $PAGE->requires->js_call_amd('core_user/status_field', 'init', [$params]);
        }
    }

    /**
     * Generate the select column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_select($data) {
        if ($this->selectall) {
            $checked = 'checked="true"';
        } else {
            $checked = '';
        }
        return '<input type="checkbox" class="usercheckbox" name="user' . $data->id . '" ' . $checked . '/>';
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_fullname($data) {
        global $OUTPUT;

        return $OUTPUT->user_picture($data, array('size' => 35, 'courseid' => $this->course->id, 'includefullname' => true));
    }

    /**
     * Generate the groups column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_groups($data) {
        global $OUTPUT;

        $usergroups = [];
        foreach ($this->groups as $coursegroup) {
            if (isset($coursegroup->members[$data->id])) {
                $usergroups[] = $coursegroup->id;
            }
        }
        $editable = new \core_group\output\user_groups_editable($this->course, $this->context, $data, $this->groups, $usergroups);
        return $OUTPUT->render_from_template('core/inplace_editable', $editable->export_for_template($OUTPUT));
    }

    /**
     * Generate the country column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_country($data) {
        if (!empty($this->countries[$data->country])) {
            return $this->countries[$data->country];
        }
        return '';
    }

    /**
     * Generate the last access column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_lastaccess($data) {
        if ($data->lastaccess) {
            return format_time(time() - $data->lastaccess);
        }

        return get_string('never');
    }

    /**
     * Generate the course completion column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_coursecompletion($data) {
        global $OUTPUT;
        // Load completion from cache (hopefully?)
        $params = array(
            'userid'    => $data->id,
            'course'    => $this->course->id
        );

        $ccompletion = new \completion_completion($params);
        $value = '';
        if ($ccompletion->is_complete()) {
            $value = userdate($ccompletion->timecompleted, get_string('strftimedatetimeshort', 'langconfig'));
        }
        $url = new \moodle_url('/local/recompletion/editcompletion.php', array('id' => $this->course->id, 'user' => $data->id));
        $value .= $OUTPUT->action_link($url, '', null, null, new \pix_icon('t/edit', get_string('edit')));
        if (!empty($this->recompletionenabled)) {
            $url = new \moodle_url('/local/recompletion/resetcompletion.php',
                array('id' => $this->course->id, 'user' => $data->id));
            $value .= $OUTPUT->action_link($url, get_string('resetallcompletion', 'local_recompletion'));
        }
        return $value;
    }

    /**
     * This function is used for the extra user fields.
     *
     * These are being dynamically added to the table so there are no functions 'col_<userfieldname>' as
     * the list has the potential to increase in the future and we don't want to have to remember to add
     * a new method to this class. We also don't want to pollute this class with unnecessary methods.
     *
     * @param string $colname The column name
     * @param \stdClass $data
     * @return string
     */
    public function other_cols($colname, $data) {
        // Do not process if it is not a part of the extra fields.
        if (!in_array($colname, $this->extrafields)) {
            return '';
        }

        return s($data->{$colname});
    }

    /**
     * Query the database for results to display in the table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        list($twhere, $tparams) = $this->get_sql_where();

        $total = $this->user_get_total_participants($this->course->id, $this->currentgroup, $this->accesssince,
            $this->roleid, $this->enrolid, $this->status, $this->search, $twhere, $tparams);

        $this->pagesize($pagesize, $total);

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = 'ORDER BY ' . $sort;
        }
        $rawdata = $this->user_get_participants($this->course->id, $this->currentgroup, $this->accesssince,
            $this->roleid, $this->enrolid, $this->status, $this->search, $twhere, $tparams, $sort, $this->get_page_start(),
            $this->get_page_size());
        $this->rawdata = [];
        foreach ($rawdata as $user) {
            $this->rawdata[$user->id] = $user;
        }
        $rawdata->close();

        if ($this->rawdata) {
            $this->allroleassignments = get_users_roles($this->context, array_keys($this->rawdata),
                true, 'c.contextlevel DESC, r.sortorder ASC');
        } else {
            $this->allroleassignments = [];
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars(true);
        }
    }

    /**
     * copied from old deprecated core function to allow single branch of recompletion to support multiple branches
     * without lots of deprecation messages.
     * THIS IS hacky but works for now.
     *
     * @param int $courseid
     * @param int $groupid
     * @param int $accesssince
     * @param int $roleid
     * @param int $enrolid
     * @param int $statusid
     * @param string $search
     * @param string $additionalwhere
     * @param array $additionalparams
     * @return int
     * @throws \dml_exception
     */
    private function user_get_total_participants($courseid, $groupid = 0, $accesssince = 0, $roleid = 0, $enrolid = 0,
                                                 $statusid = -1, $search = '', $additionalwhere = '',
                                                 $additionalparams = array()) {
        global $DB;

        list($select, $from, $where, $params) = $this->user_get_participants_sql($courseid, $groupid, $accesssince,
            $roleid, $enrolid, $statusid, $search, $additionalwhere, $additionalparams);

        return $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);
    }

    /**
     * Copied from old deprecated core function to allow single branch of recompletion to support multiple branches
     * without lots of deprecation messages.
     * THIS IS hacky but works for now.
     *
     * @param int $courseid
     * @param int $groupid
     * @param int $accesssince
     * @param int $roleid
     * @param int $enrolid
     * @param int $statusid
     * @param string $search
     * @param string $additionalwhere
     * @param array $additionalparams
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function user_get_participants_sql($courseid, $groupid = 0, $accesssince = 0, $roleid = 0, $enrolid = 0,
                                               $statusid = -1, $search = '', $additionalwhere = '',
                                               $additionalparams = array()) {
        global $DB, $USER, $CFG;

        // Get the context.
        $context = \context_course::instance($courseid, MUST_EXIST);

        $isfrontpage = ($courseid == SITEID);

        // Default filter settings. We only show active by default, especially if the user has no capability to review enrolments.
        $onlyactive = true;
        $onlysuspended = false;
        if (has_capability('moodle/course:enrolreview', $context) &&
            (has_capability('moodle/course:viewsuspendedusers', $context))) {
            switch ($statusid) {
                case ENROL_USER_ACTIVE:
                    // Nothing to do here.
                    break;
                case ENROL_USER_SUSPENDED:
                    $onlyactive = false;
                    $onlysuspended = true;
                    break;
                default:
                    // If the user has capability to review user enrolments, but statusid is set to -1, set $onlyactive to false.
                    $onlyactive = false;
                    break;
            }
        }

        list($esql, $params) = get_enrolled_sql($context, null, $groupid, $onlyactive, $onlysuspended, $enrolid);

        $joins = array('FROM {user} u');
        $wheres = array();

        $userfields = get_extra_user_fields($context);
        $userfieldssql = \user_picture::fields('u', $userfields);

        if ($isfrontpage) {
            $select = "SELECT $userfieldssql, u.lastaccess";
            $joins[] = "JOIN ($esql) e ON e.id = u.id"; // Everybody on the frontpage usually.
            if ($accesssince) {
                $wheres[] = user_get_user_lastaccess_sql($accesssince);
            }
        } else {
            $select = "SELECT $userfieldssql, COALESCE(ul.timeaccess, 0) AS lastaccess";
            $joins[] = "JOIN ($esql) e ON e.id = u.id"; // Course enrolled users only.
            // Not everybody has accessed the course yet.
            $joins[] = 'LEFT JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = :courseid)';
            $params['courseid'] = $courseid;
            if ($accesssince) {
                $wheres[] = user_get_course_lastaccess_sql($accesssince);
            }
        }

        // Performance hacks - we preload user contexts together with accounts.
        $ccselect = ', ' . \context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = 'LEFT JOIN {context} ctx ON (ctx.instanceid = u.id AND ctx.contextlevel = :contextlevel)';
        $params['contextlevel'] = CONTEXT_USER;
        $select .= $ccselect;
        $joins[] = $ccjoin;

        // Limit list to users with some role only.
        if ($roleid) {
            // We want to query both the current context and parent contexts.
            list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($context->get_parent_context_ids(true),
                SQL_PARAMS_NAMED, 'relatedctx');

            // Get users without any role.
            if ($roleid == -1) {
                $wheres[] = "u.id NOT IN (SELECT userid FROM {role_assignments} WHERE contextid $relatedctxsql)";
                $params = array_merge($params, $relatedctxparams);
            } else {
                $wheres[] = "u.id IN (SELECT userid FROM {role_assignments} WHERE roleid = :roleid AND contextid $relatedctxsql)";
                $params = array_merge($params, array('roleid' => $roleid), $relatedctxparams);
            }
        }

        if (!empty($search)) {
            if (!is_array($search)) {
                $search = [$search];
            }
            foreach ($search as $index => $keyword) {
                $searchkey1 = 'search' . $index . '1';
                $searchkey2 = 'search' . $index . '2';
                $searchkey3 = 'search' . $index . '3';
                $searchkey4 = 'search' . $index . '4';
                $searchkey5 = 'search' . $index . '5';
                $searchkey6 = 'search' . $index . '6';
                $searchkey7 = 'search' . $index . '7';

                $conditions = array();
                // Search by fullname.
                $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
                $conditions[] = $DB->sql_like($fullname, ':' . $searchkey1, false, false);

                // Search by email.
                $email = $DB->sql_like('email', ':' . $searchkey2, false, false);
                if (!in_array('email', $userfields)) {
                    $maildisplay = 'maildisplay' . $index;
                    $userid1 = 'userid' . $index . '1';
                    // Prevent users who hide their email address from being found by others
                    // who aren't allowed to see hidden email addresses.
                    $email = "(". $email ." AND (" .
                        "u.maildisplay <> :$maildisplay " .
                        "OR u.id = :$userid1". // User can always find himself.
                        "))";
                    $params[$maildisplay] = \core_user::MAILDISPLAY_HIDE;
                    $params[$userid1] = $USER->id;
                }
                $conditions[] = $email;

                // Search by idnumber.
                $idnumber = $DB->sql_like('idnumber', ':' . $searchkey3, false, false);
                if (!in_array('idnumber', $userfields)) {
                    $userid2 = 'userid' . $index . '2';
                    // Users who aren't allowed to see idnumbers should at most find themselves
                    // when searching for an idnumber.
                    $idnumber = "(". $idnumber . " AND u.id = :$userid2)";
                    $params[$userid2] = $USER->id;
                }
                $conditions[] = $idnumber;

                if (!empty($CFG->showuseridentity)) {
                    // Search all user identify fields.
                    $extrasearchfields = explode(',', $CFG->showuseridentity);
                    foreach ($extrasearchfields as $extrasearchfield) {
                        if (in_array($extrasearchfield, ['email', 'idnumber', 'country'])) {
                            // Already covered above. Search by country not supported.
                            continue;
                        }
                        $param = $searchkey3 . $extrasearchfield;
                        $condition = $DB->sql_like($extrasearchfield, ':' . $param, false, false);
                        $params[$param] = "%$keyword%";
                        if (!in_array($extrasearchfield, $userfields)) {
                            // User cannot see this field, but allow match if their own account.
                            $userid3 = 'userid' . $index . '3' . $extrasearchfield;
                            $condition = "(". $condition . " AND u.id = :$userid3)";
                            $params[$userid3] = $USER->id;
                        }
                        $conditions[] = $condition;
                    }
                }

                // Search by middlename.
                $middlename = $DB->sql_like('middlename', ':' . $searchkey4, false, false);
                $conditions[] = $middlename;

                // Search by alternatename.
                $alternatename = $DB->sql_like('alternatename', ':' . $searchkey5, false, false);
                $conditions[] = $alternatename;

                // Search by firstnamephonetic.
                $firstnamephonetic = $DB->sql_like('firstnamephonetic', ':' . $searchkey6, false, false);
                $conditions[] = $firstnamephonetic;

                // Search by lastnamephonetic.
                $lastnamephonetic = $DB->sql_like('lastnamephonetic', ':' . $searchkey7, false, false);
                $conditions[] = $lastnamephonetic;

                $wheres[] = "(". implode(" OR ", $conditions) .") ";
                $params[$searchkey1] = "%$keyword%";
                $params[$searchkey2] = "%$keyword%";
                $params[$searchkey3] = "%$keyword%";
                $params[$searchkey4] = "%$keyword%";
                $params[$searchkey5] = "%$keyword%";
                $params[$searchkey6] = "%$keyword%";
                $params[$searchkey7] = "%$keyword%";
            }
        }

        if (!empty($additionalwhere)) {
            $wheres[] = $additionalwhere;
            $params = array_merge($params, $additionalparams);
        }

        $from = implode("\n", $joins);
        if ($wheres) {
            $where = 'WHERE ' . implode(' AND ', $wheres);
        } else {
            $where = '';
        }

        return array($select, $from, $where, $params);
    }

    /**
     * Copied from old deprecated core function to allow single branch of recompletion to support multiple branches
     * without lots of deprecation messages.
     * THIS IS hacky but works for now.
     *
     * @param int $courseid
     * @param int $groupid
     * @param int $accesssince
     * @param int $roleid
     * @param int $enrolid
     * @param int $statusid
     * @param string $search
     * @param string $additionalwhere
     * @param array $additionalparams
     * @param string $sort
     * @param int $limitfrom
     * @param int $limitnum
     * @return \moodle_recordset
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function user_get_participants($courseid, $groupid = 0, $accesssince, $roleid, $enrolid = 0, $statusid, $search,
                                   $additionalwhere = '', $additionalparams = array(), $sort = '', $limitfrom = 0, $limitnum = 0) {
        global $DB;

        list($select, $from, $where, $params) = $this->user_get_participants_sql($courseid, $groupid, $accesssince, $roleid,
            $enrolid, $statusid, $search, $additionalwhere, $additionalparams);

        return $DB->get_recordset_sql("$select $from $where $sort", $params, $limitfrom, $limitnum);
    }
}

