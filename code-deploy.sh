#!/bin/bash

ROOT_PATH="/var/www/"
TEMP_DEPLOY_PATH="temp-artifacts"
TEMP_FOLDER="html"
PROJECT_PATH="$ROOT_PATH/$TEMP_FOLDER"
EXCLUDE_DIRS_FILES="$ROOT_PATH/$TEMP_DEPLOY_PATH/$TEMP_FOLDER/exclude-files.txt"

server=`hostname`
echo "--------------------- Deployment starting on the $server ---------------------------"

ls -lsha $ROOT_PATH/$TEMP_DEPLOY_PATH/$TEMP_FOLDER

ls -Ar $ROOT_PATH/$TEMP_DEPLOY_PATH/$TEMP_FOLDER/build | xargs -I {} -P 1 -n 1 rsync -rlpgoDc --exclude-from "$EXCLUDE_DIRS_FILES" "$ROOT_PATH/$TEMP_DEPLOY_PATH/$TEMP_FOLDER/{}" "$PROJECT_PATH" --out-format="%n"

echo "--------------------- Folder cleaning on the $server ---------------------------"
rsync -rlpgoDc --exclude-from "$EXCLUDE_DIRS_FILES" --delete "$ROOT_PATH/$TEMP_DEPLOY_PATH/$TEMP_FOLDER/" "$PROJECT_PATH" --out-format="%n"

echo "--------------------- Removing the Temp artifact from the $server ---------------------------"
rm -rf $ROOT_PATH/$TEMP_DEPLOY_PATH/$TEMP_FOLDER/**
