The recompletion plugin is supported and maintained by Dan Marsden

Branches
--------
The git branches here support the following versions.

| Moodle version     | Branch      |
| ----------------- | ----------- |
| Mooodle 3.5 - 3.7  | MOODLE_37_STABLE |
| Mooodle 3.8  | MOODLE_38_STABLE |
| Mooodle 3.9 - 3.10  | MOODLE_39_STABLE |
| Moodle 3.11 and higher | MOODLE_311_STABLE |

This plugin adds course level settings for recompletion - clearing all course and activity completion for a user based on the duration set notifying the student they need to return to the course and recomplete it.

This plugin could be used to facilitate annual re-certification.

The following information is cleared from the course during recompletion:
* All activity grades cleared (and saved to standard grade history tables.)
* All activity completion and course completion flags removed. (with the option to archive this information)

The following activities have extra support:
1) Quiz
You can choose to delete all existing quiz attempt data with the option to archive the information or,
you can keep the existing attempts and give the student the ability to add new attempts.

2) SCORM
You can choose to delete all existing SCORM attempt data with the option to archive the information.

3) Assignment
You can choose to give the student another attempt (if the assignment is configured to allow reopening and the maximum number of attempts has not been reached.

If a user has already completed the course, and a teacher performs a grading action on an assignment, you can choose to have the course completion date updated at the same time.

Other activities that store user data will have the activity completion data reset, but may require manual intervention as they are not yet supported fully.
Get in touch privately if you would like to fund support for other activities with user data.


For more documentation on this plugin please see:
https://github.com/danmarsden/moodle-local_recompletion/wiki
