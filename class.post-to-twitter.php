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

class Twitter_Post  {

  protected $recent_post;
  protected $postID;
  protected $postURL;
  protected $allowPost;
  protected $twitterConfig;

  public function __construct($recent_post, $postID, $postURL, $content) {

    $this->recent_post   = $recent_post;
    $this->postID        = $postID;
    $this->postURL       = $postURL;
    $this->content       = $content;
    $this->twitterConfig = new P11_SOCIAL_CONFIG();
    if($this->twitterConfig->allowTwitterPost() == "1") {
      $this->post_tweet();
    }
  }

  /*
    *
    * Function gets called when user publishes a new blog post
    * and gets the data of the last post and posts to Twitter
    *
    */

  public function post_tweet() {
    $twitter_settings = $this->config();

    // Brings the post content down to 140 characters for twitter standards if it is more than that.
      $validTwitterContent = $this->truncate_content($this->content, 140);

      $url = 'https://api.twitter.com/1.1/statuses/update.json';
      $requestMethod = 'POST';
      $postfields = array(
        'status' => $validTwitterContent,
        'skip_status' => '1'
      );

      $twitter = new TwitterAPIExchange($twitter_settings);
      echo $twitter->buildOauth($url, $requestMethod)
      ->setPostfields($postfields)
      ->performRequest();

  }

  /*
    *
    * Truncates the content down to 140 characters if
    * the post is longer than that to make it valid for twitter
    *
  */

  private function truncate_content($text, $length) {
    $length = abs((int)$length);
    if(strlen($text) > $length) {
       $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
    }
    return($text);
  }

  private function config() {
    return array(
      'oauth_access_token' => $this->twitterConfig->getTwitterAccessToken(),
      'oauth_access_token_secret' => $this->twitterConfig->getTwitterAccessTokenSecret(),
      'consumer_key' => $this->twitterConfig->getTwitterConsumerKey(),
      'consumer_secret' => $this->twitterConfig->getTwitterConsumerSecret()
    );
  }
}
?>
