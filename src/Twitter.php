<?php
/**
 * Created by PhpStorm.
 * User: AfolabiAbass
 * Date: 16/06/2018
 * Time: 21:23
 */

namespace AfolabiAbass\App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Twitter
 * @package AfolabiAbass\App
 */
class Twitter
{
    /**
     * @var array|false|string
     */
    private $url;
    /**
     * @var array|false|string
     */
    private $key;
    /**
     * @var array|false|string
     */
    private $secret;
    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->url = getenv('TWITTER_API_URL');
        $this->key = getenv('TWITTER_API_KEY');
        $this->secret = getenv('TWITTER_API_SECRET');

        $this->client = new Client();
    }

    /**
     * @return string
     */
    private function getAppToken()
    {
        $auth = urlencode($this->key).':'.urlencode($this->secret);

        return base64_encode($auth);
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    private function accessToken()
    {
        try {
            $response = $this->client->request('POST', $this->url . 'oauth2/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->getAppToken(),
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8.',
                    'Content-Length' => 29,
                    'Accept-Encoding' => 'gzip'
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                $twitter_access_token = json_decode($content)->access_token;
                return ['status' => true, 'token' => $twitter_access_token];
            }
            return ['status' => false, 'token' => ''];
        }catch(\Exception $e) {
            return ['status' => false, 'token' => ''];
        }
    }

    /**
     * @return mixed|string
     * @throws GuzzleException
     */
    public function getAccessToken()
    {
        if(isset($_SESSION['twitter_access_token']) && $_SESSION['twitter_access_token']) {
            return $_SESSION['twitter_access_token'];
        } else {
            $token = $this->accessToken();
            if ($token['status']) {
                $_SESSION['twitter_access_token'] = $token['token'];
                return $token['token'];
            }
            return '';
        }
    }

    /**
     * @param $query
     * @return mixed
     * @throws GuzzleException
     */
    public function getTweets($query)
    {
        $twitter_results = json_decode('{"statuses"}');
        try {
            $response = $this->client->request('GET', $this->url."1.1/search/tweets.json?q=%23{$query}&result_type=recent&count=100", [
                'headers' => [
                    'Authorization' => 'Bearer '. $this->getAccessToken(),
                    'Accept-Encoding' => 'gzip'
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                $twitter_results = json_decode($content);
            } else {
                return $twitter_results;
            }
        } catch(\Exception $e) {
            //
        }
        return $twitter_results;
    }

}
