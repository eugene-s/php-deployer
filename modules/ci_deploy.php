<?php defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

/**
 * Class CI_Deploy
 *
 * Deploying project with special configuration for CodeIgniter
 *
 * @class CI_Deploy
 *
 * @extends PhpDeploy
 */
final class CI_Deploy extends PhpDeploy
{

    /**
     * Configs path, which will be copied to CI
     *
     * @const
     */
    const PRODUCTION_CONFIGS_PATH = 'configs';


    /**
     * CodeIgniter production configs path
     *
     * @const
     */
    const CI_PRODUCTION_CONFIGS_PATH = PhpDeploy::DOC_ROOT_PATH . '/application/config/production';


    /**
     * CI_Deploy constructor.
     *
     * @method __construct
     * @access public
     */
    public function __construct( )
    {

        parent::__construct();

    }


    /**
     * Do deploy to server
     *
     * @method do_deploy
     * @access public
     *
     * @return string
     */
    public function do_deploy()
    {
        $rs = parent::do_deploy();

        $commands = [];

        $commands = array_merge( $commands, $this->_copy_ci_configs() );
        $commands = array_merge( $commands, $this->_run_migration_and_update_index() );

        foreach ( $commands as $cmd ) {
            $tmp = shell_exec( $cmd );

            $rs .= "$ $cmd\n";
            $rs .= htmlentities(trim($tmp)) . "\n";
        }

        return $rs;

    }


    /**
     * Copy production configs to CodeIgniter
     *
     * @method _copy_ci_configs
     * @access private
     *
     * @return string
     */
    private function _copy_ci_configs( )
    {
        
        $cmd[] = 'cp -Rf ' . CI_Deploy::PRODUCTION_CONFIGS_PATH . '/. ' . CI_Deploy::CI_PRODUCTION_CONFIGS_PATH;
        
        $cmd[] = 'find ' . CI_Deploy::CI_PRODUCTION_CONFIGS_PATH . ' -type f | xargs chmod -v 600';
        
        return $cmd;
        
    }


    /**
     * Run migration and update index
     *
     * @method _run_migration_and_update_index
     * @access private
     *
     * @return string
     */
    private function _run_migration_and_update_index( )
    {
        $cmd[] = 'CI_ENV="production" /usr/bin/php ' . PhpDeploy::DOC_ROOT_PATH . '/index.php migrate';

        return $cmd;
    }

}
