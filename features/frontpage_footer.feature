@front
Feature: frontpage footer
  In order to have easy access to different sections
  As any user
  I should be able to see footer links

  @anon
  Scenario Outline: View links and text in the footer
    Given I am on "<page>"
    And I should see the following <links> in "footer" area
    | links |

    Examples:
      | page |
      | /    |
