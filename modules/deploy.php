<?php defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

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
     * @type string
     */
    const VAR_PATH = 'var';


    /**
     * Builds path
     *
     * @const
     * @type string
     */
    const BUILDS_PATH = 'builds';


    /**
     * Archive of old builds
     *
     * @const
     * @type string
     */
    const ARCHIVE_BUILDS_PATH = 'var/archive/builds';


    /**
     * Temporally path of project
     *
     * @const
     * @type string
     */
    const TMP_PATH = 'current';


    /**
     * Project (www) path of server
     *
     * @const
     * @type string
     */
    const DOC_ROOT_PATH = 'www';


    /**
     * Dir for uploading files
     * 
     * @const
     * @type string
     */
    const UPLOAD_FILES_PATH = 'files';


    /**
     * Current build index file
     *
     * @const
     * @type string
     */
    const BUILD_INDEX_FILE = 'var/build';


    /**
     * Locker file
     *
     * @const
     * @type string
     */
    const LOCK_FILE = 'var/locked';


    /**
     * Repeat update file
     *
     * @const
     * @type string
     */
    const REPEAT_UPDATE_FILE = 'var/repeat_update';


    /**
     * Archive build, if it older than builds index at current index
     *
     * @const
     * @type int
     */
    const ARCHIVE_BUILDS_OLDER_THEN_INDEX = 5;


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

        $this->_build_index = (int) $this->_read_file( self::BUILD_INDEX_FILE );
        $this->_build_folder = self::BUILDS_PATH . '/' . $this->_build_index;

    }


    /**
     * Do deploy project
     *
     * @param array $_commands_
     *
     * @return string
     */
    public function do_deploy( array $_commands_ = null )
    {

        // Save next build index
        $this->_rewrite_file( self::BUILD_INDEX_FILE, $this->_build_index + 1 );

        $rs = '';

        $this->_lock( true );

        $commands = [ 'echo $PWD' ];

        $commands = array_merge( $commands, $this->_download_latest_project( ) );
        $commands = array_merge( $commands, $this->_create_build_by_current_index( ) );
        $commands = array_merge( $commands, $this->_deploy_current_build( ) );
        $commands = array_merge( $commands, $this->_archive_old_builds( ) );

        if ( ! empty( $_commands_ ) ) {
            $commands = array_merge( $commands, $_commands_ );
        }

        foreach ( $commands as $cmd ) {
            $tmp = shell_exec( $cmd );

            $rs .= "$ $cmd\n";
            $rs .= htmlentities(trim($tmp)) . "\n";
        }
        
        if ( $this->_read_file( self::REPEAT_UPDATE_FILE ) === '1' ) {

            $this->_rewrite_file( self::REPEAT_UPDATE_FILE, '0' );
            $this->do_deploy( );
            
        } else {

            $this->_lock( false );

        }

        return $rs;

    }


    /**
     * Repeat deploy after this deploying
     *
     * @method do_repeat_deploy
     * @access public
     *
     * @return array of commands
     */
    public function do_repeat_deploy( )
    {
        $this->_rewrite_file( self::REPEAT_UPDATE_FILE, '1' );

        $cmd[] = 'print "Server is deploying.\nAfter this, will happen repeat deploy.\n"';
        
        return $cmd;
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

        $locked_raw = $this->_read_file( self::LOCK_FILE );
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
        $this->_create_directory_if_not_exists( self::VAR_PATH );

        // Check builds path folder
        $this->_create_directory_if_not_exists( self::BUILDS_PATH );

        // Check tmp path folder
        $this->_create_directory_if_not_exists( self::TMP_PATH );

        // Check archive builds path exists
        $this->_create_directory_if_not_exists( self::ARCHIVE_BUILDS_PATH );

        // Check files path exists
        $this->_create_directory_if_not_exists( self::UPLOAD_FILES_PATH );

        // Check file build index exists
        $this->_create_file_if_not_exists( self::BUILD_INDEX_FILE );

        // Check file lock exists
        $this->_create_file_if_not_exists( self::LOCK_FILE );

        // Check file repeat update exists
        $this->_create_file_if_not_exists( self::REPEAT_UPDATE_FILE );
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

            $this->_rewrite_file( self::LOCK_FILE, '1' );

        } else {

            $this->_rewrite_file( self::LOCK_FILE, '0' );

        }

    }


    /**
     * Download latest project to TMP folder
     *
     * @method _download_latest_project
     * @access private
     *
     * @return array of commands
     */
    private function _download_latest_project( )
    {
        
        if ( ! is_dir( self::TMP_PATH . '/.git' ) ) {

            // If temporally build with git does not exists
            $cmd[] = 'git clone -b ' . BRANCH_NAME . ' ' . REPO_LINK . ' ' . self::TMP_PATH;


        } else {

            // Update current temporally build
            $cmd[] = '(cd ' . self::TMP_PATH . '; git pull origin ' . BRANCH_NAME . ')';

        }

        // Check status of temporally build
        $cmd[] = '(cd ' . self::TMP_PATH . '; git status)';

        return $cmd;

    }


    /**
     * Create build from current pull
     *
     * @method _create_build_by_current_index
     * @access private
     *
     * @return array of commands
     */
    private function _create_build_by_current_index( )
    {

        // Create folder for the new build
        $this->_create_directory_if_not_exists( $this->_build_folder );

        // Copy new build to 'BUILDS_PATH' with new index 
        $cmd[] = 'cp -Rf ' . self::TMP_PATH . '/. ' . $this->_build_folder;

        $cmd[] = 'cp -Rf ' . self::TMP_PATH . '/files/. ' . self::UPLOAD_FILES_PATH;

        // Run right chmod to files/folders
        $cmd[] = "find {$this->_build_folder} -type f | xargs chmod -v 644 > /dev/null";
        $cmd[] = "find {$this->_build_folder} -type d | xargs chmod -v 755 > /dev/null";

        // Remove unnecessary folders
        foreach ( CLEAN_FOLDERS as $folder_name ) {
            $cmd[] = "find {$this->_build_folder} -type d -name $folder_name -exec rm -rf {} +";
        }

        // Remove unnecessary files
        foreach ( CLEAN_FILES as $file_name ) {
            $cmd[] = "find {$this->_build_folder} -type f -name $file_name -delete";
        }

        return $cmd;

    }


    /**
     * Deploy current build to server
     *
     * @method _deploy_current_build
     * @access private
     *
     * @return array of commands
     */
    private function _deploy_current_build( )
    {

        $cmd[] = 'rm ' . getcwd( ) . self::DOC_ROOT_PATH . '/' . self::UPLOAD_FILES_PATH;
        $cmd[] = 'rm -rf ' . getcwd( ) . self::DOC_ROOT_PATH . '/' . self::UPLOAD_FILES_PATH;

        // Remove old 'DOC_ROOT_PATH' link
        $cmd[] = 'rm ' . self::DOC_ROOT_PATH;
        $cmd[] = 'rm -rf ' . self::DOC_ROOT_PATH;

        // Create link on new build
        $cmd[] = 'ln -s ' . $this->_build_folder . '/ ' . self::DOC_ROOT_PATH;
        $cmd[] = 'ln -s ' . getcwd( ) . '/' . self::UPLOAD_FILES_PATH . '/ ' . self::DOC_ROOT_PATH . '/' . self::UPLOAD_FILES_PATH;

        return $cmd;

    }


    /**
     * Archive all builds, which older than build index at 'ARCHIVE_BUILDS_OLDER_THEN_INDEX' times
     *
     * @method _archive_old_builds
     * @access private
     *
     * @return array of commands
     */
    private function _archive_old_builds( )
    {

        // Get list of folders in 'BUILDS_PATH'
        $scan_result = scandir( self::BUILDS_PATH );

        // Sort folders by numeric
        sort( $scan_result, SORT_NUMERIC );

        // Get older index, which older that build index at 'ARCHIVE_BUILDS_OLDER_THEN_INDEX' times
        $older_than_index = $this->_build_index - self::ARCHIVE_BUILDS_OLDER_THEN_INDEX;

        $cmd = [ ];

        // Iterating folders
        foreach ( $scan_result as $item ) {

            if ( $item === '.' or $item === '..' ) {
                continue;
            }

            if ( ( (int) preg_replace( '/\D/', '', $item ) ) <= $older_than_index ) {
                // Get build path
                $full_build_path = getcwd( ) . '/' . self::BUILDS_PATH . '/' . $item;
                
                // Archive the build and move to 'ARCHIVE_BUILDS_PATH'
                $cmd[] =
                    '(cd ' . $full_build_path . '; tar cpzf ' . getcwd( ) . '/' . self::ARCHIVE_BUILDS_PATH . '/' . $item . '.gz . --exclude files)';
                
                // Remove this build
                $cmd[] = 'rm -rf ' . $full_build_path;
            }

        }

        return $cmd;
        
    }

}
