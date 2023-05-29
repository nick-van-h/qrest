# What is Qrest?

> ==**Qrest**== ~_noun_~ \[_/krest/_\]
> A privacy-by-design productivity platform that stores to-do's and notes in a secure way

The goal of Qrest is to create a so-called Second Brain.

Qrest is an acronym that encompasses the following definitions;

1. Crest: The identifying emblem worn on a knight's helmet, i.e. it is an extension of the head
2. Quest: A mission to fulfill the need for a privacy focussed productivity platform
3. Rest: Create inner piece by freeing the mind from remembering everything

# Installation

## Add autoload directory to the PATH include

Update php.ini, on linux:
`/etc/php/8.1/apache2/php.ini`
Add the root project folder to _include_path_

## Generate composer autoload

Generate the autoload.php file using following commands:

```
composer update
composer dump-autoload
```

## Restart Apache

Restart the Apache service for the changes to take effect

`sudo service apache2 restart`

## Set up the database

<tbd>
