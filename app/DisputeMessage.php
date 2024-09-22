<?php

namespace App;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Permission;

class DisputeMessage extends Model
{
    use Uuids;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;




    public function checkUserName($username=null){
    $record = User::where('username',$username)->first();
    if(Permission::where('user_id',$record->id)->count() > 0){
        return true;
    }else{
        return false;
    }
    }



    /**
     * \App\Dispute instance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dispute()
    {
        return $this -> belongsTo(\App\Dispute::class, 'dispute_id');
    }

    /**
     * Set dispute of this message
     *
     * @param Dispute $dispute
     */
    public function setDispute(Dispute $dispute)
    {
        $this -> dispute_id = $dispute -> id;
    }

    /**
     * Set author of the message
     *
     * @param User $author
     */
    public function setAuthor(User $author)
    {
        $this -> author_id = $author -> id;
    }

    /**
     * Return author of the purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function author()
    {
        return $this -> hasOne(\App\User::class, 'id', 'author_id');
    }

    /**
     * Past time since message is created
     *
     * @return string
     */
    public function getTimeAgoAttribute()
    {
        return Carbon::parse($this -> created_at) -> diffForHumans();
    }

}
