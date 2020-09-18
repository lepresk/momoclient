<?php
namespace Lepresk\MomoApi\Http;

class Response {

    /**
     * List of all registered headers, as key => array of values.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Map of normalized header name to original name used to register header.
     *
     * @var array
     */
    protected $headerNames = [];

    /**
     * @var string
     */
    private $protocol = '1.1';

    /**
     * The reason phrase for the status code
     *
     * @var string
     */
    private $reasonPhrase;

    /**
     * @var mixed
     */
    private $body;
    
    /**
     * @var array|null
     */
    private $_json;

    public function __construct($headers= [], $body = '')
    {
        $this->_parseHeaders($headers);
        $this->body = $body;        
    }


    /**
     * Parses headers if necessary.
     *
     * - Decodes the status code and reasonphrase.
     * - Parses and normalizes header names + values.
     *
     * @param array $headers Headers to parse.
     * @return void
     */
    protected function _parseHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            if (substr($value, 0, 5) === 'HTTP/') {
                preg_match('/HTTP\/([\d.]+) ([0-9]+)(.*)/i', $value, $matches);
                $this->protocol = $matches[1];
                $this->code = (int)$matches[2];
                $this->reasonPhrase = trim($matches[3]);
                continue;
            }
            if (strpos($value, ':') === false) {
                continue;
            }
            list($name, $value) = explode(':', $value, 2);
            $value = trim($value);
            $name = trim($name);

            $normalized = strtolower($name);

            if (isset($this->headers[$name])) {
                $this->headers[$name][] = $value;
            } else {
                $this->headers[$name] = (array)$value;
                $this->headerNames[$normalized] = $name;
            }
        }
    }

    /**
     * Check if the response was OK
     *
     * @return bool
     */
    public function isOk()
    {
        $codes = [
            200,
            201,
            202,
            203,
            204
        ];

        return in_array($this->code, $codes);
    }

    public function getStatusCode()
    {
        return $this->code;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * Get the response body as string.
     *
     * @return string
     */
    public function getStringBody()
    {
        return $this->body;
    }

     /**
     * Get the response body as JSON decoded data.
     *
     * @return array|null
     */
    public function getJson()
    {
        return $this->_getJson();
    }

    /**
     * Get the response body as JSON decoded data.
     *
     * @return array|null
     */
    private function _getJson()
    {
        if ($this->_json) {
            return $this->_json;
        }

        return $this->_json = json_decode($this->body, true);
    }
}