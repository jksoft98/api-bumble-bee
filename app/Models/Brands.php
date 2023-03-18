<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'brand_name',
        'description',
        'status',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'brands';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    //public $timestamps = false;

    const CREATED_AT = null;
    const UPDATED_AT = null;

}
