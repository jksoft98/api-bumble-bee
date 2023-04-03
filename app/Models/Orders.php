<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'search_code',
        'customer',
        'vendor',
        'total_amount',
        'installment_plan',
        'loan',
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
    protected $table = 'orders';

   
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
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null;


    public function installment_plan()
    {
        return $this->hasOne(InstallmentPlan::class,'id','installment_plan');
    }

    public function vendor()
    {
        return $this->hasOne(AffiliateUsers::class,'id','vendor');
    }

    public function customer()
    {
        return $this->hasOne(Customers::class,'id','customer');
    }
}
