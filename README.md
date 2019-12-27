<p align="center"><img src="./src/icon.svg" width="100" height="100" alt="Amazon S3 for Craft CMS icon"></p>

<h1 align="center">CloudFront Purge</h1>

A plugin for Craft CMS 3.x to enable CloudFront cache invalidation on entry save.

#

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1.  Open your terminal and go to your Craft project:

        cd /path/to/project

2.  Then tell Composer to load the plugin:

        composer require kayqq/cloud-front-purge

3.  In the Control Panel, go to Settings → Plugins and click the “Install” button for CloudFront Purge.

## CloudFront Purge Overview

A lightweight plugin that creates an invalidation for CloudFront edge caches on entry save.
The invalidation is specific to the entry being saved and preserves all other cached objects.

## Configuring CloudFront Purge

1. Add an IAM access key
2. Add a IAM secret key
3. Add a CloudFront distribution ID
4. Add the region in which the distribution is, example: `us-west-1`.

**Optional**

5. Add a path prefix, applicable if you’re using CloudFront as CDN for your assets and have configured subfolders or custom behaviors.
6. Add a path suffix, applicable if you want to add trailing slash, wild card operator, or other custom behaviors.

Note: IAM role permissions must be configured to allow for invalidation creation for CloudFront.

## Using CloudFront Purge

**Note that Amazon CloudFront charges for invalidation requests. Since invalidation requests can quickly add up when purging individual URLs, you should be aware of the potential costs. Kayqq takes no responsibility whatsoever for expenses incurred.**

> No additional charge for the first 1,000 paths requested for invalidation each month. Thereafter, \$0.005 per path requested for invalidation.
> A path listed in your invalidation request represents the URL (or multiple URLs if the path contains a wildcard character) of the object(s) you want to invalidate from CloudFront cache.

Source: [aws.amazon.com/cloudfront/pricing](https://aws.amazon.com/cloudfront/pricing/)

## CloudFront Purge Roadmap

- Release it

Brought to you by [Kayqq](https://www.github.com/kayqq)
