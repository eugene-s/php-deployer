<?php defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

# Git repo configs
const REPO_LINK = 'git@github.com:InStandart/360dpi.git';
const BRANCH_NAME = '2click.remotehost.tk';

# Remove unnecessary files/folders from project dir on deployed
const CLEAN_FILES = [
    '.gitignore',
    '*.md'
];

const CLEAN_FOLDERS = [
    '.git',
    '.idea'
];
