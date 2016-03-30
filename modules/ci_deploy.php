<?php if ( ! defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

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
     * Migrations pat in CodeIgniter
     *
     * @const
     */
    const MIGRATIONS_PATH = PhpDeploy::DOC_ROOT_PATH . '/application/migrations';


    /**
     * Current migration index file
     *
     * @const
     */
    const MIGRATION_INDEX_FILE = 'var/migration_index';


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

        parent::__construct( );

        $this->_load();

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

        ob_start();

        echo parent::do_deploy();

        $cmd = '';
        
        $cmd .= $this->_copy_ci_configs();
        $cmd .= $this->_run_migration_and_update_index();

        echo shell_exec( $cmd );

        return ob_get_clean();

    }


    /**
     * Load files and folders.
     * Create its, if not found.
     *
     * @method _load
     * @access private
     */
    private function _load( )
    {

        // Check migration index file exists
        $this->_create_file_if_not_exists( CI_Deploy::MIGRATION_INDEX_FILE );

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
        
        $cmd = 'cp -Rf ' . CI_Deploy::PRODUCTION_CONFIGS_PATH . '/. ' . CI_Deploy::CI_PRODUCTION_CONFIGS_PATH . ';';
        
        $cmd .= 'find ' . CI_Deploy::CI_PRODUCTION_CONFIGS_PATH . ' -type f | xargs chmod -v 600;';
        
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
        return 'echo NaN;';
    }

}
