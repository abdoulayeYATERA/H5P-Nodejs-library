#!/bin/bash
#exit on error
set -e
#exit on unset variable
set -u
#extend globbing
shopt -s extglob

if [ -d ./build ]; then
    rm -r ./build
fi
npm install
npm run build
docker-compose build --no-cache
docker-compose up --build
