<?php

namespace AppBundle\Service;

/**
 * interface implemented by ContentFormater
 */
interface ContentFormaterInterface
{
    /**
     * Removes links from $text
     *
     * @param string  $text
     *
     * @return string $text without links
     */
    public function removeLinks($text);

    /**
     * Removes a non letter characters from $text.
     *
     * @param string  $text
     *
     * @return string $text without non letter characters
     */
    public function removeNonLetterChars($text);
}