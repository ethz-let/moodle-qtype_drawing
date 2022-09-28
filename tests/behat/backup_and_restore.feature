@qtype @qtype_drawing @qtype_drawing_backup
Feature: Test duplicating a quiz containing an drawing question
  As a teacher
  In order re-use my courses containing drawing questions
  I need to be able to backup and restore them

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
   And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |

  @javascript
  Scenario: Backup and restore a course containing one drawing question
    # first create drawing question
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I add a "drawing" question filling the form with:
      | id_name             | drawing-001                    |
      | id_questiontext     | Draw a biology cell.           |
      | id_generalfeedback  | This is general feedback       |
    Then I should see "drawing-001"

    # backup/restore course
    And I am on "Course 1" course homepage
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_drawing_backup.mbz |
    And I restore "test_drawing_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    And I navigate to "Question bank" in current page administration
    And I should see "drawing-001"
    And I choose "Edit question" action for "drawing-001" in the question bank
    Then the following fields match these values:
      | id_name             | drawing-001                                               |
      | id_questiontext     | Draw a biology cell.                                      |
      | id_generalfeedback  | This is general feedback                                  |
    And I press "Cancel"
