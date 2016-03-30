<?php if ( ! defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

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
