<?php
namespace AppBundle\Service;

use AppBundle\Exception\TwitterApiException;
use Psr\Log\LoggerInterface;

/**
 * Layer between generic api and twitter all twitter related information
 * should be stored here.
 */
class TwitterApi {

    const API_ENDPOINT = 'https://api.twitter.com';
    const AUTH_ENDPOINT = '/oauth2/token';
    const USER_TIMELINE_ENDPOINT = '/1.1/statuses/user_timeline.json';
    const MAX_TWEET_COUNT = 200;

    private $logger;
    private $contentFormater;
    private $genericApi;

    /**
     * @var string Twitter auth token
     */
    private $token;

    /**
     * @var array Used for getting more than 200 tweets (pagination)
     */
    private $cursors;


    /**
     * @param GenericApiInterface $genericApi Api for making basic requests
     * @param LoggerInterface $logger Service for error logging
     * @param ContentFormaterInterface $contentFormater Basic functions for formating text
     * @param string $apiKey Twitter api key
     * @param string $apiSecret Twitter api secret
     */
    public function __construct(
        GenericApiInterface $genericApi,
        LoggerInterface $logger,
        ContentFormaterInterface $contentFormater,
        $apiKey,
        $apiSecret
    ) {
        $this->genericApi = $genericApi;
        $this->logger = $logger;
        $this->cursors = [];
        $this->contentFormater = $contentFormater;
        $this->authenticate($apiKey, $apiSecret);
    }

    /**
     * Makes call to twitter to get user's tweets
     *
     * @param string $username Tweets will be returned from this user's timeline
     * @param integer $count Number of tweets returned (max 200)
     *
     * @return array
     */
    public function getUsersTweets($username, $count)
    {
        $endpoint = self::API_ENDPOINT.self::USER_TIMELINE_ENDPOINT;
        $count = $count > self::MAX_TWEET_COUNT ? self::MAX_TWEET_COUNT : $count;
        $parameters = ['screen_name' => $username, 'count' => $count];
        if (isset($this->cursors[$endpoint.$username])) {
            $parameters['max_id'] = $this->cursors[$endpoint.$username];
        }
        $this->genericApi->setBearerAuth($this->token);
        $response = $this->genericApi->get($endpoint, $parameters);
        if (empty($response) or !is_array($response)) {
            return [];
        }
        $lastTweet = end($response);
        $this->validateResponse($lastTweet, ['id_str']);
        $this->cursors[$endpoint.$username] = $lastTweet['id_str'];
        return $response;
    }

    /**
     * Combines text from tweets into one string
     *
     * @param array $tweets Array of tweets returned from twitter
     *
     * @return string Combined tweets text
     */
    public function extractContent($tweets)
    {
        $allContent = '';
        foreach ($tweets as $tweet) {
            $content = $this->contentFormater->removeLinks($tweet['text']);
            $content = $this->contentFormater->removeNonLetterChars($content);
            $allContent .= strtolower($content).' ';
        }
        return $allContent;
    }

    /**
     * Combines text from hashtags in tweets into one string
     *
     * @param array $tweets Array of tweets returned from twitter
     *
     * @return string Combined hashtags text
     */
    public function extractHashtags($tweets)
    {
        $allHashtags = '';
        foreach ($tweets as $tweet) {
            if (!empty($tweet['entities']['hashtags'])) {
                foreach ($tweet['entities']['hashtags'] as $hashtag) {
                    $allHashtags .= strtolower($hashtag['text']).' ';
                }
            }
        }
        return $allHashtags;
    }

    /**
     * Sets all cursors to start
     *
     * @return boolean
     */
    public function clearCursors()
    {
        $this->cursors = [];
        return true;
    }

    /**
     * This method is called in constructor
     * to authenticate application once this service is created
     *
     * @param string $apiKey Twitter api key
     * @param string $apiSecret Twitter api secret
     *
     * @return boolean
     */
    private function authenticate($apiKey, $apiSecret)
    {
        $endpoint = self::API_ENDPOINT.self::AUTH_ENDPOINT;
        $parameters = ['grant_type' => 'client_credentials'];
        $validation = [
            'access_token' => false,
            'token_type' => 'bearer'
        ];
        $this->genericApi->setBasicAuth($apiKey, $apiSecret);
        $response = $this->genericApi->post($endpoint, $parameters);
        $errors = $this->validateResponse($response, $validation);
        if ($errors) {
            $this->handleError($errors, $response, $endpoint, $parameters);
        }
        $this->token = $response['access_token'];
        return true;
    }

    /**
     * Validates response from api according to validation rules
     *
     * @param array|string $response Response from api endpoint
     * @param  array $validation Rules for validating response
     *
     * @return array Array of errors or empty array if no errors occurred
     */
    private function validateResponse($response, $validation)
    {
        $errors = [];
        if (!is_array($response)) {
            $errors[] = "wrong response format";
            return $errors;
        }
        foreach ($validation as $key => $value) {
            if (empty($response[$key])) {
                $errors[] = $key . ' was not returned';
            } elseif ($value && $response[$key] != $value) {
                $errors[] = $key . ' should be '. $value;
            }
        }
        return $errors;
    }

    /**
     * Logs errors and throws TwitterApiException to let user know about errors
     *
     * @param array $errors Errors which occurred
     * @param array|string $response Response returned from api
     * @param string $endpoint Endpoint which returned unexpected response
     * @param array $parameters Parameters which has been passed to endpoint
     *
     * @throws TwitterApiException
     */
    private function handleError($errors, $response, $endpoint, $parameters)
    {
        $response = is_array($response) ?  json_encode($response) : $response;
        $this->logger->error(
            ' Response was not valid!' .
            ' ERRORS: '. json_encode($errors) .
            ' RESPONSE: '. $response .
            ' ENDPOINT: '. $endpoint .
            ' PARAMETERS PASSED: '. json_encode($parameters)
        );
        throw new TwitterApiException('Not valid response');
    }
}