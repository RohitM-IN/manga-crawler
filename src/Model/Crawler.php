<?php

declare(strict_types=1);

namespace RohitMIN\MangaCrawler\Model;

use Illuminate\Database\Eloquent\Model;

class Crawler extends Model
{
    public $table = 'manga_crawler';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'worker',
        'url',
        'active',
        'data'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

}
