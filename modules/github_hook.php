<?php defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class GitHubHook
{

    /**
     * Post data
     * 
     * @var mixed
     * @type array
     */
    private $_post;


    /**
     * Name of pushed branch
     * 
     * @var mixed
     * @type string
     */
    private $_pushed_branch;


    /**
     * GitHubHook constructor.
     * 
     * @method __construct
     * @access public
     */
    public function __construct( )
    {

        // Get raw input data from POST request
        $post_raw_data = file_get_contents( 'php://input' );

        // Decode JSON data
        $this->_post = json_decode( $post_raw_data );

        // Get pushed branch
        $this->_pushed_branch = preg_replace( '/[^//]*$/', '', $this->_post->ref );

    }


    /**
     * Compare branch name
     * 
     * @param $_branch_name_
     * 
     * @return bool
     */
    public function is_branch( $_branch_name_ )
    {

        // Compare branches' names
        return 
            ($this->_pushed_branch === $_branch_name_);

    }


    /**
     * TODO: create verify by key method
     * 
     * @param $_key_
     */
    public function verify_by_key( $_key_ )
    {
        # OAUEFHiHM#(*H#MO*f9MF*(93823dsfsdfe2r
    }

}
