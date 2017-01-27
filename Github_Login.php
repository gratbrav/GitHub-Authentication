<?php
/**
* GitHub Login
*
* Basic GitHub Authentication
*
* @package  gratbrav
* @author   Gratbrav
* @link     https://www.gratbrav.de/
*/
class Github_Login
{
    /**
     * GitHub Configuration
     * @var array
     */
    protected $config;

    /**
     * Constructor
     * 
     * @param array $config Configuration
     */
    public function __construct($config)
    {
        $this->config = (array)$config;
    }

    /**
     * Authenticate
     *
     * @param array $GET
     */
    public function authenticate($GET)
    {
        if (!isset($GET['code'])) {
            $this->authorize();
        }

        $code = filter_var($GET['code'], FILTER_SANITIZE_STRING);

        $accessToken = $this->getAccess($code);
        $email = $this->getEmail($accessToken);

        $userData = $this->getUser($accessToken);
        $userData['email'] = $email;

        return (array)$userData;
    }

    /**
     * Start authentication
     */
    protected function authorize()
    {
        $url = 'https://github.com/login/oauth/authorize';

        $params = '?client_id=' . $this->getConfig('client_id')
           . '&redirect_uri=' . $this->getConfig('redirect_url')
           . '&scope=user';

        header('Location: ' . $url . $params);
        exit;
    }

    /**
     * Get access token
     *
     * @param string $code
     */
    protected function getAccess($code) 
    {
        $settings = [];
        $settings['post_data'] = [
            'client_id' => $this->getConfig('client_id'),
            'redirect_uri' => $this->getConfig('redirect_url'),
            'client_secret' => $this->getConfig('client_secret'),
            'code' => $code,
        ];

        $url = 'https://github.com/login/oauth/access_token';
        $result = $this->sendRequest($url, $settings);

        $r = json_decode($result, true);
        return (string)$r['access_token'];
    }

    /**
     * Get user data
     *
     * @param string $accessToken
     */
    protected function getUser($accessToken)
    {
        $settings = ['access_token' => $accessToken];
        $url = 'https://api.github.com/user?access_token=' . $accessToken;
        $result = $this->sendRequest($url, $settings);

        return json_decode($result, true);
    }

    /**
     * Get email
     *
     * @param string $accessToken
     */
    protected function getEmail($accessToken)
    {
        $settings = ['access_token' => $accessToken];
        $url = 'https://api.github.com/user/emails?access_token=' . $accessToken;
        $result = $this->sendRequest($url, $settings);

        $emails = json_decode($result, true);
        return (string)$emails[0]['email'];
    }

    /**
     * Send request
     *
     * @param string $url
     * @param array $opt
     */
    protected function sendRequest($url, $opt = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // post data
        if (isset($opt['post_data']) && is_array($opt['post_data'])) {
            curl_setopt($curl, CURLOPT_POST, sizeof($opt['post_data']));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $opt['post_data']);
        }

        // set header
        if (isset($opt['access_token']) && $opt['access_token'] !== '') {
            $header = [
                'User-Agent: ' . $this->getConfig('app_name'),
                'Accept: application/json',
                'Authorization: Bearer ' . $opt['access_token']
            ];
        } else {
            $header = [
                'Accept: application/json'
            ];
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * Get config setting from key
     * 
     * @param string $key  Config param
     * @return string
     */
    protected function getConfig($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return '';
    }
}
