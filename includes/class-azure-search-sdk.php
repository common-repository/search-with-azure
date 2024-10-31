<?php

/**
 * The class responsible for interacting with the Azure Search API.
 * It's not really an SDK because it's specific to this plugin.
 */
class Azure_Search_SDK
{
    const API_VERSION = '2015-02-28';

    private $serviceName;
    private $indexName;
    private $adminKey;
    private $queryKey;

    function __construct($serviceName, $indexName, $adminKey, $queryKey)
    {
        $this->serviceName = $serviceName;
        $this->indexName = $indexName;
        $this->adminKey = $adminKey;
        $this->queryKey = $queryKey;
    }

    /**
     * Create or update the 'post' index used for indexing posts
     * @return Azure_Search_Post_Response the response
     */
    public function createPostsIndex()
    {
        // fields are based on WP_Post
        // https://codex.wordpress.org/Class_Reference/WP_Post
        // ID is String because that's what a key has to be
        $postData = array(
            "fields" => array(
                array("name" => "ID", "type" => "Edm.String", "key" => true, "searchable" => false),
                array("name" => "post_author", "type" => "Edm.String", "searchable" => false),
                array("name" => "post_name", "type" => "Edm.String"),
                array("name" => "post_type", "type" => "Edm.String", "searchable" => false),
                array("name" => "post_title", "type" => "Edm.String"),
                array("name" => "post_date", "type" => "Edm.String", "searchable" => false),
                array("name" => "post_date_gmt", "type" => "Edm.DateTimeOffset", "searchable" => false),
                array("name" => "post_content", "type" => "Edm.String", "searchable" => false),
                array("name" => "post_content_text", "type" => "Edm.String"),
                array("name" => "post_excerpt", "type" => "Edm.String"),
                array("name" => "post_status", "type" => "Edm.String", "searchable" => false),
                array("name" => "post_modified", "type" => "Edm.String", "searchable" => false),
                array("name" => "post_modified_gmt", "type" => "Edm.DateTimeOffset", "searchable" => false),
                array("name" => "comment_count", "type" => "Edm.String", "searchable" => false)
            ),
            "corsOptions" => array(
                "allowedOrigins" => array("*")
            ),
            "suggesters" => array(
                array("name" => "sg", "searchMode" => "analyzingInfixMatching",
                    "sourceFields" => array("post_name", "post_title", "post_excerpt", "post_content_text"))
            )
        );

        $url = "/indexes/{$this->indexName}";
        return $this->post($url, $postData, 'PUT');
    }

    /**
     * Add or update a post
     * @param WP_Post post the post
     * @return Azure_Search_Post_Response the response
     */
    public function addPost($post)
    {
        _log('addPost: ' . $post->ID);
        $postData = array(
            "value" => array(
                $this->mapPostToDocument($post)
            )
        );

        $url = "/indexes/{$this->indexName}/docs/index";
        return $this->post($url, $postData);
    }

    /**
     * Add or update a set of posts
     * @param $posts
     * @return Azure_Search_Post_Response the response
     */
    public function addPosts($posts)
    {
        _log('addPosts: ' . count($posts));
        $postData = array(
            "value" => array_map(array('Azure_Search_SDK', 'mapPostToDocument'), $posts)
        );

        $url = "/indexes/{$this->indexName}/docs/index";
        return $this->post($url, $postData);
    }

    /**
     * Delete a post
     */
    public function deletePost($postId)
    {
        _log('deletePost: ' . $postId);
        $postData = array(
            "value" => array(
                array(
                    "@search.action" => "delete",
                    "ID" => strval($postId)
                )
            )
        );

        $url = "/indexes/{$this->indexName}/docs/index";
        return $this->post($url, $postData);
    }

    /**
     * Search suggestions
     */
    public function suggestions($text)
    {
        $url =
            "/indexes/{$this->indexName}/docs/suggest?suggesterName=sg" .
            '&$top=5' . // TODO use the same variable for limit as the widget
            '&searchFields=post_name,post_title,post_content_text' .
            '&$select=ID,post_type,post_name,post_title&search=' . urlencode($text);
        $result = $this->get($url);
        if (200 !== $result->httpCode) {
            die (json_encode($result->response));
        }
        $response = $result->response;
        return $response["value"];
    }

    /**
     * @param $url the relative url, excluding the api-version
     * @return Azure_Search_Get_Response the response
     */
    private function get($url)
    {
        $absUrl = "https://{$this->serviceName}.search.windows.net{$url}&api-version=" . self::API_VERSION;

        $ch = curl_init($absUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', "api-key: {$this->queryKey}"));

        // disable SSL verification because it fails on Azure
        // TODO remove this and make it work without it
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Send the request
        $responseText = curl_exec($ch);

        $response = new Azure_Search_Get_Response();

        // check for curl error
        if(curl_errno($ch))
        {
            $response->is_error = TRUE;
            $response->error_message = curl_error($ch);
            return $response;
        }

        $response->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseText) {
            $response->response = json_decode($responseText, TRUE);
        }

        return $response;
    }

    /**
     * Post or put
     * @param $url the relative url, excluding the api-version
     * @return Azure_Search_Post_Response the response
     */
    private function post($url, $postData, $requestType = "POST")
    {
        $absUrl = "https://{$this->serviceName}.search.windows.net{$url}?api-version=" . self::API_VERSION;
        $jsonData = json_encode($postData);

        $ch = curl_init($absUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Prefer: return=minimal',
            "api-key: {$this->adminKey}"));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // disable SSL verification because it fails on Azure
        // TODO remove this and make it work without it
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Send the request
        $responseText = curl_exec($ch);

        $response = new Azure_Search_Post_Response();

        // check for curl error
        if(curl_errno($ch))
        {
            $response->is_error = TRUE;
            $response->error_message = curl_error($ch);
            return $response;
        }

        $response->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseText) {
            $responseJson = json_decode($responseText, TRUE);
            $response->is_error = array_key_exists('error', $responseJson);
            if ($response->is_error) {
                $error = $responseJson['error'];
                if (array_key_exists('message', $error)) {
                    $response->error_message = $error['message'];
                }
            }
        } else {
            $response->is_error = FALSE;
        }

        return $response;
    }

    /**
     * Convert a wp_date to UTC
     */
    private function wp_date_to_utc($wp_date)
    {
        if (substr($wp_date, 0, 4) === '0000') {
            return '1970-01-01T00:00:00.000Z';
        }

        return str_replace(' ', 'T', $wp_date) . '.000Z';
    }

    /**
     * Expand shortcodes and remove html tags
     */
    private function content_to_text($content)
    {
        $a = do_shortcode($content);
        $b = wp_strip_all_tags($a, true);
        return $b;
    }

    /**
     * @param $post
     * @return array
     */
    private function mapPostToDocument($post)
    {
        return array(
            "@search.action" => "upload",
            "ID" => strval($post->ID),
            "post_author" => $post->post_author,
            "post_name" => $post->post_name,
            "post_type" => $post->post_type,
            "post_title" => $post->post_title,
            "post_date" => $post->post_date,
            "post_date_gmt" => $this->wp_date_to_utc($post->post_date_gmt),
            "post_content" => $post->post_content,
            "post_content_text" => $this->content_to_text($post->post_content),
            "post_excerpt" => $post->post_excerpt,
            "post_status" => $post->post_status,
            "post_modified" => $post->post_modified,
            "post_modified_gmt" => $this->wp_date_to_utc($post->post_modified_gmt),
            "comment_count" => $post->comment_count
        );
    }
}

class Azure_Search_Get_Response
{
    public $httpCode;
    public $response;
}

class Azure_Search_Post_Response
{
    public $httpCode;
    public $is_error;
    public $error_message;
}
