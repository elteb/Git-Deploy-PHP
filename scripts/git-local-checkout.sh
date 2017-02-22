#!/bin/bash


# SCRIPT
# ------------------------------------------

APPLICATION_PATH="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && cd .. && pwd )"
cd "$APPLICATION_PATH"


# Git deploy

unset GIT_DIR
git checkout $1