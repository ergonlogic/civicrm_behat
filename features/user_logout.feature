@user
Feature: User log out
  In order to leave the site and prevent others from using my account
  As an authenticated user
  I should be able to log out

  Scenario: Log in as site user and view links and texts
    Given I am logged in as "admin"
    # Then I should see the heading "admin"
    And I should see the following <links>
    | links                   |
    | My account              |
    | Log out                 |
    | View                    |
    | Edit                    |
    And I should not see the following <links>
    | links                 |
    | Create new account    |
    | Log in                |
    | Request new password  |
    And I should not see the following <texts>
    | texts     |
    | Username  |
    | Password  |

  Scenario: Admin logs out
    Given I am logged in as "admin"
    When I follow "Log out"
    Then I should be on "/"
    And I should not see the following <links>
    | links                   |
    | My account              |
    | Log out                 |
    When I go to "/admin"
    Then I should get a "403" HTTP response
    And I should see the following <texts>
    | texts     |
    | Access denied  |
    | You are not authorized to access this page. |



  @anon
  Scenario: Visit /user url anonymously
    Given I am not logged in
    When I visit "/user/login"
    Then I should see the heading "User account"
    And I should see the following <links>
    | links                 |
    | Log in                |
    | Request new password  |
    And I should see the following <texts>
    | texts     |
    | Username  |
    | Password  |
    And I should not see the following <links>
    | links                   |
    | My account              |
    | Log out                 |
    | View                    |
    | Edit                    |
