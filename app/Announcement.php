<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Announcement extends Model
{
    use Uuids;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public static $types = [
        'info','warning','danger','success'
    ];
    private static $cacheKey = 'announcements_cache';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getAllFromCache()
    {
        if (!Cache::has(self::$cacheKey)){
            self::putAllInCache();
        }

        return Cache::get(self::$cacheKey);
    }
    public static function putAllInCache(){
        self::clearAllFromCache();
        $announcements = self::orderByDesc('created_at')->limit(10)->get();

        Cache::forever(self::$cacheKey,$announcements);
    }

    public static function clearAllFromCache()
    {
        Cache::forget(self::$cacheKey);
    }
}
