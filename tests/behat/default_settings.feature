@local @local_recompletion
Feature: Recompletion default settings are copied to new courses
  In order to reduce repeated course setup work
  As an administrator
  I need recompletion defaults to prefill new course recompletion settings

  Scenario: Saved recompletion defaults apply to a newly created course
    Given the following config values are set as admin:
      | enablecompletion | 1 |
    And I log in as "admin"
    And the following config values are set as admin:
      | recompletiontype         | ondemand | local_recompletion |
      | recompletionnotify       | enrolled | local_recompletion |
      | recompletionunenrolenable| 1        | local_recompletion |
      | deletegradedata          | 0        | local_recompletion |
      | archivecompletiondata    | 0        | local_recompletion |

    And the following "courses" exist:
      | fullname   | shortname | enablecompletion |
      | New course | NEW1      | 1                |
    When I am on "New course" course homepage
    And I navigate to "Course recompletion" in current page administration
    Then the field "Recompletion type" matches value "On demand"
    And the field "Recompletion message" matches value "Send to completed users with an enrollment"
    And the field "Reset completion on un-enrolment" matches value "1"
    And the field "Delete all grades for the user" matches value "0"
    And the field "Archive completion data" matches value "0"
