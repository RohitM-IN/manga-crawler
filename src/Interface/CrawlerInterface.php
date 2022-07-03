<?php

declare(strict_types=1);

namespace RohitMIN\MangaCrawler\Interface;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;

interface CrawlerInterface
{

    /**
     * Get the list of manga from the source.
     * @return Collection
     */

    public function getMangaList() : Collection;

    /**
     * Get Manga Details from the source.
     * @param string $mangaUrl
     * @return self
     */

    public function getManga(string $mangaUrl) : self;

    /**
     * Get All the chapters from the source and dispatch the job to process the chapters.
     * @return Bus
     */

    public function getAllChapters();

    /**
     * Get Specific Chapter from the source by giving the chapter id or index.
     * @param int|float|string $index
     * @return Collection
     */

    public function getChapter(int|float|string $index);


    /**
     * Get Chapter By Url from the source.
     * @param string $chapterUrl
     * @return Collection
     */

    public function getChapterByUrl(string $chapterUrl) : Collection;


}
