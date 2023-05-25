#!/bin/bash
#exit on error
set -e
#exit on unset variable
set -u
#extend globbing
shopt -s extglob

docker compose build --no-cache
docker compose up --build 
