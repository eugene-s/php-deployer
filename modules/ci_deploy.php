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
    const CI_PRODUCTION_CONFIGS_PATH = parent::DOC_ROOT_PATH . '/application/config/production';


    /**
     * Archive of database dumps path
     * 
     * @const
     */
    const ARCHIVE_DB_DUMPS_PATH = 'var/archive/dumps';


    /**
     * CI_Deploy constructor.
     *
     * @method __construct
     * @access public
     */
    public function __construct( )
    {

        parent::__construct();
        
        $this->_load( );

    }


    /**
     * Do deploy to server
     *
     * @method do_deploy
     * @access public
     *
     * @param array $_commands_: Commands array
     *
     * @return string
     */
    public function do_deploy( array $_commands_ = [] )
    {

        $_commands_ = [ ];

        $_commands_ = array_merge( $_commands_, $this->_copy_ci_configs() );
        $_commands_ = array_merge( $_commands_, $this->_run_migration_and_update_index() );
        $_commands_ = array_merge( $_commands_, $this->_create_and_archive_database_dump( ) );

        return parent::do_deploy( $_commands_ );

    }

    private function _load( )
    {

        // Check production configs path exists
        $this->_create_directory_if_not_exists( self::PRODUCTION_CONFIGS_PATH );
        
        // Check archive dumps path exists
        $this->_create_directory_if_not_exists( self::ARCHIVE_DB_DUMPS_PATH );
        
    }


    /**
     * Copy production configs to CodeIgniter
     *
     * @method _copy_ci_configs
     * @access private
     *
     * @return array of commands
     */
    private function _copy_ci_configs( )
    {
        
        // Copy production configs
        $cmd[] = 'cp -Rf ' . self::PRODUCTION_CONFIGS_PATH . '/. ' . self::CI_PRODUCTION_CONFIGS_PATH;
        
        // Set right chmod for production configs
        $cmd[] = 'find ' . self::CI_PRODUCTION_CONFIGS_PATH . ' -type f | xargs chmod -v 600';
        
        return $cmd;
        
    }


    /**
     * Run migration and update index
     *
     * @method _run_migration_and_update_index
     * @access private
     *
     * @return array of commands
     */
    private function _run_migration_and_update_index( )
    {
        
        // Run migration to current index which is set in CI configs
        $cmd[] = 'CI_ENV="production" /usr/bin/php ' . parent::DOC_ROOT_PATH . '/index.php migrate';

        return $cmd;
        
    }


    /**
     * Create dump of database and archive it
     *
     * @method _create_and_archive_database_dump
     * @access private
     *
     * @return array of commands
     */
    private function _create_and_archive_database_dump( )
    {

        // Create name for new dump file
        $mysqldump_file_name = DB_NAME . '_' . date( 'dmY_Hms' );

        // Get path to new dump file
        $mysqldump_file_path = self::ARCHIVE_DB_DUMPS_PATH . '/' . $mysqldump_file_name . '.sql.gz';

        // Set up credentials to MySQL
        $mysqldump_credentials = '-u' . DB_USER . ' -p' . DB_PASSWORD;

        // Other parameters for 'mysqldump'
        $mysqldump_parameters =
            '--add-drop-database --add-drop-table --add-drop-trigger --create-options --triggers --single-transaction';

        // Create dump of database to file
        $cmd[] =
            'mysqldump ' . $mysqldump_credentials . ' ' . $mysqldump_parameters . ' ' . DB_NAME . 
            ' | gzip > ' . getcwd( ) . '/' . $mysqldump_file_path;

        return $cmd;
        
    }

}
