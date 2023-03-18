<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\UserPermissions;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /*
    |--------------------------------------------------------------------------
    | Protected function / get Authorization 
    |--------------------------------------------------------------------------
    */   
    protected function getAuthorization($request)
    {
        $token   = $request->header('authorization');
        $payload = JWTAuth::setToken($token)->getPayload();
        $sub     = $payload->get();
        return $sub;
    }

    /*
    |--------------------------------------------------------------------------
    | Protected function / get check recode already exsist
    |--------------------------------------------------------------------------
    */
    protected function isRecodeExsist($value,$table){
        $data = DB::table($table)->where('id',$value)->first();
        if(!$data){
            return false;
        }
        return true;
    }


    protected function checkDataRelevantUser($user_id,$filed,$value){
        $user = DB::table('affiliate_users')->where($filed,$value)->where('id',$user_id)->first();
        if($user){
            return true;
        }
        else{
            $user1 = DB::table('affiliate_users')->where($filed,$value)->first();
            if($user1){
                return false;
            }
            return true;
        }
    }

    protected function checkDataRelevantCustomer($customer_id,$filed,$value){
        $customer = DB::table('customers')->where($filed,$value)->where('id',$customer_id)->first();
        if($customer){
            return true;
        }
        else{
            $customer1 = DB::table('customers')->where($filed,$value)->first();
            if($customer1){
                return false;
            }
            return true;
        }
    }

    protected function checkDataRelevantBrand($brand_id,$filed,$value){
        $brand = DB::table('brands')->where($filed,$value)->where('id',$brand_id)->first();
        if($brand){
            return true;
        }
        else{
            $brand1 = DB::table('brands')->where($filed,$value)->first();
            if($brand1){
                return false;
            }
            return true;
        }
    }


    protected function checkDataRelevantCategory($category_id,$filed,$value){
        $category = DB::table('categories')->where($filed,$value)->where('id',$category_id)->first();
        if($category){
            return true;
        }
        else{
            $category1 = DB::table('categories')->where($filed,$value)->first();
            if($category1){
                return false;
            }
            return true;
        }
    }

    protected function checkDataRelevantProduct($product_id,$filed,$value){
        $product = DB::table('products')->where($filed,$value)->where('id',$product_id)->first();
        if($product){
            return true;
        }
        else{
            $product1 = DB::table('products')->where($filed,$value)->first();
            if($product1){
                return false;
            }
            return true;
        }
    }

    protected function getAuthUserPermissionsAsPluck($id){
       $user = DB::table('affiliate_users')->where('id',$id)->first();
       $data = UserPermissions::where('role_id',$user->user_role)->get()->pluck('permissions');
       return $data;
    }


    protected function createMessage($from,$to,$message){
        $create  = Message::create(['from'=>$from, 'to'=>$to,  'message'=>$message]);
        return $create;
    }


    /*
    |--------------------------------------------------------------------------
    | Protected function / get check is vendor
    |--------------------------------------------------------------------------
    */
    protected function isVendor($value){
        $data = DB::table('affiliate_users')->where('id',$value)->where('user_role',2)->first();
        if(!$data){
            return false;
        }
        return true;
    }


    

}
