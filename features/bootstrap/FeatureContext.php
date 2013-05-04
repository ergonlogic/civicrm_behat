<?php

/**
 * Some of our features need to run their scenarios sequentially
 * and we need a way to pass relevant data (like generated node id)
 * from one scenario to the next.  This class provides a simple
 * registry to pass data. This should be used only when absolutely
 * necessary as scenarios should be independent as often as possible.
 */
abstract class HackyDataRegistry {
  public static $data = array();
  public static function set($name, $value) {
    self::$data[$name] = $value;
  }
  public static function get($name) {
    $value = "";
    if (isset(self::$data[$name])) {
      $value = self::$data[$name];
    }
    return $value;
  }
}

use Behat\Behat\Exception\PendingException,
    Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;
use Symfony\Component\Process\Process;

use Behat\Behat\Context\Step\Given;
use Behat\Behat\Context\Step\When;
use Behat\Behat\Context\Step\Then;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;

use Behat\Mink\Exception\ElementNotFoundException;

require 'vendor/autoload.php';

/**
 * Features context.
 */
class FeatureContext extends DrupalContext {

  /**
   *Store rss feed xml content
   */
  private $xmlContent = "";

  /**
   * Store project value
   */
  private $project_value = '';

  /**
   * Store the md5 hash of a downloaded file.
   */
  private $md5Hash = '';

  /**
   * Store a post title value
   */
  private $postTitle = '';

  /**
   * Store the file name of a downloaded file
   */
  private $downloadedFileName = '';
  /**
   * Initializes context.
   *
   * Every scenario gets its own context object.
   *
   * @param array $parameters.
   *   Context parameters (set them up through behat.yml or behat.local.yml).
   */
  public function __construct(array $parameters) {
    $this->default_browser = $parameters['default_browser'];
    if (isset($parameters['drupal_users'])) {
      $this->drupal_users = $parameters['drupal_users'];
    }
    if (isset($parameters['environment'])) {
      $this->environment = $parameters['environment'];
    }
  }

  /**
   * @defgroup helper functions
   * @{
   */

  /**
   * Helper function to fetch user passwords stored in behat.local.yml.
   *
   * @param string $type
   *   The user type, e.g. drupal or git.
   *
   * @param string $name
   *   The username to fetch the password for.
   *
   * @return string
   *   The matching password or FALSE on error.
   */
  public function fetchPassword($type, $name) {
    $property_name = $type . '_users';
    try {
      $property = $this->$property_name;
      $password = $property[$name];
      return $password;
    } catch (Exception $e) {
      throw new Exception("Non-existant user/password for $property_name:$name please check behat.local.yml.");
    }
  }



  /**
   * Helper function to fetch previously generated random strings stored by randomString().
   *
   * @param string $name
   *   The name of the random string.
   *
   * @return string
   *   The stored string.
   */
  public function fetchRandomString($name) {
    return HackyDataRegistry::get('random:' . $name);
  }

  /**
   * Helper function to check if the `expect` library is installed.
   */
  public function checkExpectLibraryStatus() {
    $process = new Process('which expect');
    $process->run();
    if (!$process->isSuccessful()) {
      throw new RuntimeException('This feature requires that the `expect` library be installed');
    }
  }

  /**
   * Private function for the whoami step.
   */
  private function whoami() {
    $element = $this->getSession()->getPage();
    // Go to the user page.
    $this->getSession()->visit($this->locatePath('/user'));
    //if ($find = $element->find('css', '.page-title')) {
    if ($find = $element->findById('page-title')) {
      $page_title = $find->getText();
      if ($page_title) {
        return $page_title;
      }
    }
    return FALSE;
  }

  /**
   * Hold the execution until the page is/resource are completely loaded OR timeout
   *
   * @Given /^I wait until the page (?:loads|is loaded)$/
   * @param object $callback
   *   The callback function that needs to be checked repeatedly
   */
  public function iWaitUntilThePageLoads($callback = null) {
    // Manual timeout in seconds
    $timeout = 60;
    // Default callback
    if (empty($callback)) {
      if ($this->getSession()->getDriver() instanceof Behat\Mink\Driver\GoutteDriver) {
          $callback = function($context) {
          // If the page is completely loaded and the footer text is found
          if(200 == $context->getSession()->getDriver()->getStatusCode()) {
            return true;
          }
          return false;
        };
      }
      else {
        // Convert $timeout value into milliseconds
        // document.readyState becomes 'complete' when the page is fully loaded
        $this->getSession()->wait($timeout*1000, "document.readyState == 'complete'");
        return;
      }
    }
    if (!is_callable($callback)) {
      throw new Exception('The given callback is invalid/doesn\'t exist');
    }
    // Try out the callback until $timeout is reached
    for ($i = 0, $limit = $timeout/2; $i < $limit; $i++) {
      if ($callback($this)) {
        return true;
      }
      // Try every 2 seconds
      sleep(2);
    }
    throw new Exception('The request is timed out');
  }
  /**
   * @} End of defgroup "helper functions".
   */

  /**
   * @defgroup mink extensions
   * @{
   * Wrapper step definitions to the Mink extensions in order to implement
   * alternate wording for tests.
   */

  /**
   * @} End of defgroup "mink extensions"
   */


  /**
   * @defgroup "site functions"
   * @{
   *
   */

  /**
   * Authenticates a user.
   *
   * @Given /^I am logged in as "([^"]*)" with the password "([^"]*)"$/
   */
  public function iAmLoggedInAsWithThePassword($username, $passwd) {
    $user = $this->whoami();
    if (strtolower($user) == strtolower($username)) {
      // Already logged in.
      return;
    }

    $element = $this->getSession()->getPage();
    if (empty($element)) {
        throw new Exception('Page not found');
    }
    if ($user != 'User account') {
      // Logout.
      $this->getSession()->visit($this->locatePath('/user/logout'));
    }

    // Go to the user page.
    $this->getSession()->visit($this->locatePath('/user'));
    // Get the page title.
    //$title_element = $this->getSession()->getPage()->find('xpath', '//h2[text()="User account"]');
    $title_element = $this->getSession()->getPage()->findById('page-title');
    if (empty($title_element)) {
        throw new Exception ('No page title found at ' . $this->getSession()->getCurrentUrl());
    }
    $page_title = trim($title_element->getText());

    if ($page_title == 'User account') {
      // If I see this, I'm not logged in at all so log in.
      $element->fillField('Username', $username);
      $element->fillField('Password', $passwd);
      $submit = $element->findButton('Log in');
      if (empty($submit)) {
        throw new Exception('No submit button at ' . $this->getSession()->getCurrentUrl());
      }
      // Log in.
      $submit->click();
      $user = $this->whoami();

      if (strtolower($user) == strtolower($username)) {
        HackyDataRegistry::set('username', $username);
        /*
        $link = $this->getSession()->getPage()->findLink("Your Dashboard");
        // URL format: /user/{uid}/dashboard
        preg_match("/\/user\/(.*)\//", $link->getAttribute('href'), $match);
        if (!empty($match[1])) {
          HackyDataRegistry::set('uid:' . $username, trim($match[1]));
        }
        */
        return;
      }
    }
    else {
      throw new Exception("Failed to reach the login page.");
    }

    throw new Exception('Not logged in.');
  }

  /**
   * Authenticates a user with password from configuration.
   *
   * @Given /^I am logged in as "([^"]*)"$/
   */
  public function iAmLoggedInAs($username) {
    $password = $this->fetchPassword('drupal', $username);
    $this->iAmLoggedInAsWithThePassword($username, $password);
  }


  /**
   * @Given /^I should not see the following <texts>$/
   */
  public function iShouldNotSeeTheFollowingTexts(TableNode $table) {
    $page = $this->getSession()->getPage();
    $table = $table->getHash();
    foreach ($table as $key => $value) {
      $text = $table[$key]['texts'];
      if(!$page->hasContent($text) === FALSE) {
        throw new Exception("The text '" . $text . "' was found");
      }
    }
  }

  /**
   * @Given /^I (?:should |)see the following <texts>$/
   */
  public function iShouldSeeTheFollowingTexts(TableNode $table) {
    $page = $this->getSession()->getPage();
    $table = $table->getHash();
    foreach ($table as $key => $value) {
      $text = $table[$key]['texts'];
      if($page->hasContent($text) === FALSE) {
        throw new Exception("The text '" . $text . "' was not found");
      }
    }
  }

  /**
  * @Given /^I (?:should |)see the following <links>$/
  */
  public function iShouldSeeTheFollowingLinks(TableNode $table) {
    $page = $this->getSession()->getPage();
    $table = $table->getHash();
    foreach ($table as $key => $value) {
      $link = $table[$key]['links'];
      $result = $page->findLink($link);
      if(empty($result)) {
        throw new Exception("The link '" . $link . "' was not found");
      }
    }
  }

  /**
   * @Given /^I should not see the following <links>$/
   */
  public function iShouldNotSeeTheFollowingLinks(TableNode $table) {
    $page = $this->getSession()->getPage();
    $table = $table->getHash();
    foreach ($table as $key => $value) {
      $link = $table[$key]['links'];
      $result = $page->findLink($link);
      if(!empty($result)) {
        throw new Exception("The link '" . $link . "' was found");
      }
    }
  }

/**
   * Find given type in specific region on the page
   *
   * @Then /^I (?:should |)see the "([^"]*)" "([^"]*)" in "([^"]*)" area$/
   *
   * @param string $type
   *   text/link/option/count/tab/power drupal
   * @param string $content
   *   text/link
   * @param string $region
   *   region on homepage
   * @param boolean $find
   *   should see/should not see
   * @param boolean $count_param
   *   count
   */
  public function iShouldSeeInArea($type = 'text', $content, $region, $find = true, $count_param = null) {
    // Find the region
    $region_ele = $this->getSession()->getPage()->find('region', $region);
    if (empty($region_ele)) {
      throw new Exception('The region "' . $region . '" is not found at ' . $this->getSession()->getCurrentUrl() );
    }
    switch ($type) {
      // Normal text(includes link labels as well)
      case 'text':
        if (false === strpos($region_ele->getText(), $content)) {
          if ($find) {
            throw new Exception('The text "' . $content . '" was not found in the "' . $region . '" region of the page');
          }
        }
        else {
          if (!$find) {
            throw new Exception('The text "' . $content . '" was found in the "' . $region . '" region of the page, but it should not be');
          }
        }
        break;
      // Hyperlinks
      case 'link':
        $a_ele = $region_ele->findLink($content);
        if (empty($a_ele)) {
          if ($find) {
            throw new Exception('The link "' . $content . '" was not found in the "' . $region . '" region of the page');
          }
        }
        else {
          // Look for exact match
          $is_exact = ($region_ele->getText() === $content);
          if (!$find && $is_exact) {
            throw new Exception('The link "' . $content . '" was found in the "' . $region . '" region of the page, but it should not be');
          }
        }
        break;
      // Radio buttons.
      case 'option':
        $radio_ele = $region_ele->findAll('xpath', '//input[@type="radio"]');
        if (empty($radio_ele)) {
          throw new Exception('The option "' . $content . '" is not found in the "' . $region . '" region of the page');
        }
        $found = false;
        foreach ($radio_ele as $radio) {
          if ($content == $radio->getParent()->getText()) {
            $found = true;
            if (!$find) {
              throw new Exception('The option "' . $content . '" is found in the "' . $region . '" region of the page but it should not be');
            }
            break;
          }
        }
        if (!$found && $find) {
          throw new Exception('The option "' . $content . '" is not found in the "' . $region . '" region of the page');
        }
        break;
      // Tabs (bottom header/bottom content)
      case 'tab':
        $a_ele = $region_ele->findAll('xpath', '//ul/li/a');
        if (empty($a_ele)) {
          throw new Exception('The tab "' . $content . '" is not found in the "' . $region . '" region of the page');
        }
        $found = false;
        foreach ( $a_ele as $a) {
          if ($content == $a->getText()) {
            $found = true;
            if (!$find) {
              throw new Exception('The tab "' . $content . '" is found in the "' . $region . '" region of the page but it should not be');
            }
            break;
          }
        }
        if (!$found && $find) {
           throw new Exception('The tab "' . $content . '" is not found in the "' . $region . '" region of the page');
        }
        break;
      // Right content count for different links
      case 'count':
        $td_ele = $region_ele->find('xpath', '//table[@class="front-current-activity"]//tr//td//a[text()="' . $content . '"]');
        if (empty($td_ele)) {
          throw new Exception('"' . $content . '" is not found in the "' . $region . '" region of the page');
        }
        $count_ele = $td_ele->getParent()->getParent()->find('css', 'td');
        if(empty($count_ele)) {
          throw new Exception('Count for "' . $content . '" is not found in the "' . $region . '" region of the page');
        }
        $count = (int) str_replace(',','', $count_ele->getText());
        if (trim($count) == "") {
          throw new Exception('"' . $content . '" count is not found');
        }
        if ($count < $count_param) {
          throw new Exception('"' . $content . '" count is less than "' . $count_param . '"');
        }
        break;
      default:
        throw new Exception('The type "' . $type . '" is not implemented.' );
        break;
    }
  }

  /**
   * Checks links in a homepage area
   *
   * @Given /^I should see the following <(?:links|tabs|options)> in "([^"]*)" area$/
   *
   * @param string $region
   *   region on homepage
   * @param object $table
   *   TableNode
   */
  public function iShouldSeeTheFollowingLinksInArea($region, TableNode $table) {
    foreach ($table->getHash() as $content) {
      $keys = array_keys($content);
      $key = str_replace('s', '', $keys[0]);
      $this->iShouldSeeInArea($key, $content[$keys[0]], $region, true);
    }
  }

  /**
   * @} End of defgroup "sitefunctions"
   */


}
