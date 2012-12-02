# Advanced Custom Fields Lite

A lite version of the [Advanced Custom Fields](http://advancedcustomfields.com/) WordPress plugin for powerful theme development

-----------------------

### Overview

Welcome to the `lite` version of Advanced Custom Fields. This repository holds the core functionality for ACF without any of the user friendly interfaces for creating fields.

The `lite` version allows you to easily integrate Advanced Custom Fields into your premium / free themes. All code relating to creating / saving / reading field groups from the database has been stripped out leaving you with a light wieght API to register field groups with code.

All the CSS, JS and API functions are the same as the full version. The only real difference is there is no menu for creating field groups.


### Notes

Do not use this `lite` version with an active 'full' version of ACF running. You will recieve a white screen of death (PHP error) because both the `acf plugin` and the 'lite code' use the same classes / functions.

I suggest that you run 2 sites for dev:
1. Blank WP + ACF plugin: for creating field groups and exporting to PHP
2. Real WP + ACF lite: pasting in exported code and developing your theme


### Getting Started

1. Download this repository as a zip file

2. Copy the `acf` folder to your theme

3. View the `functions.php` example file


### Distributing ACF lite in your themes

That's what this is for! Your are 100% allowed to include this `lite` version in your theme. You are also allowed to include your activation codes in the acf_settings hook (see functions.php example).

You are not required to puchase multiple activation codes. Just 1.

At the moment, activation codes are not hidden by any security measures. This may change in the future so please read any notices before "upgrading" or using a "newer" version of the `lite` ACF.