<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Contracts\RepetitionInterface;
use DB;

class RepetitionRepository extends BaseRepository implements RepetitionInterface
{
    protected $modelName = 'App\Repetition';
}
