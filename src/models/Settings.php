<?php

/**
 * CloudFront Purge plugin for Craft CMS 3.x
 *
 * Invalidate the CloudFront cache on entry save
 *
 * @link      https://www.kennyquan.com
 * @copyright Copyright (c) 2019 Kenny Quan
 */

namespace kayqq\cloudfrontpurge\models;

use kayqq\cloudfrontpurge\CloudfrontPurge;

use Craft;
use craft\base\Model;

/**
 * CloudfrontPurge Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Kenny Quan
 * @package   CloudfrontPurge
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string AWS key ID
     */
    public $keyId = '';

    /**
     * @var string AWS key secret
     */
    public $secret = '';

    /**
     * @var string Region to use
     */
    public $region = '';

    /**
     * @var string CloudFront Distribution ID
     */
    public $cfDistributionId = '';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
