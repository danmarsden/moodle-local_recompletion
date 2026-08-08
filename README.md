About this fork
----------------
This is a fork of Dan Marsden's `local_recompletion` plugin, based on
`MOODLE_405_STABLE`. Upstream doesn't support restricting recompletion by course
group (only by enrolment method), which this site's course setup requires, so
this fork adds a `restrictgroups` restriction (`classes/local/restrictions/groups.php`)
that works the same way as the existing `restrictenrol` one and applies to every
recompletion trigger type (on demand, period, schedule) since they all funnel
through `check_recompletion::reset_user()`.

What this plugin does
----------------------
This plugin adds course level settings for recompletion - clearing all course,
activity completion and all other related Moodle plugins' data for a user based
on the duration set, notifying the student they need to return to the course
and recomplete it.

It can be used to facilitate annual re-certification.

The following information is cleared from the course during recompletion:
* All activity grades cleared (and saved to standard grade history tables).
* All activity completion and course completion flags removed (with the option
  to archive this information).

The following activities have extra support:

1. **Quiz** - you can choose to delete all existing quiz attempt data with the
   option to archive the information, or keep the existing attempts and give
   the student the ability to add new attempts.
2. **SCORM** - you can choose to delete all existing SCORM attempt data with
   the option to archive the information.
3. **Assignment** - you can choose to give the student another attempt (if the
   assignment is configured to allow reopening and the maximum number of
   attempts has not been reached).

If a user has already completed the course, and a teacher performs a grading
action on an assignment, you can choose to have the course completion date
updated at the same time.

Other plugins that store user data will have the activity completion data, and
all related data reset, but may require manual intervention as they are not
yet fully supported.

Documentation
-------------
For more documentation on the base plugin, see the upstream wiki:
https://github.com/danmarsden/moodle-local_recompletion/wiki
