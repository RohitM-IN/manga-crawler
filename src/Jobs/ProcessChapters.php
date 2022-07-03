<?php

namespace RohitMIN\MangaCrawler\Jobs;

use Faker\Core\File;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessChapters implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $worker, $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($worker,$url)
    {
        $this->onQueue('chapters');
        $this->worker = $worker;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->worker->getChapterByUrl($this->url);

        $details = $this->worker->mangaDetails;

        $chapter = $details['chapters']->where('url',$this->url)->first();

        $path = str_replace(" ", '_' ,$details['title']) . "/" . $chapter['name'] . "/";

        Storage::deleteDirectory(storage_path('tmp/' . $path));
        $images = [];

        foreach($data as $url){
            $contents = file_get_contents($url);
            $name = $path . basename($url);

            Storage::disk('temp')->put($name, $contents);
            $images[] = $name;
        }

        $process = new $this->worker->chapterListner($details,$chapter,$images);

    }
}
