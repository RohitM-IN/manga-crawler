<?php

namespace RohitMIN\MangaCrawler;

use RohitMIN\MangaCrawler\Crawlers\FanFox;
use RohitMIN\MangaCrawler\Crawlers\ReaperScans;

class MangaCrawler
{
    public $chapterListner;

    public function __construct()
    {
        //
    }

    public function addChapterListner($worker): self
    {
        $this->chapterListner = $worker;

        return $this;
    }

    /**
     * Get List of Supported Manga Sites.
     * @param void
     * @return array
     */
    public function getMangaList()
    {
        $list = [
            // "FanFox" => array(
            //     'name' => 'FanFox',
            //     'url' => 'https://fanfox.net/',
            //     'icon' => 'https://fanfox.net/favicon.ico',
            //     'crawler' => 'RohitMIN\MangaCrawler\Crawlers\FanFox',
            // ),
            "ReaperScans" => [
                'name' => 'ReaperScans',
                'url' => 'https://reaperscans.com/',
                'icon' => 'https://reaperscans.com/favicon.ico',
                'crawler' => ReaperScans::class,
            ],
        ];

        return $list;
    }

    /**
     * Crawl Manga Site.
     * @param string $site
     * @return array
     */
    public function crawl($site)
    {
        $list = $this->getMangaList();

        $crawler = new $list[$site]['crawler']();

        return $crawler($this);
    }

    /**
     * Call ReaperScans
     * @param void
     */
    public function ReaperScans()
    {
        return new ReaperScans($this);
    }

    public function getchapterListner()
    {
        return $this->chapterListner;
    }
}
