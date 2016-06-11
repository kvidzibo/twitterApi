<?php
namespace AppBundle\Service;

use AppBundle\Exception\GenericApiException;
use Psr\Log\LoggerInterface;

/**
 * Api for making curl requests
 */
class GenericApi implements GenericApiInterface
{
    const USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

    private $logger;

    /**
     * Array of headers which will be added to request
     * for now it's only 1 header, latter if there is need we can add more
     *
     * @var boolean|array
     */
    private $authHeader = false;

    /**
     * @param LoggerInterface Logging service to log errors
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function post($url, $fields)
    {
        $ch = curl_init();
        if ($this->authHeader) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$this->authHeader]);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $response = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($statusCode > 399) {
            $this->logError($statusCode, $response, $url, $fields, 'POST');
            throw new GenericApiException("Unexpected status code", $statusCode);
        }
        if (in_array("application/json", explode(';', $contentType))) {
            $response = json_decode($response, true);
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function get($url, $fields = [])
    {
        $ch = curl_init();
        if ($this->authHeader) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$this->authHeader]);
        }
        $query = empty($fields) ? '' : '?'.http_build_query($fields);
        curl_setopt($ch, CURLOPT_URL, $url . $query);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        $response = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($statusCode > 399) {
            $this->logError($statusCode, $response, $url, $fields, 'GET');
            throw new GenericApiException("Unexpected status code", $statusCode);
        }
        if (in_array("application/json", explode(';', $contentType))) {
            $response = json_decode($response, true);
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function setBasicAuth($username, $password)
    {
        $token = urlencode($username) . ':' . urlencode($password);
        $this->authHeader = "Authorization: Basic " . base64_encode($token);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setBearerAuth($token)
    {
        $this->authHeader = "Authorization: Bearer " . $token;
        return true;
    }

    /**
     * Function to log errors if request failed
     *
     * @param integer $statusCode Response status code
     * @param string $response Response body
     * @param string $url Url where response has been sent
     * @param array $fields Data sent to $url
     * @param string $method POST or GET
     *
     * @return true
     */
    private function logError($statusCode, $response, $url, $fields, $method)
    {
        $this->logger->error(
            ' Unexpected status code!'.
            ' method: \'' . $method . '\''.
            ' code: \'' . $statusCode . '\''.
            ' response: \'' . $response . '\''.
            ' url: \'' . $url . '\''.
            ' data sent: \'' . json_encode($fields) . '\''
        );
        return true;
    }
}