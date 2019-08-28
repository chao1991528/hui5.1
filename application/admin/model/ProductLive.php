<?php

namespace app\admin\model;

use think\Model;

class ProductLive extends Model
{
    protected $connection = 'product_db';
    protected $table = 'kl_live';
}
