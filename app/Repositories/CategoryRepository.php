<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Contracts\CategoryInterface;
use DB;

class CategoryRepository extends BaseRepository implements CategoryInterface
{
    protected $modelName = 'App\Category';
}
