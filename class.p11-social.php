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

class P11_SOCIAL  {

  private static $initiated = false;

  /*
  *
  * Initializes the instance of P11_SOCIAL
  *
  */

  public static function init() {

    if ( !self::$initiated ) {
      self::init_hooks();
      require_once P11_SOCIAL_PLUGIN_DIR . '/includes/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
      require_once P11_SOCIAL_PLUGIN_DIR . '/includes/TwitterAPIExchange/TwitterAPIExchange.php';
      require_once P11_SOCIAL_PLUGIN_DIR . '/class.p11-social-config.php';
      require_once P11_SOCIAL_PLUGIN_DIR . '/class.post-to-twitter.php';
      require_once P11_SOCIAL_PLUGIN_DIR . '/class.post-to-facebook.php';

    }

    // checking if session already exists
    if( !session_id() ) {
      session_start();
    }
  }

  /*
  *
  * Initializes the Plugin Wordpress Hooks
  *
  */

  public static function init_hooks() {
    self::$initiated = true;
    add_action( 'admin_menu', array( 'P11_SOCIAL', 'p11_social_admin_menu' ));
    add_action( 'admin_init', array( 'P11_SOCIAL', 'p11_social_settings' ));
    add_action( 'admin_init', array( 'P11_SOCIAL', 'set_globals' ));
    add_action( 'publish_post', array( 'P11_SOCIAL', 'get_last_post' ));
  }

  /*
  *
  * Adds the Admin Menu
  *
  */

  public static function p11_social_admin_menu() {
    add_options_page(
    'P11 Social',
    'P11 Social',
    'manage_options',
    'p11-social-settings',
    array( 'P11_SOCIAL', 'p11_social_settings_page' )
  );
}

/*
*
*
* Set Global Data For Plugin Use
* TODO: I don't think I need the function now that I have config.
*
*
*/

public static function set_globals() {
  $_SESSION['site_url'] = P11_SOCIAL_CONFIG::getSiteURL();
  $_SESSION['fb_app_id'] = P11_SOCIAL_CONFIG::getFBAppID();
  $_SESSION['fb_app_secret'] = P11_SOCIAL_CONFIG::getFBAppSecret();
  $_SESSION['fb_page_id'] = P11_SOCIAL_CONFIG::getFBPageId();

  $_SESSION['twitter_oauth_access_token'] = get_option('twitter_oauth_access_token');
  $_SESSION['twitter_oauth_access_token'] = get_option('twitter_oauth_access_token_secret');
  $_SESSION['twitter_consumer_key'] = get_option('twitter_consumer_key');
  $_SESSION['twitter_consumer_secret'] = get_option('twitter_consumer_secret');

}

/*
*
* Construct and Render The Settings Page
*
*/
public static function p11_social_settings_page() {
  ?>

  <div class="wrap">
    <h2>P11 Social API Settings</h2>

    <form method="post" action="options.php">
      <?php settings_fields( 'p11-social-settings-group' ); ?>
      <?php do_settings_sections( 'p11-social-settings-group' ); ?>
      <table class="form-table">
        <h1>Twitter API</h1>
        <tr valign="top">
          <th scope="row">Twitter OAuth Access Token</th>
          <td><input type="text" name="twitter_oauth_access_token" value="<?php echo esc_attr( get_option('twitter_oauth_access_token') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row">Twitter Ouath Access Token Secret</th>
          <td><input type="text" name="twitter_oauth_access_token_secret" value="<?php echo esc_attr( get_option('twitter_oauth_access_token_secret') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row">Twitter Consumer Key</th>
          <td><input type="text" name="twitter_consumer_key" value="<?php echo esc_attr( get_option('twitter_consumer_key') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row">Twitter Consumer Secret</th>
          <td><input type="text" name="twitter_consumer_secret" value="<?php echo esc_attr( get_option('twitter_consumer_secret') ); ?>" /></td>
        </tr>
      </table>

      <hr>

      <table class="form-table">
        <h1>Facebook API</h1>

        <?php self::checkForValidAccessToken(); ?>

          <tr valign="top">
            <th scope="row">Facebook App ID</th>
            <td><input type="text" name="facebook_app_id" value="<?php echo esc_attr( get_option('facebook_app_id') ); ?>" /></td>
          </tr>

          <tr valign="top">
            <th scope="row">Facebook App Secret</th>
            <td><input type="text" name="facebook_app_secret" value="<?php echo esc_attr( get_option('facebook_app_secret') ); ?>" /></td>
          </tr>

          <tr valign="top">
            <th scope="row">Facebook Page ID</th>
            <td><input type="text" name="facebook_page_id" value="<?php echo esc_attr( get_option('facebook_page_id') ); ?>" /></td>
          </tr>

        </table>

        <?php submit_button(); ?>

      </form>

    </div>

    <?php
  }

  /*
  *
  *
  * Registers Options in the WordPress Options Table.
  * TODO: Eventually Move this to the config class.
  *
  *
  */

  public static function p11_social_settings() {
    // Set Twitter Settings
    register_setting( 'p11-social-settings-group', 'twitter_oauth_access_token' );
    register_setting( 'p11-social-settings-group', 'twitter_oauth_access_token_secret' );
    register_setting( 'p11-social-settings-group', 'twitter_consumer_key' );
    register_setting( 'p11-social-settings-group', 'twitter_consumer_secret' );

    // Set Facebook Settings
    register_setting( 'p11-social-settings-group', 'facebook_app_id' );
    register_setting( 'p11-social-settings-group', 'facebook_app_secret' );
    register_setting( 'p11-social-settings-group', 'facebook_page_id' );
    register_setting( 'p11-social-settings-group', 'facebook_access_token' );
  }

  /*
  *
  * Check to see if the access Token is in the database
  * TODO: Make a function like this for twitter
  *
  */

  public static function checkForValidAccessToken() {

    if( get_option('facebook_access_token') ) {
      $_SESSION['fb_access_token'] = P11_SOCIAL_CONFIG::getFBAccessToken();
      var_dump($_SESSION);
    }

    elseif( !isset($_SESSION['fb_access_token']) ) {

      $fb = new Facebook\Facebook([
        'app_id' => P11_SOCIAL_CONFIG::getFBAppID(),
        'app_secret' => P11_SOCIAL_CONFIG::getFBAppSecret(),
        'default_graph_version' => 'v2.8',
      ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['email', 'manage_pages', 'publish_pages', 'user_posts']; // Optional permissions

        $loginUrl = $helper->getLoginUrl(P11_SOCIAL_PLUGIN_URL . '/fb-callback.php', $permissions);

        echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';


    }

  }

  /*
  *
  * Function gets called when user publishes a new blog posts
  * and gets the data from that last blog post published.
  * TODO: Think about moving this to a post class
  *
  */

  public static function get_last_post() {
    $args = array(
      'numberposts' => 1,
      'offset' => 0,
      'category' => 0,
      'orderby' => 'post_date',
      'order' => 'DESC',
      'include' => '',
      'exclude' => '',
      'meta_key' => '',
      'meta_value' =>'',
      'post_type' => 'post',
      'post_status' => 'draft, publish, future, pending, private',
      'suppress_filters' => true
    );

    $recent_post = wp_get_recent_posts( $args, ARRAY_A );

    // var_dump($recent_post);
    foreach ($recent_post as $post ) {
      $postID = $post['ID'];
      $postURL = $post['guid'];
      if( has_post_thumbnail() ) {
        $postImage = the_post_thumbnail();
      }
    }
    // var_dump($postID);
    $imageArr = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), 'single-post-thumbnail' );
    $image = $imageArr[0];

    // TODO: Make a post to social class
    self::post_to_twitter($recent_post, $postID, $postURL);
    self::post_to_facebook($recent_post, $postID, $postURL, $postImage);
  }


  /*
  *
  * Function gets called when user publishes a new blog post
  * and gets the data of the last post and posts to Twitter
  * TODO: move this to a post class
  *
  */
  public static function post_to_twitter($recent_post, $postID, $postURL) {

    $twitter_settings = array(
      'oauth_access_token' => P11_SOCIAL_CONFIG::getTwitterAccessToken(),
      'oauth_access_token_secret' => P11_SOCIAL_CONFIG::getTwitterAccessTokenSecret(),
      'consumer_key' => P11_SOCIAL_CONFIG::getTwitterConsumerKey(),
      'consumer_secret' => P11_SOCIAL_CONFIG::getTwitterConsumerSecret()
    );

    // Brings the post content down to 140 characters for twitter standards if it is more than that.
    $twitterPost = self::truncate($recent_post[0]['post_content'], 140);
    // $twitterPost = substr($recent_post[0]['post_content'], 0, $twitterPost );

    $url = 'https://api.twitter.com/1.1/statuses/update.json';
    $requestMethod = 'POST';
    $postfields = array(
      'status' => $twitterPost,
      'skip_status' => '1'
    );

    $twitter = new TwitterAPIExchange($twitter_settings);
    echo $twitter->buildOauth($url, $requestMethod)
    ->setPostfields($postfields)
    ->performRequest();

  }

  public static function truncate($text, $length) {
   $length = abs((int)$length);
   if(strlen($text) > $length) {
      $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
   }
   return($text);
}

  /*
  *
  * Function gets called when user publishes a new blog post
  * and gets the data of the last post and posts to Facebook
  * TODO: move this to a facebook post class
  *
  */
  public static function post_to_facebook($recent_post, $postID, $postURL, $postImage) {
    if( $_SESSION['fb_access_token'] ) {

      $accessToken = $_SESSION['fb_access_token'];

      // Start Authorization of Facebook API
      $fb = new Facebook\Facebook([
        'app_id' => P11_SOCIAL_CONFIG::getFBAppID(),
        'app_secret' => P11_SOCIAL_CONFIG::getFBAppSecret(),
        'default_graph_version' => 'v2.8',
      ]);

    	$params = array(
        "message" => $recent_post[0]['post_content'],
        "link" => $postURL,
        "picture" => $postImage,
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
