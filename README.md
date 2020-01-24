ToDoList
========

_Version 1.1.3_

[![Build Status](https://travis-ci.com/Starbugstone/todoandco.svg?branch=master)](https://travis-ci.com/Starbugstone/todoandco)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Starbugstone/todoandco/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Starbugstone/todoandco/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Starbugstone/todoandco/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Starbugstone/todoandco/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Starbugstone/todoandco/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Starbugstone/todoandco/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/Starbugstone/todoandco/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

Base du projet #8 : Améliorez un projet existant

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1

## Comment contribuer
Voir [ici](./Contributing.md)

## Contributors
Voir [ici](./Contributors.md)

## Useful information
Be careful with the line endings., the docker-entrypoint.sh and bin/console need to be in lf and not crlf

---------------------
update process
 - adding migration bundle : composer require doctrine/doctrine-migrations-bundle:1.1
 - Making our initial migrations : bin/console doctrine:migrations:diff
 - Applying our migrations : bin/console doctrine:migrations:migrate
 - Adding Blackfire : need to create a blackfire-variables.env
   - BLACKFIRE_SERVER_ID=xxx
   - BLACKFIRE_SERVER_TOKEN=xxx
 - Adding fixtures and test utilities
 - making the tasks attached to a user
 - Adding user roles to the edit form
 - Allowing only the self user and the admin modify profile
 - allow the update of a profile without resetting password
 - personalised error pages
---------------------

docker-compose exec php bin/console doctrine:fixtures:load -n

docker-compose exec php bin/phpunit --coverage-html=coverage/
