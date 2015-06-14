VEWA - Web
=============

> VEWA - A web enabled wishlist application built with the help of Symfony2 PHP Framework

        Everyone is permitted to copy and distribute verbatim copies
        of this license document, but changing it is not allowed.

## Install

## Usage

## Development

### Requirements
To run this application on your machine, you need at least:

* PHP >=5.5
* MySQL >= 5.5
* Apache Web Server with mod rewrite enabled or Nginx Web Server

### Install dependencies
You can clone the repository and then install dependencies using make:

    $ composer install

### Database
You'll need to create the database and initialize schema:

    echo 'CREATE DATABASE vewa CHARSET=utf8 COLLATE=utf8_unicode_ci' | mysql -u root
    cat schema/vewa.sql | mysql -u root vewa

## License
see LICENSE file for details...
