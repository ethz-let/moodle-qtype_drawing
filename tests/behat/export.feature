@qtype @qtype_drawing @qtype_drawing_export
Feature: Test exporting drawing questions
  As a teacher
  In order to be able to reuse my drawing questions
  I need to export them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  Scenario: Export drawing question
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I add a "drawing" question filling the form with:
      | id_name             | drawing-001                    |
      | id_questiontext     | Draw a biology cell.           |
      | id_generalfeedback  | This is general feedback       |
    And I am on the "Course 1" "core_question > course question export" page
    And I set the field "id_format_xml" to "1"
    And I press "Export questions to file"
    And I wait "10" seconds
    Then following "click here" should download between "1000" and "2700" bytes
    # If the download step is the last in the scenario then we can sometimes run
    # into the situation where the download page causes a http redirect but behat
    # has already conducted its reset (generating an error). By putting a logout
    # step we avoid behat doing the reset until we are off that page.
    And I log out
