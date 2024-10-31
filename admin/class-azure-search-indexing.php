<?php

/**
 * Functions to update the Azure Search index.
 * These methods are all triggered by admin hooks of the same name.
 */
class Azure_Search_Indexing {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

    private function get_post_status($post)
    {
        $post_status = get_post_status($post);
        _log("post_status:$post_status");
        if ('inherit' == $post_status) {
            return $this->get_post_status($post->post_parent); // recurse
        } else {
            return $post_status;
        }
    }

    private function get_sdk() {
        $serviceName = Azure_Search_Settings_Page::getServiceName();
        if (!$serviceName) {
            return FALSE;
        }
        $indexName = Azure_Search_Settings_Page::getIndexName();
        if (!$indexName) {
            return FALSE;
        }
        $adminKey = Azure_Search_Settings_Page::getAdminKey();
        if (!$adminKey) {
            return FALSE;
        }
        return new Azure_Search_SDK($serviceName, $indexName, $adminKey, FALSE);
    }

    /**
     * @param int $post_ID Post ID.
     * @param WP_Post $post Post object.
     * @param bool $update Whether this is an existing post being updated or not.
     */
    function wp_insert_post($post_id, $post, $update)
    {
        _log("wp_insert_post - post_id:$post_id update:$update");
        _log($post);

        // return unless it's one of the post types that we're interested in
        _log("post_type: {$post->post_type}");
        switch($post->post_type) {
            case 'post':
            case 'page':
                break;
            default:
                return;
        }

        $post_status = $this->get_post_status($post);
        _log("post_status: $post_status");

        $sdk = $this->get_sdk();
        if (!$sdk) {
            return;
        }

        // if status is publish then add/update index, else remove from index
        if ('publish' == $post_status) {
            $response = $sdk->addPost($post);
        } else {
            $response = $sdk->deletePost($post_id);
        }

        _log('response: ' . json_encode($response));
    }

    function delete_post( $postId )
    {
        // deleted from trash
        _log('delete_post: ' . $postId);

        $sdk = $this->get_sdk();
        if ($sdk) {
            $response = $sdk->deletePost($postId);
            _log('response: ' . json_encode($response));
        }
    }

    function comment_post( $commentId )
    {
        _log('comment_post: ' . $commentId);
    }

    function edit_comment( $commentId )
    {
        _log('edit_comment: ' . $commentId);
    }

    function delete_comment( $commentId )
    {
        _log('delete_comment: ' . $commentId);
    }

    function delete_attachment( $postId )
    {
        // eg delete media
        _log('delete_attachment: ' . $postId);
    }

    function add_attachment( $postId )
    {
        // eg add media
        _log('add_attachment: ' . $postId);
    }

    function edit_attachment( $postId )
    {
        // eg edit media
        _log('edit_attachment: ' . $postId);
    }

    function transition_post_status( $new_status, $old_status, $post )
    {
        // When a post status changes, check child posts that inherit their status from parent
        _log("transition_post_status - new_status:$new_status old_status:$old_status postId: {$post->ID}");
        // _log($post);
    }

}
