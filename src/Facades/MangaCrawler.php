<?php

namespace RohitMIN\MangaCrawler\Facades;

use Illuminate\Support\Facades\Facade;
use RohitMIN\MangaCrawler\MangaCrawler as MangaCrawlerMangaCrawler;

/**
 * @see \RohitMIN\MangaCrawler\MangaCrawler
 */
class MangaCrawler extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MangaCrawlerMangaCrawler::class;
    }
}
