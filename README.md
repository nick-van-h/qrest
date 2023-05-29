# What is Qrest?

> ### **Qrest** <sub>_noun_</sub>
>
> \[_/krest/_\]
>
> A privacy-by-design productivity platform that stores to-do's and notes in a secure way

The goal of Qrest is to create a so-called Second Brain.

Qrest is an acronym that encompasses the following definitions;

1. Crest: The identifying emblem worn on a knight's helmet, i.e. it is an extension of the head
2. Quest: A mission to fulfill the need for a privacy focussed productivity platform
3. Rest: Create inner piece by freeing the mind from remembering everything

# Installation

## Clone the project

Set up a directory and clone the project `git clone https://github.com/nick-van-h/qrest.git <target-directory>`

Make sure the _public_ folder is accessible via the browser.

## Add autoload directory to the PATH include

Update php.ini and add the root project folder to the _include_path_
On linux php.ini is located in _/etc/php/8.1/apache2/_

Restart the Apache service for the changes to take effect, on linux:
`sudo service apache2 restart`

## Generate composer autoload

Generate _autoload.php_ via composer using following commands:

```
composer update
composer dump-autoload
```

## Set up the database

Create _config/db.ini_ as per the template _\_db.ini_ with the settings of your database setup.

First launch will redirect to _[Qrest base URI]/admin_ and update the table definitions to the latest version.

# Upgrading

## Updating the database

First launch after updating will redirect to _[Qrest base URI]/admin_, update the table definitions to the latest version.
