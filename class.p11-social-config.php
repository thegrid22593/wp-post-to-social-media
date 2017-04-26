<?php

/**
*
* @author     Garrett Sanderson
* @author     P11 Creative
* @copyright  2017 P11 Creative
* @version    Release: 0.1.0
* @since      Class available since Release 0.1.0
*
*
* This Class Only handles getting the API Keys and Secrets for the various social accounts
*
*/


class P11_SOCIAL_CONFIG {

    private static $initiated = false;

    public static function init() {
        if ( !self::$initiated ) {
            $initiated = true;
        }
    }

    public function getSiteURL() {
        return site_url();
    }

    public function getFBAppID() {
        return get_option('facebook_app_id');
    }


    public function getFBAppSecret() {
        return get_option('facebook_app_secret');
    }

    public function getFBPageId() {
        return get_option('facebook_page_id');
    }

    public function getFBAccessToken() {
        return get_option('facebook_access_token');
    }

    public function allowFacebookPost() {
        return get_option('allow_fb_post');
    }

    public function updateFacebookPostPermission($permission) {
        update_option('allow_fb_post', $permission);
    }

    public function allowTwitterPost() {
        return get_option('allow_twitter_post');
    }

    public function updateTwitterPostPermission($permission) {
        update_option('allow_twitter_post', $permission);
    }

    public function getTwitterAccessToken() {
        return get_option('twitter_oauth_access_token');
    }

    public function getTwitterAccessTokenSecret() {
        return get_option('twitter_oauth_access_token_secret');
    }

    public function getTwitterConsumerKey() {
        return get_option('twitter_consumer_key');
    }

    public function getTwitterConsumerSecret() {
        return get_option('twitter_consumer_secret');
    }

}
