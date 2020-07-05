#!/bin/bash

# Get to main project directory
cd $(dirname $0)/..

printf "\n\n"
if test ${DATABASE_URL:-not_set} != "not_set"; then {
  printf "\tInitialization failed:\n\n"
  printf "\t\tUnset DATABASE_URL then restart.\n\n"
  exit
} fi
if test ${DATABASE_BRAND:-not_set} == "not_set" || ( test "${DATABASE_BRAND}" != "pgsql" && test "${DATABASE_BRAND}" != "mysql" ) ; then {
  printf "\tInitialization failed:\n\n"
  printf "\t\tDATABASE_BRAND must be set to either mysql or pgsql.\n\n"
  exit
} fi
if test ${CONTAINER_REPO:-not_set} == "not_set" && test ${DATABASE_BRAND} == "pgsql"; then {
    printf "\tInitialization failed:\n\n"
    printf "\t\tCONTAINER_REPO must be set to 'postgres' when DATABASE_BRAND is set to pgsql.\n\n"
    exit
} fi
if test ${CONTAINER_REPO:-not_set} == "not_set" && test ${DATABASE_BRAND} == "mysql"; then {
    printf "\tInitialization failed:\n\n"
    printf "\t\tCONTAINER_REPO must be set to 'percona' when DATABASE_BRAND is set to mysql.\n\n"
    exit
} fi
if test ${DATABASE_DRIVER:-not_set} == "not_set" && test ${DATABASE_BRAND} == "mysql"; then {
    printf "\tInitialization failed:\n\n"
    printf "\t\tDATABASE_DRIVER must be set to 'pdo_mysql' when DATABASE_BRAND is set to mysql.\n\n"
    exit
} fi
if test ${DATABASE_DRIVER:-not_set} == "not_set" && test ${DATABASE_BRAND} == "pgsql"; then {
    printf "\tInitialization failed:\n\n"
    printf "\t\tDATABASE_DRIVER must be set to 'pdo_pgsql' when DATABASE_BRAND is set to pgsql.\n\n"
    exit
} fi
if test ${CONTAINER_REPO} != "percona" && test ${DATABASE_BRAND} == "mysql"; then {
    printf "\tInitialization failed:\n\n"
    printf "\t\tCONTAINER_REPO must be set to 'percona' when DATABASE_BRAND is set to mysql.\n\n"
    exit
} fi
if test ${CONTAINER_REPO} != "postgres" && test ${DATABASE_BRAND} == "pgsql"; then {
    printf "\tInitialization failed:\n\n"
    printf "\t\tCONTAINER_REPO must be set to 'postgres' when DATABASE_BRAND is set to pgsql.\n\n"
    exit
} fi
if test ${DATABASE_DRIVER} != "pdo_pgsql" && test ${DATABASE_BRAND} == "pgsql"; then {
    printf "\tInitialization failed:\n\n"
    printf "\t\tDATABASE_DRIVER must be set to 'pdo_pgsql' when DATABASE_BRAND is set to pgsql.\n\n"
    exit
} fi
if test ${DATABASE_DRIVER} != "pdo_mysql" && test ${DATABASE_BRAND} == "mysql"; then {
    printf "\tInitialization failed:\n\n"
    printf "\t\tDATABASE_DRIVER must be set to 'pdo_mysql' when DATABASE_BRAND is set to mysql.\n\n"
    exit
} fi

printf "\n\n"
printf "\t Configuring project for ${DATABASE_BRAND}\n\n"
rm -f ./docker/docker-compose.yml
printf "\t1. Configuring docker-compose.${DATABASE_BRAND}.yml to docker-compose.yml ... "
ln -sf  docker-compose.${DATABASE_BRAND}.yml docker/docker-compose.yml
rm -f ./config/packages/doctrine.yaml
printf " Done\n"
printf "\t2. Linking doctrine.${DATABASE_BRAND}.yaml ./config/packages/doctrine.yaml ... "
ln -sf doctrine.${DATABASE_BRAND}.yaml ./config/packages/doctrine.yaml 
printf " Done\n"
rm -f docker/.env ./.env
printf "\t3. Configuring environment for ${DATABASE_BRAND} ... "
cp docker/.env.${DATABASE_BRAND}.dist docker/.env
ln -sf docker/.env .env
printf "\n"
for line in `cat ./.env|sed -n '/^DATABASE_/p'`; do
#  printf "\t\t%s\n" `echo ${line}` # |awk -F= '{print $1}'`
  export $line;
done
for line in `cat ./.env|sed -n '/^MYSQL_/p'`; do
#  printf "\t\t%s\n" `echo ${line}` # |awk -F= '{print $1}'`
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

# Replace tokens with random values
#see https://stackoverflow.com/questions/2320564/sed-i-command-for-in-place-editing-to-work-with-both-gnu-sed-and-bsd-osx
printf "\t4. Generating secrets ... "
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
printf " Done\n\n"
# Start docker containers


make up
# Install libraries, create css and js files, and setup database
touch -c composer.lock yarn.lock
make update

# Add an initial super user
./bin/console salt:user:add admin Unknown --password=secret --role=super-user

echo 'You should now be able to connect to http://127.0.0.1:3000'
echo 'Log in with initial user "admin" with password "secret"'
