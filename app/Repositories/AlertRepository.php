<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Contracts\AlertInterface;
use DB;

class AlertRepository extends BaseRepository implements AlertInterface
{
    protected $modelName = 'App\Alert';
}
