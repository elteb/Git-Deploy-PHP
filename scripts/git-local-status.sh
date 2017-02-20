#!/bin/bash


# SCRIPT
# ------------------------------------------

APPLICATION_PATH="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && cd .. && pwd )"
cd "$APPLICATION_PATH"

git status
