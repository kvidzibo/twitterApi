<?php
namespace Tests\AppBundle\Test\Command;

use AppBundle\Command\ParseTweetsCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests for ParseTweetsCommand
 */
class ParseTweetsCommandTest extends KernelTestCase
{
    private static $command;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $application = new Application(static::$kernel);
        $application->add(new ParseTweetsCommand());
        $command = $application->find('twitter:parse:tweets');
        $command->setApplication($application);
        self::$command = $command;
        static::$kernel->getContainer()->get("generic_api")->generateTweets(300);
    }

    /**
     * Runs command
     * and checks if words where returned with correct counts and in correct order
     */
    public function testParseTweetsCommandSimpleRun()
    {
        $responseShouldBe = [
            'hashtaginall,100',
            'textinall,100',
            'hashtagt,34',
            'textt,34',
            'hashtagf,20',
            'textf,20',
            'hashtagtt,10',
            'texttt,10'
        ];

        $commandTester = new CommandTester(self::$command);
        $commandTester->execute(array(
            'command'      => self::$command->getName(),
            'username'         => 'commandTester'
        ));
        $response = $commandTester->getDisplay();
        foreach ($responseShouldBe as $line) {
            $this->assertContains($line, $response);
            $response = substr($response, strpos($response, $line));
        }
    }

    /**
     * Runs command with pagination (makes multiple requests)
     * and checks if words where returned with correct counts and in correct order
     */
    public function testParseTweetsCommandSimplePaginatedRun()
    {
        $responseShouldBe = [
            'hashtaginall,300',
            'textinall,300',
            'hashtagt,100',
            'textt,100',
            'hashtagf,60',
            'textf,60',
            'hashtagtt,30',
            'texttt,30'
        ];

        $commandTester = new CommandTester(self::$command);
        $commandTester->execute(array(
            'command' => self::$command->getName(),
            'username' => 'commandTester',
            'number' => 300
        ));
        $response = $commandTester->getDisplay();
        foreach ($responseShouldBe as $line) {
            $this->assertContains($line, $response);
            $response = substr($response, strpos($response, $line));
        }
    }

    /**
     * Runs command for hashtags
     * and checks if words where returned with correct counts and in correct order
     */
    public function testParseTweetsCommandSimpleHashtagRun()
    {
        $responseShouldBe = [
            'hashtaginall,100',
            'hashtagt,34',
            'hashtagf,20',
            'hashtagtt,10'
        ];

        $commandTester = new CommandTester(self::$command);
        $commandTester->execute(array(
            'command' => self::$command->getName(),
            'username' => 'commandTester',
            '--h'  => true
        ));
        $response = $commandTester->getDisplay();
        foreach ($responseShouldBe as $line) {
            $this->assertContains($line, $response);
            $response = substr($response, strpos($response, $line));
        }
    }

    /**
     * Runs command for hashtags with pagination (makes multiple requests)
     * and checks if words where returned with correct counts and in correct order
     */
    public function testParseTweetsCommandSimplePaginatedHashtagRun()
    {
        $responseShouldBe = [
            'hashtaginall,300',
            'hashtagt,100',
            'hashtagf,60',
            'hashtagtt,30'
        ];

        $commandTester = new CommandTester(self::$command);
        $commandTester->execute(array(
            'command' => self::$command->getName(),
            'username' => 'commandTester',
            'number' => 300,
            '--h'  => true,
        ));
        $response = $commandTester->getDisplay();
        foreach ($responseShouldBe as $line) {
            $this->assertContains($line, $response);
            $response = substr($response, strpos($response, $line));
        }
    }
}