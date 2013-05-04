@front
Feature: Site frontpage
  In order to have a view of the site frontpage
  As any user
  I should go to the site's frontpage

  Background:
    Given I am on the homepage

  @anon
  Scenario: Confirm I am viewing the frontpage as an anonymous user
    And I am not logged in
  #  Then I should see the text "Access denied"

  @anon
  Scenario: Confirm I see the login form
    And I am not logged in
    Then I should see the text "User login"
    And I should see the link "Request new password"

  Scenario: Confirm I am viewing the frontpage as an admin
    And I am logged in as "admin"
    Then I should see the text "Home"
