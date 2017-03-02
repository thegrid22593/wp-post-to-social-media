<?php

/**
*
* @author     Garrett Sanderson
* @author     p11 Creative
* @copyright  2017 p11 Creative
* @version    Release: 0.1.0
* @since      Class available since Release 0.1.0
*
*/

class Facebook_Post  {

    protected $recent_post;
    protected $postID;
    protected $postURL;
    protected $postImage;
    protected $fbConfig;


    public function __construct($recent_post, $postID, $postURL, $postImage) {
        $this->recent_post = $recent_post;
        $this->postID = $postID;
        $this->postURL = $postURL;
        $this->postImage = $postImage;
        $this->postStatus();
    }

    /*
    *
    * Function gets called when user publishes a new blog post
    * and gets the data of the last post and posts to Facebook
    *
    */

    public function postStatus() {
        if( $_SESSION['fb_access_token'] ) {
        $fbConfig = new P11_SOCIAL_CONFIG();
      $accessToken = $_SESSION['fb_access_token'];

      // Start Authorization of Facebook API
      $fb = new Facebook\Facebook([
        'app_id' => $fbConfig->getFBAppID(),
        'app_secret' => $fbConfig->getFBAppSecret(),
        'default_graph_version' => 'v2.8',
      ]);

    	$params = array(
        "message" => $this->recent_post[0]['post_content'],
        "link" => $this->postURL,
        "picture" => $this->postImage,
    	);

      $url = 'https://graph.facebook.com/me/accounts?access_token='. $accessToken;


      // TODO: Move to a function within the facebook post class for cleaner code.
      $response = wp_remote_get($url);
      $jsonfile = file_get_contents($url);
      $jsondata = json_decode($jsonfile);
      $pageAccessToken = $jsondata->data[0]->access_token;

    	$posturl = '/'.$_SESSION['fb_page_id'].'/feed';
    	$result = $fb->post($posturl,$params,$pageAccessToken);
      try {
        $result = $fb->post($posturl,$params,$pageAccessToken);
      } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
      } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
      }
    }
    }

}

?>
