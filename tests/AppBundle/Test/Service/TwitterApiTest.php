<?php
namespace Tests\AppBundle\Test\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests for TwitterApi
 */
class TwitterApiTest extends KernelTestCase
{
    private static $container;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
         self::bootKernel();
         self::$container = static::$kernel->getContainer();
    }

    /**
     * Tests getUsersTweets function
     * Checks if returned tweets number is correct in case:
     *      user has less tweets than requested
     *      requested more tweets than twitter can return
     *      user has no tweets at all
     */
    public function testGetUsersTweetsUserHasLessTweets()
    {
        self::$container->get("generic_api")->generateTweets(50);
        $tweets = self::$container->get("twitter_api")->getUsersTweets("user", 100);
        $this->assertCount(50, $tweets);
        self::$container->get("twitter_api")->clearCursors();

        self::$container->get("generic_api")->generateTweets(250);
        $tweets = self::$container->get("twitter_api")->getUsersTweets("user", 201);
        $this->assertCount(200, $tweets);
        $tweets = self::$container->get("twitter_api")->getUsersTweets("user", 200);
        $this->assertCount(50, $tweets);

        self::$container->get("twitter_api")->clearCursors();
        self::$container->get("generic_api")->generateTweets(0);
        $tweets = self::$container->get("twitter_api")->getUsersTweets("user", 201);
        $this->assertCount(0, $tweets);
    }

    /**
     * Tests ExtractContent function
     * Checks if Extracted content is correct
     */
    public function testExtractTweetsContent()
    {
        self::$container->get("generic_api")->generateTweets(100);
        $tweets = self::$container->get("twitter_api")->getUsersTweets("user", 100);
        $content = self::$container->get("twitter_api")->ExtractContent($tweets);
        $counts = array_count_values(str_word_count($content, 1));
        $expectedValues = [
            "textinall" => 100,
            "hashtaginall" => 100,
            "textt" => 34,
            "hashtagt" => 34,
            "textf" => 20,
            "hashtagf" => 20,
            "texttt" => 10,
            "hashtagtt" => 10
        ];
        foreach ($counts as $key => $value) {
            $this->assertEquals($expectedValues[$key], $value);
        }
    }

    /**
     * Tests ExtractHashtags function
     * Checks if Extracted content is correct
     */
    public function testExtractHashtags()
    {
        self::$container->get("generic_api")->generateTweets(100);
        $tweets = self::$container->get("twitter_api")->getUsersTweets("user", 100);
        $content = self::$container->get("twitter_api")->ExtractHashtags($tweets);
        $counts = array_count_values(str_word_count($content, 1));
        $expectedValues = [
            "hashtaginall" => 100,
            "hashtagt" => 34,
            "hashtagf" => 20,
            "hashtagtt" => 10
        ];
        foreach ($counts as $key => $value) {
            $this->assertEquals($expectedValues[$key], $value);
        }
    }
}