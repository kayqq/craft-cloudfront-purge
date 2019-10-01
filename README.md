# CloudFront Purge plugin for Craft CMS 3.x

Invalidate the CloudFront cache on entry save

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require kayqq/cloud-front-purge

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for CloudFront Purge.

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

* Release it

Brought to you by [Kenny Quan](https://www.kennyquan.com)
