<?php

declare(strict_types=1);

namespace RohitMIN\MangaCrawler\Crawlers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use RohitMIN\MangaCrawler\Interface\CrawlerInterface;
use RohitMIN\MangaCrawler\Jobs\ProcessChapters;
use RohitMIN\MangaCrawler\MangaCrawler;
use RohitMIN\MangaCrawler\Model\Crawler as ModelCrawler;
use Symfony\Component\DomCrawler\Crawler;

class ReaperScans implements CrawlerInterface
{
    private $url;
    private $homepage;
    private $perPage;

    public $mangaDetails = [];
    public $chapterListner;

    public function __construct(MangaCrawler $crawler)
    {
        $this->homepage = "https://reaperscans.com/";
        $this->url = "https://reaperscans.com/wp-admin/admin-ajax.php";
        $this->chapterListner = $crawler->getchapterListner();
        $this->perPage = config('mangacrawler.reaperscans.perPage', 30);
    }

    public function getMangaList(): Collection
    {
        $list = [];
        $count = 0;
        while (true) {
            $response = $this->httpClient()->withHeaders($this->getHeader())
            ->withBody($this->getContent($count, $this->perPage), 'raw')->post($this->url);


            $crawler = new Crawler($response->getBody()->getContents());

            $data = $this->parseList($crawler);

            if (count($data) < 1) {
                break;
            }

            $list[] = $data;

            $count++;
        }

        return collect($list)->collapse();
    }

    public function getManga($url): self
    {
        $response = $this->httpClient()->get($url);
        $crawler = new Crawler($response->getBody()->getContents());

        $info = $crawler->filter('#nav-info > div > div.post-content > div.post-content_item')->each(function (Crawler $node, $i) {
            return [$node->filter('.summary-heading')->text() => $node->filter('.summary-content')->text()];
        });


        $details = [
            'title' => $crawler->filter('.post-title > h1')->text(),
            'description' => trim(rtrim(strip_tags($crawler->filter('.description-summary > div ')->html()))),
            'cover' => explode(" ", $crawler->filter('.summary_image > a > img ')->attr('data-srcset'))[0],
            'author' => $this->getData($info, 'Author(s)'),
            'artist' => $this->getData($info, 'Artist(s)'),
            'genre' => array_map('trim', explode(",", $this->getData($info, 'Genre(s)'))),
            'status' => $crawler->filter('.post-status > div:nth-child(2) > div.summary-content')->text(),
            'type' => $this->getData($info, 'Type'),
            'release' => $crawler->filter('.post-status > div:nth-child(1) > div.summary-content')->text(),
            'alternative' => $this->getData($info, 'Alternative'),
            'rating' => $this->getData($info, 'Rating'),
            'chapters' => $this->getChapterinfo($crawler),
        ];

        ModelCrawler::updateOrCreate([
            'title' => $details['title'],
        ], [
            'worker' => 'ReaperScans',
            'url' => $url,
            'active' => true,
            'data' => json_encode($details),
        ]);

        $this->mangaDetails = $details;

        return $this;
    }

    public function getAllChapters()
    {
        $chapters = [];
        foreach ($this->mangaDetails['chapters'] as $chapter) {
            $chapters[] = new ProcessChapters($this, $chapter['url']);
        }

        $bus = Bus::batch($chapters)->onQueue('chapters')->dispatch();

        return $bus;
    }

    public function getChapter($index)
    {
        $chapter = $this->mangaDetails['chapters']->where('id', $index)->first();

        return $this->getChapterByUrl($chapter['url']);
    }

    public function getChapterByUrl($url): Collection
    {
        try {
            $response = $this->httpClient()->get($url);
            $crawler = new Crawler($response->getBody()->getContents());
            $images = $crawler->filter('.reading-content > div.page-break > img')->each(function (Crawler $node, $i) {
                return trim($node->attr('data-src'));
            });

            return collect($images);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getDetails(): array
    {
        return $this->mangaDetails;
    }

    public function httpClient()
    {
        return Http::withOptions([
            'verify' => false,
        ])->retry(3, 2000);
    }

    /**
     * Private Functions for Crawler
     */
    private function getData($array, $key)
    {
        foreach ($array as $data) {
            if (isset($data[$key])) {
                return $data[$key];
            }
        }

        return null;
    }

    private function getChapterinfo($crawler)
    {
        $chapters = $crawler->filter('ul.main > li')->each(function (Crawler $node, $i) {
            return [
                'id' => explode(" ", $node->filter('.chapter-link > a > p')->text())[1],
                'name' => $node->filter('.chapter-link > a > p')->text(),
                'url' => $node->filter('.chapter-link > a')->attr('href'),
                'time' => Carbon::parse($node->filter('.chapter-link > a > span')->text())->format('Y-m-d H:i:s'),
            ];
        });

        return collect($chapters)->reverse();
    }

    private function getContent($page, $perPage = 100)
    {
        return "action=madara_load_more&page=$page&template=madara-core%2Fcontent%2Fcontent-archive&vars%5Borderby%5D=meta_value_num&vars%5Bpaged%5D=1&vars%5Btimerange%5D=&vars%5Bposts_per_page%5D=$perPage&vars%5Btax_query%5D%5Brelation%5D=OR&vars%5Bmeta_query%5D%5B0%5D%5B0%5D%5Bkey%5D=_wp_manga_chapter_type&vars%5Bmeta_query%5D%5B0%5D%5B0%5D%5Bvalue%5D=manga&vars%5Bmeta_query%5D%5B0%5D%5Brelation%5D=AND&vars%5Bmeta_query%5D%5Brelation%5D=OR&vars%5Bpost_type%5D=wp-manga&vars%5Bpost_status%5D=publish&vars%5Bmeta_key%5D=_latest_update&vars%5Border%5D=desc&vars%5Bsidebar%5D=full&vars%5Bmanga_archives_item_layout%5D=big_thumbnail";
    }

    private function getHeader(): array
    {
        return [
            'content-type' => ' application/x-www-form-urlencoded; charset=UTF-8',
        ];
    }

    private function parseList($node)
    {
        $data = $node->filter('.page-item-detail')->each(function (Crawler $node, $i) {
            return [
                'title' => $node->filter('.post-title')->text(),
                'url' => $node->filter('.post-title > h3 > a')->attr('href'),
                'chapter' => collect($node->filter('.list-chapter > div.chapter-item')->each(function (Crawler $node, $i) {
                    return [
                        'title' => $node->filter('.chapter')->text(),
                        'url' => $node->filter('.chapter > a')->attr('href'),
                        'time' => Carbon::parse($node->filter('.post-on')->text())->format('Y-m-d H:i:s'),
                    ];
                })),
            ];
        });

        return $data;
    }
}
