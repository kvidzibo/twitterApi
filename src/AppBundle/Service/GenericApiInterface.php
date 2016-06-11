<?php

namespace AppBundle\Service;

/**
 * Interface implemented by GenericApi
 */
interface GenericApiInterface
{
    /**
     * Sends post requests to $url with $fields data
     *
     * @param string $url Url where request will be sent
     * @param array $fields Data which will sent to url
     *
     * @return array|string If response content type was application/json it will return array else string
     *
     * @throws GenericApiException If status code is more than 399
     */
    public function post($url, $fields);

    /**
     * Sends get requests to $url with $fields data as query string
     *
     * @param string $url Url where request will be sent
     * @param array $fields Data which will sent to url
     *
     * @return array|string If response content type was application/json it will return array else string
     *
     * @throws GenericApiException If status code is more than 399
     */
    public function get($url, $fields = []);

    /**
     * Adds basic auth header to following requests
     *
     * @param string $username Username for basic auth
     * @param string $password Password for basic auth
     */
    public function setBasicAuth($username, $password);

    /**
     * Adds bearer auth header to following requests
     *
     * @param string $token The token
     */
    public function setBearerAuth($token);
}