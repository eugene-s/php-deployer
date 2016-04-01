<?php defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class BaseDeploy
{    
    
    /**
     * Check directory, if not exists, then create it
     *
     * @method _create_directory_if_not_exists
     * @access protected
     *
     * @param $_directory_
     */
    protected function _create_directory_if_not_exists( $_directory_ )
    {

        if ( ! is_dir( $_directory_ ) ) {

            $old_mask = umask( );
            mkdir( $_directory_, 0755, true );
            umask( $old_mask );

        }

    }


    /**
     * Check file, if not exists, then create it
     *
     * @method _create_file_if_not_exists
     * @access protected
     *
     * @param $_file_name_
     */
    protected function _create_file_if_not_exists( $_file_name_ )
    {

        if ( ! file_exists( $_file_name_ ) ) {

            $this->_rewrite_file( $_file_name_ );

        }

    }


    /**
     * Rewrite file with new content
     *
     * @method _rewrite_file
     * @access protected
     *
     * @param $_file_name_
     * @param int $_content_
     */
    protected function _rewrite_file( $_file_name_, $_content_ = 0 )
    {

        // Open file with mode write
        $handler = fopen( $_file_name_, 'wb' );

        // Write content to file
        fwrite( $handler, $_content_ );

        // Close file
        fclose( $handler );

    }


    /**
     * Get content of file
     *
     * @method _read_file
     * @access protected
     *
     * @param $_file_name_
     *
     * @return string
     */
    protected function _read_file( $_file_name_ )
    {

        $content = file_get_contents( $_file_name_ );

        return $content;

    }
    
}
