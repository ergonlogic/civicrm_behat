
CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Running tests
 * Creating features and scenarios

INTRODUCTION
------------
This is the home of CiviCRM Behavior Driven Development.

It's a collection of tests to verify the functionality of CiviCRM for pre-
deployment purposes. They are written in plain English as "Features" with
"Scenarios" beneath them, they outline how a piece of functionality is supposed
to work. Those English descriptions can then generate skeleton code for real
functionality testing.

For more background on Behat, see http://docs.behat.org

INSTALLATION
------------


"curl -s http://getcomposer.org/composer.phar > composer.phar"
  OR
"wget -nc http://getcomposer.org/composer.phar"

"php composer.phar install"

"cp behat.local.yml.example behat.local.yml"

modify behat.local.yml as needed.


RUNNING TESTS
-------------
To run tests, change into the repo project directory and run:

bin/behat

This will cycle through all of the available features and scenarios and output
their results.

See http://docs.behat.org/guides/6.cli.html for other, fancier ways to run tests.

Example commands:

  bin/behat - run all

  bin/behat --tags="anon&&~wip"   - test the scenario's that do not require logging in and are not a "work in progress"

  bin/behat features/pages/frontpage.feature - test the scenarios in the frontpage feature

  bin/behat -dl - list definitions
  bin/behat -di - list expanded definitions

  Profiles - these contain helpful configuration combinations

  bin/behat --profile="firefox" - run all tests through firefox using selenium2
  bin/behat --profile="wip" - work-in-progress, run tests that are still in dev
  bin/behat --profile="smoke" - run key tests (fast test)
  bin/behat --profile="no-slow" - don't run slow tests

FEATURES AND SCENARIOS
----------------------
Human-readable features and scenarios are available in the features/ directory.
The actual code for each can be found in the 'bootstrap' directory within.

A tutorial on how to write features, scenarios, and tests can be found at http://docs.behat.org/guides/1.gherkin.html

RUNNING TESTS AGAINST STAGING SITES
----------------------------------------

Pre-created users should be existing on the site; they must be made by hand at this time if the database has been refreshed. You'll find the users expected by the tests in the behat.local.yml.example



