Standards Alignment Tool (SALT)
===============================

[![Latest Stable Version](https://poser.pugx.org/opensalt/opensalt/v/stable)](https://github.com/opensalt/opensalt) [![Build Status](https://travis-ci.org/opensalt/opensalt.svg?branch=develop)](https://travis-ci.org/opensalt/opensalt) [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=opensalt_opensalt&metric=alert_status)](https://sonarcloud.io/dashboard?id=opensalt_opensalt) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/opensalt/opensalt/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/opensalt/opensalt/?branch=develop) <!--- comment out until Insight supports PHP 7.4 [![SensioLabsInsight](https://insight.sensiolabs.com/projects/e4aee568-15d9-4d97-944f-fb742bb9e885/mini.png)](https://insight.sensiolabs.com/projects/e4aee568-15d9-4d97-944f-fb742bb9e885) -->


Overview
--------

This is a prototype for testing the IMS Global Learning Consortium® [CASE™ Specification](https://www.imsglobal.org/case) and proving its use
in real-world scenarios based on various proof of concept and pilot projects.

The code is intended to run using a set of docker containers using
docker-compose so that it can be easily deployed in any Linux environment
with docker installed.

RDBMS Support

-------------
Originally, the OpenSalt prototype was designed to use Oracle's MySQL RDBMS.  Postgres RDBMS support was introduced in Q2 of 2020 by

* A Postgres 12.3 Dock container was introduced as an alternative to the MySQL Percona image
* The default OpenSalt PHP FPM container was updated to support either MySQL or Postgres RDBMS
* OpenSalt Doctrine database connection configuration was updated to read configuration from environment variables making it simple to switch from MySQL to Postgres at the time containers are started
* OpenSalt shell scripts were updated to configure the build environment to support both RDBMS options
* The MySQL schema developed over more than two years by the OpenSalt team was converted to Postgres SQL using [PgLoader|https://pgloader.io/].  The generated schema was adjusted manually to accomodate Postgres specific initialization of JSON fields.  The Postgres schema is loaded into the Postgres 12.3 Docker container the first time the container is started
* OpenSalt Doctrine DateTime and JSON types were updated to accomodate use of Postgres date and time formats and Postgres JSONB storage
* OpenSalt Doctrine Migration versions from February 2016 through April 15th 2020 (93 migrations) were updated so the Symfony Doctrine system skips those migrations that are repalced by the [PgLoader|https://pgloader.io/] generated schema.  During implementation, as the OpenSalt team upgraded development to Doctrine Migrations 3.1, a work-around to a defect introduced in Doctrine Migration 3.0 was introduced to one migration
* OpenSalt acceptance tests were executed to ensure consistency of implementation with the OpenSalt prototype when run against the original MySQL RDBMS.  As not all of the tests passed consistently against the OpenSalt prototype, some test scripts were updated to address intermittent failures
* This README has been updated both to describe the changes required to support Postgres and to reflect changes to the installation procedures for local development introduced to make it easy for developers to use either MySQL or Postgres RDBMS

Installation
------------

While all steps required to install and start OpenSalt are described below, once Docker is installed, unattended installation may be accessed from a command line (CLI) by executing `./local-dev/initial_dev_install.sh`.

This script will ask which RDBMS should be supported, configure environment variables accordingly, build and start Docker containers, install required PHP and Node.JS modules, install the database schema and create an administrative user account.

Once the application is running, an initial organization may be created using the web-based user interface or by CLI by using the script `./bin/console salt:org:add [organization name]`.  Similarly, user accounts may be created using the web-based user-interface or by CLI using the script `./bin/console salt:user:add [username] [--password="secret"] [--role="rolename"]`

For continuous integration purposes, the script `./bin/build.sh` functions similarly to `local-dev/initial_dev_install.sh` without prompting for an RDBMS selection (RDBMS is assumed based on the currently active Docker environment configuration [docker/.env] and running containers).

Installation Details
--------------------

1. Install Docker from [here](https://www.docker.com/products/docker), Docker Compose from [here](https://docs.docker.com/compose/install/).  If you are working on a Mac, review [Docker for Mac](./docs/DOCKER_FOR_MAC.md).

2. Create an .env file and docker-compose configuration

  ```Bash
  cp docker/.env.dist docker/.env
  ln -s docker/.env .env
  ln -s docker-compose.dev.yml docker/docker-compose.yml
  ```

3. Extend the env file with additional RDBMS configuration parameters

  ```Bash
  # When using MySQL,

  cat <<m.y.s.q.l >>docker/.env

  # [ Container ]
  CONTAINER_REPO=percona

  # [ Database Configuration ]
  DATABASE_BRAND=mysql
  DATABASE_CHARSET=utf8mb4
  DATABASE_COLLATE=utf8mb4_unicode_ci
  DATABASE_DRIVER=pdo_mysql
  DATABASE_FILTER=~^(?!(cache_items|LearningStandards|std_*|map_*|grade_level))~
  DATABASE_HOST=db
  DATABASE_NAME=cftf
  DATABASE_PASSWORD=cftf
  DATABASE_PORT=3306
  DATABASE_USER=cftf
  DATABASE_VERSION=5.7
  DB_USE_RDS_CERT=0
  m.y.s.q.l

  # When using Postgres,

  cat <<p.g.s.q.l >>docker/.env

  # [ Container ]
  CONTAINER_REPO=postgres

  # [ Database Configuration ]
  DATABASE_BRAND=pgsql
  DATABASE_CHARSET=UTF8
  DATABASE_COLLATE=ucs_basic
  DATABASE_DRIVER=pdo_pgsql
  DATABASE_FILTER=[^\w]
  DATABASE_HOST=db
  DATABASE_NAME=cftf
  DATABASE_PASSWORD=cftf
  DATABASE_PORT=5432
  DATABASE_USER=cftf
  DATABASE_VERSION=12.3
  p.g.s.q.l
  ```

4. Edit docker/.env and set any desired non-standard values

   * The `PORT` specified is what is used in step 7 below

5. Start the application

  ```Bash
  make up

  # Stop the application
  make down

  # Install libraries with composer or yarn and re-build the application
  make force-build
  # Linux users note
  #
  #  The user that interacts with the Docker servuce must be added to the 'docker' group
  #
  #  The MySQL folder permissions should be set appropriately:
  #
  #    chmod -R 777 docker/data/mysql
  #
  # If using Postgres, the Postgres folder permissions should be set appropriately:
  #
  #    chmod -R 777 var/lib/postgresql
  #
  # The Symfony cache directory permssions should be set appropriately:
  #
  #    chmod 777 var/cache`
  #
  # Run database migrations
  make migrate
  ```

6. [http://127.0.0.1:3000/app_dev.php/](http://127.0.0.1:3000/app_dev.php/) should show the initial screen with the debugging display visible.  The port shown here must match the value of `PORT` in the `.env` file (default being 3000)

7. If you have run these tasks manually, create an OpenSalt administrative account and password for the system:

    ```Bash
    ./bin/console salt:user:add admin Unknown --password=secret --role=super-user
    ```

Other Docs
----------

* [User Management Commands](./docs/Commands.md)
* [Github Authentication Config](./docs/deployment/GithubAuth.md)
