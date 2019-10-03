<?php

/**
 * CloudFront Purge plugin for Craft CMS 3.x
 *
 * Invalidate the CloudFront cache on entry save
 *
 * @link      https://www.kennyquan.com
 * @copyright Copyright (c) 2019 Kenny Quan
 */

namespace kayqq\cloudfrontpurge;

use kayqq\cloudfrontpurge\models\Settings;

use Aws\CloudFront\CloudFrontClient;
use Aws\CloudFront\Exception\CloudFrontException;
use Aws\Credentials\Credentials;
use Aws\Handler\GuzzleV6\GuzzleHandler;
use Aws\Sts\StsClient;

use Craft;
use craft\base\Plugin;
use craft\events\ElementEvent;
use craft\services\Elements;
use craft\elements\Entry;
use craft\helpers\StringHelper;
use craft\behaviors\EnvAttributeParserBehavior;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Kenny Quan
 * @package   CloudFrontPurge
 * @since     1.0.0
 *
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class CloudFrontPurge extends Plugin
{
  // Static Properties
  // =========================================================================

  const CACHE_KEY_PREFIX = 'aws.';
  const CACHE_DURATION_SECONDS = 3600;

  // Static Properties
  // =========================================================================

  /**
   * Static property that is an instance of this plugin class so that it can be accessed via
   * CloudFrontPurge::$plugin
   *
   * @var CloudFrontPurge
   */
  public static $plugin;

  // Public Properties
  // =========================================================================

  /**
   * To execute your plugin’s migrations, you’ll need to increase its schema version.
   *
   * @var string
   */
  public $schemaVersion = '1.0.1';
  public $invalidationSent = false;

  // Public Methods
  // =========================================================================

  /**
   * Set our $plugin static property to this class so that it can be accessed via
   * CloudFrontPurge::$plugin
   *
   * Called after the plugin class is instantiated; do any one-time initialization
   * here such as hooks and events.
   *
   * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
   * you do not need to load it in your init() method.
   *
   */
  public function init()
  {
    parent::init();
    self::$plugin = $this;

    Event::on(
      Elements::class,
      Elements::EVENT_AFTER_SAVE_ELEMENT,
      function (ElementEvent $event) {
        if ($event->element instanceof Entry) {
          $entry = $event->element;
          $this->invalidateCdnPath($entry->uri);
        }
      }
    );

    /**
     * Logging in Craft involves using one of the following methods:
     *
     * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
     * Craft::info(): record a message that conveys some useful information.
     * Craft::warning(): record a warning message that indicates something unexpected has happened.
     * Craft::error(): record a fatal error that should be investigated as soon as possible.
     *
     * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
     *
     * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
     * the category to the method (prefixed with the fully qualified class name) where the constant appears.
     *
     * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
     * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
     *
     * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
     */
    Craft::info(
      Craft::t(
        'cloud-front-purge',
        '{name} plugin loaded',
        ['name' => $this->name]
      ),
      __METHOD__
    );
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    $behaviors = parent::behaviors();
    $behaviors['parser'] = [
      'class' => EnvAttributeParserBehavior::class,
      'attributes' => [
        'keyId',
        'secret',
        'region',
        'cfDistributionId',
      ],
    ];
    return $behaviors;
  }

  /**
   * Build the config array based on a keyID and secret
   *
   * @param string|null $keyId The key ID
   * @param string|null $secret The key secret
   * @param string|null $region The region to user
   * @param bool $refreshToken If true will always refresh token
   * @return array
   */
  public static function buildConfigArray($keyId = null, $secret = null, $region = null, $refreshToken = false): array
  {
    $config = [
      'region' => $region,
      'version' => 'latest'
    ];

    $client = Craft::createGuzzleClient();
    $config['http_handler'] = new GuzzleHandler($client);

    if (empty($keyId) || empty($secret)) {
      // Assume we're running on EC2 and we have an IAM role assigned. Kick back and relax.
    } else {
      $tokenKey = static::CACHE_KEY_PREFIX . md5($keyId . $secret);
      $credentials = new Credentials($keyId, $secret);

      if (Craft::$app->cache->exists($tokenKey) && !$refreshToken) {
        $cached = Craft::$app->cache->get($tokenKey);
        $credentials->unserialize($cached);
      } else {
        $config['credentials'] = $credentials;
        $stsClient = new StsClient($config);
        $result = $stsClient->getSessionToken(['DurationSeconds' => static::CACHE_DURATION_SECONDS]);
        $credentials = $stsClient->createCredentials($result);
        $cacheDuration = $credentials->getExpiration() - time();
        $cacheDuration = $cacheDuration > 0 ?: static::CACHE_DURATION_SECONDS;
        Craft::$app->cache->set($tokenKey, $credentials->serialize(), $cacheDuration);
      }

      // TODO Add support for different credential supply methods
      $config['credentials'] = $credentials;
    }

    return $config;
  }

  // Protected Methods
  // =========================================================================

  /**
   * @inheritdoc
   */
  protected function invalidateCdnPath(string $path): bool
  {
    $settings = $this->getSettings();
    if (!empty($settings->cfDistributionId) && !$this->invalidationSent) {
      // If CloudFront distribution ID set and invalidation has not been sent, invalidate the path.
      $cfClient = $this->_getCloudFrontClient();

      try {
        $cfClient->createInvalidation(
          [
            'DistributionId' => Craft::parseEnv($settings->cfDistributionId),
            'InvalidationBatch' => [
              'Paths' =>
              [
                'Quantity' => 1,
                'Items' => ['/' . ltrim($path, '/')]
              ],
              'CallerReference' => 'Craft-' . StringHelper::randomString(24)
            ]
          ]
        );
        // Flag invalidation as sent to prevent duplicate invalidations from 'on save' event firing per site.
        $this->invalidationSent = true;
      } catch (CloudFrontException $exception) {
        // Log the warning, most likely due to 404. Allow the operation to continue, though.
        Craft::warning($exception->getMessage());
      }
    }

    return true;
  }

  /**
   * Creates and returns the model used to store the plugin’s settings.
   *
   * @return \craft\base\Model|null
   */
  protected function createSettingsModel()
  {
    return new Settings();
  }

  /**
   * Returns the rendered settings HTML, which will be inserted into the content
   * block on the settings page.
   *
   * @return string The rendered settings HTML
   */
  protected function settingsHtml(): string
  {
    return Craft::$app->view->renderTemplate(
      'cloud-front-purge/settings',
      [
        'settings' => $this->getSettings()
      ]
    );
  }

  // Private Methods
  // =========================================================================

  /**
   * Get a CloudFront client.
   *
   * @return CloudFrontClient
   */
  private function _getCloudFrontClient()
  {
    return new CloudFrontClient($this->_getConfigArray());
  }

  /**
   * Get the config array for AWS Clients.
   *
   * @return array
   */
  private function _getConfigArray()
  {
    $credentials = $this->_getCredentials();

    return self::buildConfigArray($credentials['keyId'], $credentials['secret'], $credentials['region']);
  }

  /**
   * Return the credentials as an array
   *
   * @return array
   */
  private function _getCredentials()
  {
    $settings = $this->getSettings();
    return [
      'keyId' => Craft::parseEnv($settings->keyId),
      'secret' => Craft::parseEnv($settings->secret),
      'region' => Craft::parseEnv($settings->region),
    ];
  }
}
