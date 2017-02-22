#!/bin/bash


# SCRIPT
# ------------------------------------------

APPLICATION_PATH="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && cd .. && pwd )"
cd "$APPLICATION_PATH"


# Git deploy

unset GIT_DIR
git fetch origin
git checkout -b $1 origin/$1
