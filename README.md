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

1. Install Docker from [here](https://www.docker.com/products/docker)
   and Docker Compose from [here](https://docs.docker.com/compose/install/)
  - [Docker for Mac notes](./docs/DOCKER_FOR_MAC.md)

  > **Note: the rest of the following can be automated by running `./local-dev/initial_dev_install.sh`**.  The script will ask the user which RDBMS should be supported, and configure Docker environment files accordingly

  > Once the application is running:
  > To create an organization use `./bin/console salt:org:add [organization name]`
  > To create a user use `./bin/console salt:user:add [username] [--password="secret"] [--role="rolename"]`
  > > The *initial_dev_install.sh* command creates an initial super admin "admin" with password "secret"

  > `./bin/build.sh` also does much of the following, for doing a "build" after one has started development

2. Create env file and docker-compose file

  ```Bash
  cp docker/.env.dist docker/.env
  ln -s docker/.env .env
  ln -s docker-compose.dev.yml docker/docker-compose.yml
  ```

When using MySQL,

```Bash
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
```

When using Postgres,

```Bash
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

3. Edit docker/.env and set desired values

  - The `PORT` specified is what is used in step 7 below

4. Start the application
  ```
  make up
  ```
    * To stop the application

    ```
    make down
    ```

5. Install libraries with composer/yarn and build application
  ```
  make force-build
  ```
  * Linux users should note that a new user group, `docker`, has been created. The user that will interact with the Docker service will need to be in this group.
  * Linux users also set the MySQL folder permissions: `chmod -R 777 docker/data/mysql`
  * Linux users should set the cache directory permssions: `chmod 777 var/cache`


6. Run database migrations
  ```
  make migrate
  ```

7. [http://127.0.0.1:3000/app_dev.php/](http://127.0.0.1:3000/app_dev.php/) should show the initial screen with debug turned on
  - Note that the port here should be the value of `PORT` in the `.env` file (default being 3000)

8. If you have run these manual tasks, you will also need to create the administrative account and password for the system:
    ```
    ./bin/console salt:user:add admin Unknown --password=secret --role=super-user
    ```


Other Docs
----------

- [User Management Commands](./docs/Commands.md)
- [Github Authentication Config](./docs/deployment/GithubAuth.md)
