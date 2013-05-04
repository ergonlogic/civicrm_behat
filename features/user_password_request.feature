@user @anon
Feature: Request new password
  In order to log in to the site when I forgot my password
  As any user
  I should be able to request new password

  Background:
    #Given I am on the homepage
    Given I visit "/user/"
    And I follow "Request new password"

  Scenario: View texts and links
    Then I should see the heading "User account"
    Then I should see the following <links>
    | links                 |
    | Log in                |
    | Request new password  |
    And I should see "Username or e-mail address"

  Scenario: Enter username and submit
    When I fill in "Username or e-mail address" with "admin"
    And I press "E-mail new password"
    Then I should not see "Sorry, site user is not recognized as a user name or an e-mail address"
    And I should see "Further instructions have been sent to your e-mail address"

  @wip
  Scenario: Enter email and submit
    When I fill in "Username or e-mail address" with "siteadmin@mailinator.com"
    And I press "E-mail new password"
    Then I should not see "Sorry, siteadmin@mailinator.com is not recognized as a user name or an e-mail address"
    And I should see "Further instructions have been sent to your e-mail address"

  Scenario: Enter invalid username and submit
    When I fill in "Username or e-mail address" with "site user123"
    And I press "E-mail new password"
    Then I should not see "Further instructions have been sent to your e-mail address"
    And I should see "Sorry, site user123 is not recognized as a user name or an e-mail address"

  Scenario: Enter invalid email and submit
    When I fill in "Username or e-mail address" with "siteuser123@happypunch.com"
    And I press "E-mail new password"
    Then I should not see "Further instructions have been sent to your e-mail address"
    And I should see "Sorry, siteuser123@happypunch.com is not recognized as a user name or an e-mail address"
