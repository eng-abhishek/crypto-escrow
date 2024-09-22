<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Captcha extends Model
{
    protected $table = 'captchas';
  
    protected $fillable = ['title','alt','image','created_at','updated_at'];
    
}
