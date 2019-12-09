ToDoList
========

Base du projet #8 : Améliorez un projet existant

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1


Be careful with the line endings., the docker-entrypoint.sh and bin/console need to be in lf and not crlf

---------------------
Steps taken
 - adding migration bundle : composer require doctrine/doctrine-migrations-bundle:1.1
 - Making our initial migrations : bin/console doctrine:migrations:diff
 - Applying our migrations : bin/console doctrine:migrations:migrate
 - Adding Blackfire : need to create a blackfire-variables.env
 - BLACKFIRE_SERVER_ID=xxx
 - BLACKFIRE_SERVER_TOKEN=xxx 
---------------------