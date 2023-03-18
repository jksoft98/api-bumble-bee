<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'sku',
        'stock',
        'short_description',
        'long_description',
        'weight',
        'price',
        'status',
        'brand',
        'category',
        'slug',
        'cover_image',
        'meta_title',
        'meta_description',
        'vendor',
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
    protected $table = 'products';

   
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

    public function vendor()
    {
        return $this->hasOne(AffiliateUsers::class,'id','vendor');
    }

    public function brand()
    {
        return $this->hasOne(Brands::class,'id','brand');
    }

    public function category()
    {
        return $this->hasOne(Categories::class,'id','category');
    }
}
