<?php

use Symfony\Component\DomCrawler\Crawler;

if (! function_exists('getDom')) {
    /**
     */
    function getDom(string $content)
    {
        return new Crawler($content);
    }
}
