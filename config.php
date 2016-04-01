<?php defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

# Git repo configs
const REPO_LINK = '<repo link>';
const BRANCH_NAME = '<branch name>';

# Remove unnecessary files/folders from project dir on deployed
const CLEAN_FILES = [
    '.gitignore',
    '*.md'
];

const CLEAN_FOLDERS = [
    '.git',
    '.idea'
];

# Configs for CodeIgniter deployer (ci_deploy)
const DB_USER = '<database login>';
const DB_PASSWORD = '<database password>';
const DB_NAME = '<database name>';
