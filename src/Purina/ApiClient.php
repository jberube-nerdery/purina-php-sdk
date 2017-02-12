<?php

namespace Ansira\Purina;

use GuzzleHttp\Client;

class ApiClient
{
    const OAUTH_URI = '/service/oauth/v2/token';
    const API_URI = '/service/api/v2/';

    /**
     * @var string $endpoint API endpoint
     */
    private $endpoint;

    /**
     * @var \GuzzleHttp\Client $guzzleClient
     */
    private $guzzleClient;

    /**
     * @var string $accessToken OAuth2 access token
     */
    private $accessToken;

    /**
     * @var string OAuth2 client ID
     */
    private $clientId;

    /**
     * @var string OAuth2 client secret
     */
    private $clientSecret;

    /**
     * @var string $cacheDirectory Cache directory
     */
    private $cacheDirectory = './cache/';

    /**
     * @var string $cacheFile Cache file
     */
    private $cacheFile;

    /**
     * Construct
     *
     * @param string $clientId Your provided Client ID
     * @param string $clientSecret Your provided Client Secret
     * @param array $options
     * array['endpoint'] Your API endpoint
     * array['cache_dir'] A writeable directory for caching the access token
     * @throws Exception
     */
    public function __construct($clientId, $clientSecret, $options = [])
    {
        if (empty($clientId) || empty($clientSecret)) {
            throw new \Exception('Missing credentials');
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        if (array_key_exists('endpoint', $options)) {
            $this->endpoint = $options['endpoint'];
        } else {
            $this->endpoint = 'https://profiles.purina.com';
        }

        $this->guzzleClient = new Client(['base_uri' => $this->endpoint]);

        if (array_key_exists('cache_dir', $options)) {
            if (!is_writable($this->cacheDirectory)) {
                throw new \Exception($this->cacheDirectory . ' is not writeable');
            }
            $this->cacheDirectory = $options['cache_dir'];
        }

        $clientIdParts = explode('_', $clientId);
        $this->cacheFile = $this->cacheDirectory . $clientIdParts[0] . '_access_token.json';

        $this->authenticate();
    }

    /**
     * Get OAuth2 access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * GET call to API
     *
     * @param string $uri Path to resource
     * @param array $params Array of query string parameters
     * @param array $headers Array of request headers
     * @return stdClass
     * @throws Exception
     */
    public function get($uri, array $params = [], array $headers = [])
    {
        return $this->makeRestfulRequest($uri, 'GET', null, $params, $headers);
    }

    /**
     * POST call to API
     *
     * @param string $uri Path to resource
     * @param array $data Array of body data
     * @param array $params Array of query string parameters
     * @param array $headers Array of request headers
     * @return stdClass
     * @throws Exception
     */
    public function post($uri, array $data, array $params = [], array $headers = [])
    {
        return $this->makeRestfulRequest($uri, 'POST', $data, $params, $headers);
    }

    /**
     * PUT call to API
     *
     * @param string $uri Path to resource
     * @param array $data Array of body data
     * @param array $params Array of query string parameters
     * @param array $headers Array of request headers
     * @return stdClass
     * @throws Exception
     */
    public function put($uri, array $data, array $params = [], array $headers = [])
    {
        return $this->makeRestfulRequest($uri, 'PUT', $data, $params, $headers);
    }

    /**
     * PATCH call to API
     *
     * @param string $uri Path to resource
     * @param array $data Array of body data
     * @param array $params Array of query string parameters
     * @param array $headers Array of request headers
     * @return stdClass
     * @throws Exception
     */
    public function patch($uri, array $data, array $params = [], array $headers = [])
    {
        return $this->makeRestfulRequest($uri, 'PATCH', $data, $params, $headers);
    }

    /**
     * DELETE call to API
     *
     * @param string $uri Path to resource
     * @param array $params Array of query string parameters
     * @param array $headers Array of request headers
     * @return stdClass
     * @throws Exception
     */
    public function delete($uri, array $params = [], array $headers = [])
    {
        return $this->makeRestfulRequest($uri, 'DELETE', null, $params, $headers);
    }

    /**
     * OPTIONS call to API
     *
     * @param string $uri Path to resource
     * @param array $params Array of query string parameters
     * @param array $headers Array of request headers
     * @return stdClass
     * @throws Exception
     */
    public function options($uri, array $params = [], array $headers = [])
    {
        return $this->makeRestfulRequest($uri, 'OPTIONS', null, $params, $headers);
    }

    /**
     * Make RESTful request
     *
     * @param string $uri
     * @param string $method
     * @param array $data
     * @param array $params
     * @param array $headers
     * @return stdClass
     * @throws Exception
     */
    protected function makeRestfulRequest($uri, $method, array $data = null, array $params = [], array $headers = [])
    {
        $options = [];
        $options['query'] = array_merge($params, [ 'access_token' => $this->getAccessToken() ]);

        if (!empty($data)) {
            $options['json'] = $data;
        }

        if (!empty($headers)) {
            array_merge($options, $headers);
        }

        $response = $this->guzzleClient->request($method, $uri, $options);

        return json_decode($response->getBody());
    }

    /**
     * Authenticate with OAuth, persist access token.
     *
     * @throws Exception
     */
    protected function authenticate()
    {
        $accessToken = $this->getTokenCache();

        if (empty($accessToken)) {
            $accessToken = $this->setTokenCache();

            if (empty($accessToken)) {
                throw new \Exception('Access token is missing');
            }
        }

        $this->accessToken = $accessToken;
    }

    /**
     * Get the access token and save to cache file.
     *
     * @return string Access token value.
     */
    protected function setTokenCache()
    {
        $response = $this->guzzleClient->request('GET', self::OAUTH_URI, [
            'query' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]
        ]);

        $json = json_decode($response->getBody());

        if (isset($json->access_token) && isset($json->expires_in)) {
            $expiresIn = time() + (int) $json->expires_in;
            $handler = fopen($this->cacheFile, 'w');
            fwrite($handler, json_encode([
                'access_token' => $json->access_token,
                'expires_in' => $expiresIn,
            ]));
            fclose($handler);
        } else {
            throw new \Exception('Failed getting access token');
        }

        return $json->access_token;
    }

    /**
     * Get the access token from the cache file
     *
     * @return string|boolean
     */
    protected function getTokenCache()
    {
        if (file_exists($this->cacheFile) && is_readable($this->cacheFile) && filesize($this->cacheFile) > 0) {
            $reader = fopen($this->cacheFile, 'r');
            $contents = fread($reader, filesize($this->cacheFile));
            fclose($reader);

            if (!empty($contents)) {
                $json = json_decode($contents);
                if (!$this->isTokenExpired($json->expires_in)) {
                    return $json->access_token;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the access token is expired.
     *
     * @param int $expires
     * @return boolean
     */
    private function isTokenExpired($expires)
    {
        // Subtract some time for latency.
        return $expires - 60 <= time();
    }

}
