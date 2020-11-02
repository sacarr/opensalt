#!/bin/bash

# Get to main project directory
cd $(dirname $0)/..

printf "\n\n"
while test ${ready:-false} != true; do
  while test ${DATABASE_BRAND:-not_set} != "mysql" && test ${DATABASE_BRAND:-not_set} != "postgres"; do
    if test ${DATABASE_BRAND:-not_set} != "not_set" && ( test ${DATABASE_BRAND} != "mysql" && test ${DATABASE_BRAND} != "postgres" ); then {
      printf "\n\t${DATABASE_BRAND} is not supported\n"
    } fi
    read -e -p "Select a database platform [mysql, postgres (default postgres)]: " DATABASE_BRAND
    DATABASE_BRAND=`echo ${DATABASE_BRAND} | tr '[:upper:]' '[:lower:]'`
    if test `echo $DATABASE_BRAND|wc -c` -eq 1; then
      DATABASE_BRAND="postgres"
    fi
  done
  printf "\n\n"
  printf "\tConfiguring project for ${DATABASE_BRAND}\n\n"

  printf "\t1. Configuring build for ${DATABASE_BRAND} ... "
  rm -f ./docker/docker-compose.yml
  ln -sf  docker-compose.dev.yml docker/docker-compose.yml
  rm -f docker/.env ./.env
  cp docker/.env.dist docker/.env && ln -sf docker/.env .env

  if test ${DATABASE_BRAND} == "postgres"; then {
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
  } else {
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
  } fi
  printf " Done.\n\n"

  # Replace tokens with random values
  #see https://stackoverflow.com/questions/2320564/sed-i-command-for-in-place-editing-to-work-with-both-gnu-sed-and-bsd-osx
  printf "\t3. Generating secrets ... "
  if sed --version >/dev/null 2>&1; then
    #GNU sed (common to linux)
    TOKEN=$(openssl rand -base64 33)
    sed -i "s#ThisTokenIsNotSoSecretSoChangeIt#${TOKEN}#" docker/.env

    TOKEN=$(openssl rand -base64 33)
    sed -i "s#ThisTokenIsNotSoSecretChangeIt#${TOKEN}#" docker/.env
  else
    #BSD sed (common to osX)
    TOKEN=$(openssl rand -base64 33)
    sed -i '' "s#ThisTokenIsNotSoSecretSoChangeIt#${TOKEN}#" docker/.env

    TOKEN=$(openssl rand -base64 33)
    sed -i '' "s#ThisTokenIsNotSoSecretChangeIt#${TOKEN}#" docker/.env
  fi
  printf " Done.\n\n"

  for line in `cat ./.env|sed -n '/^DATABASE_/p'`; do
    export $line;
  done
  for line in `cat ./.env|sed -n '/^MYSQL_/p'`; do
    export $line;
  done
  for setting in `env |sed -n '/^DATABASE_/p'|sort`; do
    printf "\t\t%s\n" $setting
  done
  for setting in `env |sed -n '/^MYSQL_/p'|sort`; do
    printf "\t\t%s\n" $setting
  done
  for setting in `env |sed -n '/^POSTGRES_/p'|sort`; do
    printf "\t\t%s\n" $setting
  done
  printf "\t  Done\n"

   read -e -p "Select a database platform [mysql, postgres (default postgres)]: " DATABASE_BRAND
    DATABASE_BRAND=`echo ${DATABASE_BRAND} | tr '[:upper:]' '[:lower:]'`
    if test `echo $DATABASE_BRAND|wc -c` -eq 1; then
      DATABASE_BRAND="postgres"
    fi
  read -e, -p "Ready to build? [yes, no] (default yes): " ready
  ready=`echo ${ready} | tr '[:upper:]' '[:lower:]'| head -c 1`
  if test `echo ${ready}|wc -c` -eq 1; then
    ready=true
  elif test ${ready} == "y"; then
    ready=true
  else
    ready=false
  fi
done

# Start docker containers
printf "\n\n\t4. Starting containers\n\n"
make up && printf " Done\n\n"

# Install libraries, create css and js files, and setup database
printf "\t5. Install PHP modules, Node.JS modules and Database schema\n\n"
touch -c composer.lock yarn.lock
make update
printf " Done\n\n"

# Add an initial super user
printf "\t6. Adding an admin user\n\n"
./bin/console salt:user:add admin Unknown --password=secret --role=super-user
printf " Done\n\n"

printf "\n\n\tYou should now be able to connect to http://127.0.0.1:3000\n\n"
printf "\tLog in with initial user 'admin' with password 'secret'\n"
printf " Done\n\n"
