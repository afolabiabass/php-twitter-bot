<?php
/**
 * Created by PhpStorm.
 * User: AfolabiAbass
 * Date: 16/06/2018
 * Time: 23:56
 */

namespace AfolabiAbass\App;


use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Phirehose;
use OauthPhirehose;


class Sheets extends OauthPhirehose
{
    private $client;

    private $values;

    private $twitter_data;

    /**
     * Sheets constructor.
     * @param string $data
     * @internal param string $token
     * @internal param string $secret
     * @internal param string $method
     */
    public function __construct($data)
    {
        $this->client = new \Google_Client();
        $this->client->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $this->client->SetRedirectUri(getenv('GOOGLE_CLIENT_REDIRECT'));
        $this->client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        $this->client->setAccessType('offline');

        $this->getAccessToken();

        $this->twitter_data = $data;
        parent::__construct($this->twitter_data['oauth_token'], $this->twitter_data['oauth_secret'], $this->twitter_data['method']);

        $this->values = [];
    }

    /**
     * @param $query
     * @return array
     */
    public function getValues($query)
    {
        $twitter = new Twitter();
        $twitter_results = $twitter->getTweets($query);
        foreach ($twitter_results->statuses as $tweet) {
            $user = $tweet->user;
            if ($user->followers_count >= 1000 && $user->followers_count <= 50000) {
                $this->values[] = [$user->name, $user->followers_count];
            }
        }
        return $this->values;
    }

    /**
     * @param $filter
     */
    public function updateSpreadSheet($filter)
    {
        $this->setTrack(array($filter));
        $this->consume();
    }

    private function getAuthorizationUrl()
    {
        $auth_url = $this->client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    public function setAccessToken()
    {
//        if(isset($_GET['code']) && $_GET['code'] != '') {
//            $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
//            $google_access_token = $this->client->getAccessToken();//['access_token'];
//            $_SESSION['google_access_token'] = $google_access_token;
//            $this->client->setAccessToken($google_access_token);
//            return true;
//        } else {
            //$this->getAuthorizationUrl();
            $auth_url = $this->client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $auth_url);
            print 'Enter verification code: ';
            $auth_code = trim(fgets(STDIN));

            $this->client->fetchAccessTokenWithAuthCode($auth_code);
            $google_access_token = $this->client->getAccessToken();
            $this->client->setAccessToken($google_access_token);
//        }
    }

    public function getAccessToken()
    {
//        if(isset($_SESSION['google_access_token']) && $_SESSION['google_access_token']) {
//            $this->client->setAccessToken($_SESSION['google_access_token']);
//            if($this->client->isAccessTokenExpired()) {
//                $this->setAccessToken();
//            }
//            return true;
//        } else {
            $this->setAccessToken();
//        }
    }

    /**
     * @param string $status
     */
    public function enqueueStatus($status)
    {
        $data = json_decode($status, true);
        if (is_array($data) && isset($data['user']['screen_name'])) {
            $sheet = new Google_Service_Sheets($this->client);
            $spreadsheet_id = '1MfYloVkH9iDSunAjfEfzfwWuhXTfYkqmgp9lzscG2SQ';
            $name = $data['user']['screen_name'];
            $count = $data['user']['followers_count'];
            $body = new Google_Service_Sheets_ValueRange(['values' => [[$name, $count]]]);
            $params = ['valueInputOption' => 'RAW'];
            $sheet->spreadsheets_values->update($spreadsheet_id, 'Sheet1!A:B', $body, $params);

            print $data['user']['screen_name'] . ': ' . $data['user']['followers_count'] . "\n";
        }
    }

}
