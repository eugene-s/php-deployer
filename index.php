<?php

# Only POST requests
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 404 );
    exit();
}

define( 'BASEPATH', 'BASEPATH' );

/**
 * Php-Deployer
 * 
 * @name Php-Deployer
 * @description Auto deploy from git by push to special branch
 *
 * @version 0.3beta
 *
 * @author eugene-s
 * @git https://github.com/eugene-s/php-deployer
 *
 * @inspiration https://gist.github.com/oodavid/1809044
 */

/**
 * Copyright 2016  Eugene Savchenko  (GitHub: https://github.com/eugene-s)
 *
 * WooCommerce Wizard is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * WooCommerce Wizard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

# Include config
require_once 'config.php';

# Include modules
require_once 'modules/base.php';
require_once 'modules/deploy.php';
require_once 'modules/ci_deploy.php';
require_once 'modules/github_hook.php';


# Main function
function ExecuteDeploy( ) 
{

    try {

        $deploy = new CI_Deploy( );
        $git_hook = new GitHubHook( );

        // If branch not compared
        if ( ! $git_hook->is_branch( BRANCH_NAME ) ) {
            throw new Exception( 'Deploying only if pushed branch ' . BRANCH_NAME, 203 );
        }

        // If now there is deploying
        if ( $deploy->is_locked( ) ) {
            throw new Exception( $deploy->do_repeat_deploy( ), 202 );
        }

        // Do deploy
        $message = $deploy->do_deploy( );

    } catch ( Exception $exception ) {

        $message = $exception->getMessage( );

        http_response_code( $exception->getCode( ) );

    } finally {

        echo $message;

    }

}


# Execute deploy to server
ExecuteDeploy( );
