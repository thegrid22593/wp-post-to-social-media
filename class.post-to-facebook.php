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

          $accessToken = $_SESSION['fb_access_token'];

        // Start Authorization of Facebook API

          $fb = new Facebook\Facebook([
            'app_id' => $this->fbConfig->getFBAppID(),
            'app_secret' => $this->fbConfig->getFBAppSecret(),
            'default_graph_version' => 'v2.9',
            'persistent_data_handler'=> 'session'
          ]);

          // Sets the facebook post content to the WordPress Post Content
          $params = array(
            "message" => $this->recent_post[0]['post_content'],
            "link" => $this->postURL,
            "object_attachment" => $this->postImage,
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

        //   try {
        //       $token = $helper->getAccessToken();
        //       if(isset($token)) {
        //           $response = $fb->post('/' . $_SESSION['fb_page_id'] . '/feed', $params, $pageToken);
        //           $graphNode = $response->getGraphNode();
        //           echo 'Posted with id: ' . $graphNode['id'];
        //       } else {
        //           $permission = array('scope'=>'email, publish_actions,manage_pages, publish_pages');
        //           echo "<a href='".$helper->getLoginUrl($url,$permission)."'>Click to post</a>";
        //       }
          //
        //   }

        //   catch (Facebook\Exceptions\FacebookSDKException $e) {
        //         echo $e->getMessage();
        // }

        //     $url = 'https://graph.facebook.com/me/accounts?access_token='. $accessToken;
        //     $ch = curl_init();
        //     curl_setopt ($ch, CURLOPT_URL, $url);
        //     curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //     curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        //     $contents = curl_exec($ch);
        //     if (curl_errno($ch)) {
        //       echo curl_error($ch);
        //       echo "\n<br />";
        //       $contents = '';
        //     } else {
        //       curl_close($ch);
        //     }
          //
        //     if (!is_string($contents) || !strlen($contents)) {
        //     echo "Failed to get contents.";
        //     $contents = '';
        //     }
          //
        //     // echo $contents;
          //
        //   $pageAccessToken = $contents->data[0]->access_token;

        //   $posturl = '/'.$_SESSION['fb_page_id'].'/feed';
        //   $result = $fb->post($posturl,$params,$pageAccessToken);
        //   try {
        //     $result = $fb->post($posturl,$params,$pageAccessToken);
        //   } catch(Facebook\Exceptions\FacebookResponseException $e) {
        //     // When Graph returns an error
        //     echo 'Graph returned an error: ' . $e->getMessage();
        //     exit;
        //   } catch(Facebook\Exceptions\FacebookSDKException $e) {
        //     // When validation fails or other local issues
        //     echo 'Facebook SDK returned an error: ' . $e->getMessage();
        //     exit;
        //   }
        }
    }
}

?>
