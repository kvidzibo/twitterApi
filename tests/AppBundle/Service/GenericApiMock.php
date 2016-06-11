<?php
namespace Tests\AppBundle\Service;

use AppBundle\Service\GenericApiInterface;
use Tests\AppBundle\Exception\GenericApiMockException;

/**
 * Dummy class for testing
 */
class GenericApiMock implements GenericApiInterface
{
    const API_ENDPOINT = 'https://api.twitter.com';
    const AUTH_ENDPOINT = '/oauth2/token';
    const USER_TIMELINE_ENDPOINT = '/1.1/statuses/user_timeline.json';
    const ACCESS_TOKEN = 'test_access_token';
    const USERNAME = 'test_username';
    const PASSWORD = 'test_password';

    /**
     * @var array Generated tweets
     */
    private $tweets;

    /**
     * @param LoggerInterface
     */
    public function __construct()
    {
        $this->generateTweets(50);
    }

    /**
     * {@inheritdoc}
     */
    public function post($url, $fields)
    {
        switch ($url) {
            case self::API_ENDPOINT . self::AUTH_ENDPOINT:
                return ['access_token' => self::ACCESS_TOKEN, 'token_type' => 'bearer'];
                break;
            default:
                throw new GenericApiMockException($url . " endpoint is not supported by ApiMock");
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($url, $fields = [])
    {
        switch ($url) {
            case self::API_ENDPOINT . self::USER_TIMELINE_ENDPOINT:
                $silceFrom = empty($fields['max_id']) ? 0 : $fields['max_id'] + 1;
                return array_slice($this->tweets, $silceFrom, $fields['count']);
                break;
            default:
                throw new GenericApiMockException($url . " endpoint is not supported by ApiMock");
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setBasicAuth($username, $password)
    {
        if ($username != self::USERNAME or $password != self::PASSWORD) {
            throw new GenericApiMockException("Wrong username and password");
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setBearerAuth($token)
    {
        if ($token != self::ACCESS_TOKEN) {
            throw new GenericApiMockException("Wrong token");
        }
        return true;
    }

    /**
     * Updates class variable $tweets with generated tweets.
     *
     * @param integer $number Number of tweets being generated
     *
     * @return boolean
     */
    public function generateTweets($number)
    {
        $tweets = [];
        for ($i = 0; $i < $number; $i++) {
            $text = "textInAll #HashtagInAll";
            $hashtags = [];
            $hashtags[] = ['text' => 'HashtagInAll'];
            if ($i % 3 === 0) {
                $text .= " textT";
                $text .= " #hashtagT";
                $hashtags[] = ['text' => 'hashtagT'];
            }
            if ($i % 5 === 0) {
                $text .= " textF";
                $text .= " #hashtagF";
                $hashtags[] = ['text' => 'hashtagF'];
            }
            if ($i % 10 === 0) {
                $text .= " textTT";
                $text .= " #hashtagTT";
                $hashtags[] = ['text' => 'hashtagTT'];
            }
            $tweets[] = [
                'id_str' => "$i",
                'text' => $text,
                'entities' => [
                    'hashtags' => $hashtags
                ]
            ];
        }
        $this->tweets = $tweets;
        return true;
    }
}
