<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'from',
        'to',
        'message',
        'is_read',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    /*protected $hidden = [
        'password',
    ];*/

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'messages';

   
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
  

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function from()
    {
        return $this->hasOne(AffiliateUsers::class,'id','from');
    }

}
