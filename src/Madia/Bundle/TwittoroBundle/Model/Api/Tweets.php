<?php

namespace Madia\Bundle\TwittoroBundle\Model\Api;

/**
 * Tweets class is for constructing the api call to twitter
 * via the twitter api. This will return all the tweets based on
 * the given parameters in a JSON string
 * 
 */
class Tweets {
    
    /* @var OAuth token */
    protected $_oAuthToken;
    /* @var OAuth token secret */
    protected $_oAuthTokenSecret;
    /* @var Consumer Key */
    protected $_consumerKey;
    /* @var Consumer Secret */
    protected $_consumerSecret;
    /* @var hashtag (without the #) */
    protected $_hashtag;
    /* @var basic url for the api request */
    protected $_baseUrl;
    /* @var constructed url with additional parameters */
    protected $_url;
    
    
    public function __construct() {
        $this->_baseUrl = 'https://api.twitter.com/1.1/search/tweets.json';
    }
    
    /**
     * Create the request based on the given parameters
     * and make the call to twitter api.
     * 
     * @param type $oAuthToken not ecoded OAuth Token for authorizing requests
     * @param type $oAuthTokenSecret not encoded OAuth Token secret
     * @param type $consumerKey not encoded Consumerkey string
     * @param type $consumerSecret not encoded Consumersecret token
     * @param string $hashtag the hashtag we should search for in the api call
     * @return type raw JSON string
      */
    public function makeApiCall($oAuthToken, $oAuthTokenSecret, $consumerKey, $consumerSecret, $hashtag) {
        
        $oauth = array( 'oauth_consumer_key' => $consumerKey,
                'oauth_nonce' => time(),
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_token' => $oAuthToken,
                'oauth_timestamp' => time(),
                'oauth_version' => '1.0');
        
        if($hashtag != 'all') {
            $hashtag = '#'.$hashtag; 
            $this->_url = $this->_baseUrl . '?q=' . rawurlencode($hashtag). '&count='.rawurlencode('100');
            $oauth = array_merge($oauth, array('q' => $hashtag, 'count' => '100'));
        }else {
            $this->_url = $this->_baseUrl. '?q=' . rawurlencode('#orotraining').  '&count=' . rawurlencode('100');
            $oauth = array_merge($oauth, array('q' => '#orotraining', 'count' => '100'));
        }
        
        $baseInfo = $this->buildBaseString($this->_baseUrl, 'GET', $oauth);
        $compositeKey = rawurlencode($consumerSecret) . '&' . rawurlencode($oAuthTokenSecret);
        $oauthSignature = base64_encode(hash_hmac('sha1', $baseInfo, $compositeKey, true));
        $oauth['oauth_signature'] = $oauthSignature;

        $header = array($this->buildAuthorizationHeader($oauth), 'Expect:');
        $options = array(CURLOPT_HTTPHEADER => $header,
                         CURLOPT_HEADER => false,
                         CURLOPT_URL => $this->_url,
                         CURLOPT_RETURNTRANSFER => true,
                         CURLOPT_SSL_VERIFYPEER => false);
        
        
        
        //make the call via curl
        $feed = curl_init();
        curl_setopt_array($feed, $options);
        $responseJson = curl_exec($feed);
        curl_close($feed);

        //return the raw json data
        return $responseJson;

    }
    
    /**
     * Build the correct url for making the api call
     * with all the corresponding parameters and method.
     * 
     * @param type $baseURI the url to make the call to
     * @param type $method api request method ('POST' || 'GEt')
     * @param type $params parameters for request
     * @return type String the basestring with request method and parameters
     */
    protected function buildBaseString($baseURI, $method, $params)
    {
        $r = array(); 
        ksort($params); 
        foreach($params as $key=>$value){
            $r[] = "$key=" . rawurlencode($value); 
        }            

        return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r)); //return complete base string
    }
    
    /**
     * Build the authorization header for the request.
     * 
     * @param type $oauth encoded paramters for the request
     * @return type String the authorization header string.
     */
    protected function buildAuthorizationHeader($oauth)
    {
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach($oauth as $key=>$value)
            $values[] = "$key=\"" . rawurlencode($value) . "\""; 

        $r .= implode(', ', $values); 
        return $r; 
    }    
}