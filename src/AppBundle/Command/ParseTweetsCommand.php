<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command which parses user's tweets and returns counts of words used
 * also can be used to return hashtags used
 */
class ParseTweetsCommand extends ContainerAwareCommand
{
    const MAXIMUM_TWEET_NUMBER = 3200;
    const MAXIMUM_TWEETS_IN_ONE_REQUEST = 200;

    /**
     * @var array Variable to sum word counts from multiple requests
     */
    private $totalCounts;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('twitter:parse:tweets')
            ->setDescription('Parses user\'s tweets and return counts of words used')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Twitter account username'
            )
            ->addArgument(
                'number',
                InputArgument::OPTIONAL,
                'Number of tweets to parse',
                100
            )
            ->addOption(
                'h',
                null,
                InputOption::VALUE_NONE,
                'parse only hashtags'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $number = $input->getArgument('number');
        $onlyHashtags = $input->getOption('h');
        $twitterService = $this->getContainer()->get('twitter_api');
        //$output->writeln(date('Y-m-d H:i:s',time()). ': Starting to parse ' .$number. ' tweets for ' . $username);
        if ($number > self::MAXIMUM_TWEET_NUMBER) {
            //$output->writeln('Maximum tweets we can get is '.self::MAXIMUM_TWEET_NUMBER);
            $number = self::MAXIMUM_TWEET_NUMBER;
        }

        while ($number > 0) {
            $tweetsPerRequest = $number < self::MAXIMUM_TWEETS_IN_ONE_REQUEST ? $number : self::MAXIMUM_TWEETS_IN_ONE_REQUEST;
            $number -= $tweetsPerRequest;
            $tweets = $twitterService->getUsersTweets($username, $tweetsPerRequest);
            if (!$onlyHashtags) {
                $content = $twitterService->extractContent($tweets);
            } else {
                $content = $twitterService->extractHashTags($tweets);
            }
            $this->countKeywords($content);
            //In case if user does not have anymore tweets
            if (sizeof($tweets) < $tweetsPerRequest) {
                $number = 0;
            }
        }
        arsort($this->totalCounts);
        foreach ($this->totalCounts as $keyword => $count) {
            $output->writeln($keyword . "," . $count);
        }
        return true;
    }

    /**
     * Counts keywords and adds them to class variable $totalCounts
     *
     * @param string $string String to count keywords in
     * @return true
     */
    private function countKeywords($string)
    {
        $counts = array_count_values(str_word_count($string, 1));
        if (empty($this->totalCounts)) {
            $this->totalCounts = $counts;
        } else {
            foreach ($counts as $key => $value) {
                if (isset($this->totalCounts[$key])) {
                    $this->totalCounts[$key] += $value;
                } else {
                    $this->totalCounts[$key] = $value;
                }
            }
        }
        return true;
    }
}
