<?php if ( ! defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

/**
 * Class PhpDeploy
 * 
 * @class PhpDeploy
 * 
 * @extends BaseDeploy
 */
class PhpDeploy extends BaseDeploy
{

    /**
     * Path with variables
     *
     * @const
     */
    const VAR_PATH = 'var';


    /**
     * Builds path
     *
     * @const
     */
    const BUILDS_PATH = 'builds';


    /**
     * Temporally path of project
     *
     * @const
     */
    const TMP_PATH = 'current';


    /**
     * Project (www) path of server
     *
     * @const
     */
    const DOC_ROOT_PATH = 'www';


    /**
     * Current build index file
     *
     * @const
     */
    const BUILD_INDEX_FILE = 'var/build';


    /**
     * Locker file
     *
     * @const
     */
    const LOCK_FILE = 'var/locked';


    /**
     * Repeat update file
     *
     * @const
     */
    const REPEAT_UPDATE_FILE = 'var/repeat_update';


    /**
     * @var
     * @type int
     *
     * @access protected
     */
    protected $_build_index;


    /**
     * @var
     * @type string
     *
     * @access protected
     */
    protected $_build_folder;


    /**
     * PhpDeploy constructor.
     *
     * @method __construct
     * @access public
     */
    public function __construct( )
    {

        $this->_load( );

        $this->_build_index = (int) $this->_read_file( PhpDeploy::BUILD_INDEX_FILE );
        $this->_build_folder = PhpDeploy::BUILDS_PATH . '/' . $this->_build_index;

    }


    /**
     * Do deploy project
     *
     * @return string
     */
    public function do_deploy( )
    {

        ob_start();

        $cmd = '';
        
        $cmd .= $this->_lock( true ) . '\n';
        $cmd .= $this->_download_latest_project( ) . '\n';
        $cmd .= $this->_create_build_by_current_index( ) . '\n';
        $cmd .= $this->_deploy_current_build( ) . '\n';

        echo shell_exec( $cmd );
        
        if ( $this->_read_file( PhpDeploy::REPEAT_UPDATE_FILE ) === '1' ) {

            $this->_rewrite_file( PhpDeploy::REPEAT_UPDATE_FILE, '0' );
            $this->do_deploy( );
            
        } else {

            $this->_lock( false );

        }

        return ob_get_clean();

    }


    /**
     * Repeat deploy after this deploying
     *
     * @method do_repeat_deploy
     * @access public
     *
     * @return string
     */
    public function do_repeat_deploy( )
    {
        $this->_rewrite_file( PhpDeploy::REPEAT_UPDATE_FILE, '1' );

        return 'print "Server is deploying.\nAfter this, will happen repeat deploy.\n"';
    }


    /**
     * Check on locked deploying
     *
     * @method is_locked
     * @access public
     *
     * @return bool
     */
    public function is_locked( )
    {

        $locked_raw = $this->_read_file( PhpDeploy::LOCK_FILE );
        $rs = false;

        if ( $locked_raw === '1' ) {

            $rs = true;

        }

        return $rs;

    }


    /**
     * Load base configuration and not enough environment
     *
     * @method _load
     * @access private
     */
    private function _load( )
    {
        // Check var folder
        $this->_create_directory_if_not_exists( PhpDeploy::VAR_PATH );

        // Check builds path folder
        $this->_create_directory_if_not_exists( PhpDeploy::BUILDS_PATH );

        // Check tmp path folder
        $this->_create_directory_if_not_exists( PhpDeploy::TMP_PATH );

        // Check project path folder
        $this->_create_directory_if_not_exists( PhpDeploy::DOC_ROOT_PATH );

        // Check file build index exists
        $this->_create_file_if_not_exists( PhpDeploy::BUILD_INDEX_FILE );

        // Check file lock exists
        $this->_create_file_if_not_exists( PhpDeploy::LOCK_FILE );

        // Check file repeat update exists
        $this->_create_file_if_not_exists( PhpDeploy::REPEAT_UPDATE_FILE );
    }


    /**
     * Lock deploying.
     * Duplicate protection.
     *
     * @method _lock
     * @access private
     *
     * @param $_lock_
     *
     * @return string
     */
    private function _lock( $_lock_ )
    {

        if ( $_lock_ === TRUE ) {

            $this->_rewrite_file( PhpDeploy::LOCK_FILE, '1' );

        } else {

            $this->_rewrite_file( PhpDeploy::LOCK_FILE, '0' );

        }

        return 'echo "Current deploy is locked."';

    }


    /**
     * Download latest project to TMP folder
     *
     * @method _download_latest_project
     * @access private
     *
     * @return string
     */
    private function _download_latest_project( )
    {

        $cmd = 'cd ' . PhpDeploy::TMP_PATH . ';';

        if ( ! is_dir(PhpDeploy::TMP_PATH . '/.git') ) {

            $cmd .= 'git clone -b ' . BRANCH_NAME . ' ' . REPO_LINK . ' . ;';


        } else {

            $cmd .= 'git pull origin ' . BRANCH_NAME . ';';

        }

        $cmd .= 'git status;';

        return $cmd;

    }


    /**
     * Create build from current pull
     *
     * @method _create_build_by_current_index
     * @access private
     *
     * @return string
     */
    private function _create_build_by_current_index( )
    {

        // Create folder for the new build
        $this->_create_directory_if_not_exists( $this->_build_folder );

        // Save next build index
        $this->_rewrite_file( PhpDeploy::BUILD_INDEX_FILE, $this->_build_index + 1 );

        $cmd = 'cp -Rf ' . PhpDeploy::TMP_PATH . '/. ' . $this->_build_folder . ';';

        // Remove unnecessary folders
        foreach ( CLEAN_FOLDERS as $folder_name ) {
            $cmd .= "find -type d -find $folder_name -delete;";
        }

        // Remove unnecessary files
        foreach ( CLEAN_FILES as $file_name ) {
            $cmd .= "find -type f -find $file_name -delete;";
        }

        return $cmd;

    }


    /**
     * Deploy current build to server
     *
     * @method _deploy_current_build
     * @access private
     *
     * @return string
     */
    private function _deploy_current_build( )
    {

        $cmd = 'cp -Ru ' . $this->_build_folder . '/. ' . PhpDeploy::DOC_ROOT_PATH;

        return $cmd;

    }

}
