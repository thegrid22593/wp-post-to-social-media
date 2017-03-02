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

  private $initiated = false;
  private $fbIsAuthenticated = true;
  private $twitterIsAuthenticated = true;
  private $config;

  /*
  *
  * Initializes the instance of P11_SOCIAL
  *
  */
  public function __construct() {
    
    // Initiate if not already
    if( !$this->initiated ) {
      $this->initiated = true;
      $this->init_hooks();
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

  public function init_hooks() {
    add_action( 'admin_menu', array( $this, 'p11_social_admin_menu' ));
    add_action( 'admin_init', array( $this, 'p11_social_settings' ));
    add_action( 'admin_init', array( $this, 'set_globals' ));
    add_action( 'publish_post', array( $this, 'get_last_post' ));
    add_action( 'admin_notices', array( $this, 'admin_notice_error' ));
  }

  /*
  *
  * Adds the Admin Menu
  *
  */

  public function p11_social_admin_menu() {
    add_options_page(
    'P11 Social',
    'P11 Social',
    'manage_options',
    'p11-social-settings',
    array( $this, 'p11_social_settings_page' )
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

public function set_globals() {
  $this->config = new P11_SOCIAL_CONFIG();
  $_SESSION['site_url'] = $this->config->getSiteURL();
  $_SESSION['fb_app_id'] = $this->config->getFBAppID();
  $_SESSION['fb_app_secret'] = $this->config->getFBAppSecret();
  $_SESSION['fb_page_id'] = $this->config->getFBPageId();
  
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
public function p11_social_settings_page() {
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
        <?php var_dump($_SESSION); ?>
        <?php if( get_option('facebook_access_token') ) :?>
          <?php $this->checkForValidAccessToken(); ?>
        <?php endif;?>

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

  public function p11_social_settings() {
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

  public function checkForValidAccessToken() {
    var_dump($_SESSION);
    if( get_option('facebook_access_token') ) {
      $_SESSION['fb_access_token'] = P11_SOCIAL_CONFIG::getFBAccessToken();
      var_dump($_SESSION);
      echo '<h2>You are logged in and authenticated with Facebook.</h2>';
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
  * Displays admin notices for weather or not you are authenticated with the social media's
  *
  */

  public function admin_notice_error() {

    if( $this->fbIsAuthenticated ) {
      $class = 'notice notice-success is-dismissible';
      $message1 = __( 'Your are authorized with Facebook.', 'sample-text-domain' );
    } else {
      $class = 'notice notice-error is-dismissible';
      $message1 = __( 'Please authorize your Facebook App.', 'sample-text-domain' );
    }

    if( $this->twitterIsAuthenticated ) {
      $class = 'notice notice-success is-dismissible';
      $message2 = __( 'You are authrorized with Twitter.', 'sample-text-domain' );
    } else {
      $class = 'notice notice-error is-dismissible';
      $message2 = __( 'Please authorize your Twitter App.', 'sample-text-domain' );
    }
	
	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message1 ); 
  printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message2 ); 
}

  /*
  *
  * Function gets called when user publishes a new blog posts
  * and gets the data from that last blog post published.
  *
  */

  public function get_last_post() {
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

    foreach ($recent_post as $post ) {
      $postID = $post['ID'];
      $postURL = $post['guid'];
      if( has_post_thumbnail() ) {
        $postImage = the_post_thumbnail();
      }
    }

    $imageArr = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), 'single-post-thumbnail' );
    $image = $imageArr[0];

    // TODO: Add an option to allow people to check if they want to send posts then do a check here before posting
    $post_to_twitter = new Twitter_Post($recent_post, $postID, $postURL);
    $post_to_facebook = new Facebook_Post($recent_post, $postID, $postURL, $postImage);
    
  }

}

?>
