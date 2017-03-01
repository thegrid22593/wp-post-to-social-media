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

    public static function getSiteURL() {
        return site_url();
    }

    public static function getFBAppID() {
        return get_option('facebook_app_id');
    }


    public static function getFBAppSecret() {
        return get_option('facebook_app_secret');
    }

    public static function getFBPageId() {
        return get_option('facebook_page_id');
    }

    public static function getFBAccessToken() {
        return get_option('facebook_access_token');
    }

    public static function getTwitterAccessToken() {
        return get_option('twitter_oauth_access_token');
    }

    public static function getTwitterAccessTokenSecret() {
        return get_option('twitter_oauth_access_token_secret');
    }

    public static function getTwitterConsumerKey() {
        return get_option('twitter_consumer_key');
    }

    public static function getTwitterConsumerSecret() {
        return get_option('twitter_consumer_secret');
    }

}
