<?php
namespace Lepresk\MomoApi\Http;

class HttpClient {
    
    private const METHOD_GET = "GET";
    private const METHOD_POST = "POST";
    private const METHOD_PUT = "PUT";

    private static $debug = true;


    private static function getCertificateFile(): string
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = dirname(__DIR__) . $ds . 'config'. $ds . 'cacert.pem';
        
        return $path;
    }


    private function send(string $method, string $url, array $data, array $options): Response
    {
        $defaultOptions = [
            'headers' => [],
            'auth' => null
        ];
        $options = $options + $defaultOptions;
        $paramHeaders = [];
        foreach ($options['headers'] as $key => $values) {
            if(is_array($values)) {
                $values = implode(', ', $values);
            }
            $paramHeaders[] = $key . ': ' . $values;
        }

        $ch = curl_init();

        $out = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYPEER => 2,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => self::getCertificateFile(),
            CURLOPT_VERBOSE => self::$debug,
            CURLINFO_HEADER_OUT => self::$debug
        ];

        if($method !== self::METHOD_GET) {
            $out[CURLOPT_POSTFIELDS] = json_encode($data);
            $paramHeaders[] = "Content-type: application/json";
        }

        if(!empty($options['auth'])) {
            $out[CURLOPT_USERPWD] = $options['auth']['username'] . ":" . $options['auth']['password'];
        }

        switch ($method) {
            case 'GET':
                $out[CURLOPT_HTTPGET] = true;
                break;
            default:
                $out[CURLOPT_POST] = true;
                $out[CURLOPT_CUSTOMREQUEST] = $method;
                break;
        }

        $out[CURLOPT_HTTPHEADER] = $paramHeaders;

        curl_setopt_array($ch, $out);

        $responseData = curl_exec($ch);

        if ($responseData === false) {
            $errorCode = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);

            $status = 500;
            if ($errorCode === CURLE_OPERATION_TIMEOUTED) {
                $status = 504;
            }
            throw new \Exception("cURL Error ({$errorCode}) {$error}", $status);
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = trim(substr($responseData, 0, $headerSize));
        $body = substr($responseData, $headerSize);
    
        $response = new Response(explode("\r\n", $headers), $body);

        if(self::$debug) {
            $info = curl_getinfo($ch);
            file_put_contents('curl.request.json', json_encode($info, JSON_PRETTY_PRINT));
        }

        curl_close($ch);

        return $response;
    }

    /**
     * Make post request
     * 
     * @param string $url
     * @param array $data
     * @param array $options
     * 
     * @return \Lepresk\MomoApi\Http\Response
     */
    public function post(string $url, array $data = [], array $options = []): Response
    {
        $defaultOptions = [
            'headers' => [],
            'auth' => null
        ];

        $options = $options + $defaultOptions;

        return $this->send(self::METHOD_POST, $url, $data, $options);
    }

    /**
     * Make HTTP Get Request
     * 
     * @param string $url
     * @param array $options Request options
     * 
     * @return Response
     */
    public function get(string $url, array $options = []): Response
    {
        return $this->send(self::METHOD_GET, $url, [], $options);
    }
}