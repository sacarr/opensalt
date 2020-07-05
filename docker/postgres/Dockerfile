FROM postgres:12.3-alpine

# Set the locale
#ENV LANG en_US.UTF-8  
#ENV LANGUAGE en_US:en  
#ENV LC_ALL en_US.UTF-8

RUN rm -rf /docker-entrypoint-initdb.d/*
COPY init.sql /docker-entrypoint-initdb.d/init.sql

#ENV POSTGRES_USER postgres
#ENV POSTGRES_PASSWORD Magpie-Whip-Zephyr-Ghost-81
#ENV POSTGRES_DB postgres