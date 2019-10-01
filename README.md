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

1. Add an access key id
2. Add a secret key
3. Add a distribution id
4. Fill out the region in which the distribution is

Note: IAM role permissions must be configured to allow for invalidation creation for CloudFront.

## Using CloudFront Purge

Save an entry and watch the magic happen.

## CloudFront Purge Roadmap

Some things to do, and ideas for potential features:

- Release it

Brought to you by [Kenny Quan](https://www.kennyquan.com)
