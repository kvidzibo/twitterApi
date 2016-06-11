<?php
namespace AppBundle\Service;

/**
 * Helper service for text formating.
 */
class ContentFormater implements ContentFormaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function removeLinks($text)
    {
        $linkPattern = "/https?:\/\/.*?(\n|$| )/";
        return preg_replace($linkPattern, "", $text);
    }

    /**
     * {@inheritdoc}
     */
    public function removeNonLetterChars($text)
    {
        $notLetterPattern = "/[^A-Za-z ]/";
        return preg_replace($notLetterPattern, " ", $text);
    }
}