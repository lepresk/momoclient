<?php
namespace Lepresk\MomoApi;

use Lepresk\MomoApi\Exception\UserCreationErrorException;
use Lepresk\MomoApi\Http\HttpClient;
use UnexpectedValueException;

class MomoApiClient implements PaymentMethodInterface {

    const ENV_SANDBOX = 'sandbox';
    const ENV_PRODUCTION = 'production';

    /**
     * @var string current environnement
     */
    private static $env = self::ENV_SANDBOX;

    /**
     * @var string Account subscription key
     */
    private static $subscriptionKey;

    /**
     * @var string Api User
     */
    private static $apiUser;

    /**
     * @var string Api Key
     */
    private static $apiKey;

    /**
     * @var HttpClient
     */
    private $client;

    public function __construct()
    {
        $this->client = new HttpClient();
    }

    /**
     * Setup Momo client
     * 
     * @param string $subscriptionKey
     * @return void
     */
    public static function setup(string $subscriptionKey): void
    {
        self::$subscriptionKey = $subscriptionKey;
    }

    /**
     * Create api user and return correspon
     * 
     * @param string $apiUser User identification
     * @return array
     *  [
     *    'apiUser' => 'Api user',
     *    'apiKey' => 'Api Key'
     * ]
     */
    public static function createApiUser(string $baseHost, string $apiUser = null)
    {
        $apiUser = $apiUser ?? Util::randomGuidV4();
        $data = [];
        
        $data = ['providerCallbackHost' => $baseHost];

        $headers = [
            'Ocp-Apim-Subscription-Key' => self::$subscriptionKey,
            'X-Reference-Id' => $apiUser
        ];

        $client = new HttpClient();

        $requestOptions = [
            'headers' => $headers
        ];

        $url = self::getBaseHost() . 'v1_0/apiuser';
        
        echo "Creating api user with id : {$apiUser}\n";

        $response = $client->post($url, $data, $requestOptions);

        if(!$response->isOk()) {
            throw new UserCreationErrorException($response->getStringBody());
        }

        $url .= '/'. $apiUser . '/apikey';

        $response = $client->post($url, [], $requestOptions);
        if(!$response->isOk()) {
            throw new UserCreationErrorException($response->getStringBody());
        }

        $apiKey = $response->getJson()['apiKey'];

        return compact('apiUser', 'apiKey');
    }

    /**
     * Set Environnement
     * 
     * @param string $env
     * @return void
     */
    public static function setEnvironnement(string $env): void
    {
        $validEnv = [self::ENV_SANDBOX, self::ENV_PRODUCTION];
        
        if(!in_array($env, $validEnv)) {
            throw new UnexpectedValueException("invalid environnement specified, must be ENV_SANDBOX or ENV_PRODUCTION");
        }

        self::$env = $env;
    }

    /**
     * {@inheritdoc}
     */
    private static function getBaseHost(): string
    {
        if(self::$env=== self::ENV_SANDBOX) {
            return "https://sandbox.momodeveloper.mtn.com/";
        }

        return "https://momodeveloper.mtn.com/";
    }
}