<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $guarded = [];

    public function getKey(){
      return $this->api_key;
    }
}
