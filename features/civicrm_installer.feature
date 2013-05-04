@installer
Feature: Install CiviCRM
  In order to correctly install CiviCRM
  As a server administrator
  I should be able to use the CiviCRM installer

  Background:
    Given I visit "/sites/all/modules/civicrm/install/index.php"
    Then I fill in "MySQL server" with "IntentionallyInvalid" in the "civicrm_db_settings"
    Then I fill in "MySQL username" with "IntentionallyInvalid" in the "civicrm_db_settings"
    Then I fill in "MySQL password" with "IntentionallyInvalid" in the "civicrm_db_settings"
    Then I fill in "MySQL database" with "IntentionallyInvalid" in the "civicrm_db_settings"
    Then I fill in "MySQL server" with "IntentionallyInvalid" in the "drupal_db_settings"
    Then I fill in "MySQL username" with "IntentionallyInvalid" in the "drupal_db_settings"
    Then I fill in "MySQL password" with "IntentionallyInvalid" in the "drupal_db_settings"
    Then I fill in "MySQL database" with "IntentionallyInvalid" in the "drupal_db_settings"
    And I press the "Re-check requirements" button


  Scenario: Confirm we see error messages
    Then I should see the text "We are not able to install the software. Please see below for details." in the "civicrm_error" region
    Then I should see the text "Can't find the a MySQL server on 'IntentionallyInvalid': Unknown MySQL server host 'IntentionallyInvalid'"
    Then I should see the text "Can't find the a MySQL server on 'IntentionallyInvalid': Unknown MySQL server host 'IntentionallyInvalid' \(1\)"
#    Then I should see the text "That username/password doesn't work: Unknown MySQL server host 'IntentionallyInvalid' \(1\)" in the "civicrm_error" region
    Then I should see the text "I can't create new databases and the database 'IntentionallyInvalid' doesn't exist \(user 'IntentionallyInvalid' doesn't have CREATE DATABASE permissions.\)"
#    Then I should see the text "I can't create new databases and the database 'IntentionallyInvalid' doesn't exist \(user 'IntentionallyInvalid' doesn't have CREATE DATABASE permissions.\)" in the "civicrm_db" region
#    Then I should see the text "I can't create new databases and the database 'IntentionallyInvalid' doesn't exist \(user 'IntentionallyInvalid' doesn't have CREATE DATABASE permissions.\)" in the "civicrm_testResults" region
    Then I should see the text "Unable to create InnoDB tables. MySQL InnoDB support is required for CiviCRM but is either not available or not enabled in this MySQL database server. Could not determine if mysql has innodb support. Assuming no"
    Then I should see "Could not login to the database." in the "td.error" element
    #Then I should see the text "Could not login to the database." in the "civicrm_error" region

    Then I see the number "<number>" in the page element
