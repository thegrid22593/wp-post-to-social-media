<?php ob_start();

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
    protected $postAllowed;


    public function __construct($recent_post, $postID, $postURL, $postImage) {
        $this->recent_post = $recent_post;
        $this->postID = $postID;
        $this->postURL = $postURL;
        $this->postImage = $postImage;
        $this->fbConfig = new P11_SOCIAL_CONFIG();
        if($this->fbConfig->allowFacebookPost() == "1") {
          $this->postStatus();
        }
    }

    /*
    *
    * Function gets called when user publishes a new blog post
    * and gets the data of the last post and posts to Facebook
    *
    */

    public function postStatus() {
      if( $_SESSION['fb_access_token'] ) {

        // Set Access Token
          $accessToken = $_SESSION['fb_access_token'];

        // Start Authorization of Facebook API
          $fb = new Facebook\Facebook([
            'app_id' => $this->fbConfig->getFBAppID(),
            'app_secret' => $this->fbConfig->getFBAppSecret(),
            'default_graph_version' => 'v2.9',
            'persistent_data_handler' => 'session'
          ]);

          // var_dump($this->recent_post);
          // die();

          // Sets the facebook post content to the WordPress Post Content
          $params = array(
            "message" => $this->recent_post[0]['post_content'],
            "title" => $this->recent_post[0]['post_title'],
            "description" => $this->recent_post[0]['post_content'],
            "link" => $this->postURL,
            "picture" => $this->postImage,
          );

          $helper = $fb->getRedirectLoginHelper();
          // Returns array of all pages attached to the user
          $pageTokens = $fb->get('/me/accounts?fields=id,name,access_token&access_token=' . $accessToken)
                           ->getGraphEdge()
                           ->asArray();

        // Loops through the array and gets the page access token attached to the page id provided
          foreach($pageTokens as $key => $value) {
              if($value['id'] = $this->fbConfig->getFBPageId()) {
                  $pageToken = $value['access_token'];
                  break;
              }
          }

          // Posts to the page
          $response = $fb->post('/' . $_SESSION['fb_page_id'] . '/feed', $params, $pageToken);
          $graphNode = $response->getGraphNode();

        }
    }
}

?>
