<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\AffiliateUsers;
use App\Models\UserPermissions;
use App\Models\UserRoles;
use App\Models\Brands;
use App\Models\Categories;
use App\Models\Products;
use Validator;

class ActionController extends Controller
{
    //

    public function userRoleCreate(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'role_name'      => 'required',
                'description'    => 'nullable',
                'permission'     => 'required|array',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }

            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            DB::beginTransaction($validator);
                $user_role  = UserRoles::create(
                    ['role_name'  =>$validator->valid()['role_name'], 
                    'description' =>$validator->valid()['description'],
                    ]
                );
                $this->addUserPermission($validator->valid()['permission'],$user_role->id);
            DB::commit();
            return response(['success' => true,'data'=>  null,'message' => 'User Role Created Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }


    public function userRoleEdit(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'role_id'        => 'required|numeric',
                'role_name'      => 'required',
                'description'    => 'nullable',
                'permission'     => 'required|array',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }

            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }

            if(!$this->isRecodeExsist($validator->valid()['role_id'],'user_roles')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid User Role.'], 404);  
            }

            DB::beginTransaction($validator);
                UserRoles::find($validator->valid()['role_id'])->fill($request->all())->save();
                UserPermissions::where('role_id',$validator->valid()['role_id'])->delete();
                $this->addUserPermission($validator->valid()['permission'],$validator->valid()['role_id']);
            DB::commit();
            $data = array('user_role'=> AffiliateUsers::find($sub['sub'])->user_role ,'permissions'=>$this->getAuthUserPermissionsAsPluck($sub['sub'])) ;
            return response(['success' => true,'data'=> $data, 'message' => 'User Role Created Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }


    public function changeUserStatus(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'user_id'        => 'required|numeric',
                'status'         => 'required|numeric',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            if(!$this->isRecodeExsist($validator->valid()['user_id'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. User not found.'], 404);  
            }

            DB::beginTransaction($validator);
                $user           = AffiliateUsers::find($validator->valid()['user_id']);
                $user->status   = $validator->valid()['status'];
                $user->save();
            DB::commit();

            $data = array('username' => $user->first_name.' '.$user->last_name);
            return response(['success' => true,'data'=> $data,'message' => 'User Status Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }



    public function changeCustomerStatus(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'customer_id'    => 'required|numeric',
                'status'         => 'required|numeric',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            if(!$this->isRecodeExsist($validator->valid()['customer_id'],'customers')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Customers not found.'], 404);  
            }

            DB::beginTransaction($validator);
                $customer           = Customers::find($validator->valid()['customer_id']);
                $customer->status   = $validator->valid()['status'];
                $customer->save();
            DB::commit();

            $data = array('username' => $customer->full_name);
            return response(['success' => true,'data'=> $data,'message' => 'Customer Status Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }

    
    public function changeRoleStatus(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'role_id'    => 'required|numeric',
                'status'     => 'required|numeric',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            if(!$this->isRecodeExsist($validator->valid()['role_id'],'user_roles')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Role not found.'], 404);  
            }

            DB::beginTransaction($validator);
                $role           = UserRoles::find($validator->valid()['role_id']);
                $role->status   = $validator->valid()['status'];
                $role->save();
            DB::commit();

            $data = array('username' => $role->role_name);
            return response(['success' => true,'data'=> $data,'message' => 'Role Status Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }



    public function brandCreate(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'brand_name'     => 'required|unique:brands',
                'description'    => 'nullable',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }

            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            DB::beginTransaction($validator);
                $data = array('brand_name' =>$validator->valid()['brand_name'],'description' =>$validator->valid()['description']);
                if($this->isVendor($sub['sub'])){
                    $data['status'] = 0;
                }
                $brands  = Brands::create($data);
            DB::commit();
            return response(['success' => true,'data'=>  null,'message' => 'Brand Created Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }

    
    public function brandEdit(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'brand_id'       => 'required|numeric',
                'brand_name'     => 'required',
                'description'    => 'nullable',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$this->isRecodeExsist($validator->valid()['brand_id'],'brands')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Brand.'], 404);  
            }

            if(!$this->checkDataRelevantBrand($validator->valid()['brand_id'],'brand_name',$validator->valid()['brand_name'])){
                return response(['success' => false,'data'=> null,'message' =>'The brand name has already been taken'], 404);  
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }

            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }

            DB::beginTransaction($validator);
                if($this->isVendor($sub['sub'])){
                    $request->merge(['status' => 0]);
                }
                Brands::find($validator->valid()['brand_id'])->fill($request->all())->save();
            DB::commit();
            return response(['success' => true,'data'=>  null,'message' => 'Brand Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }



    public function changeBrandStatus(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'brand_id'    => 'required|numeric',
                'status'      => 'required|numeric',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            if(!$this->isRecodeExsist($validator->valid()['brand_id'],'brands')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Brand.'], 404);  
            }

            DB::beginTransaction($validator);
                $brand           = Brands::find($validator->valid()['brand_id']);
                $brand->status   = $validator->valid()['status'];
                $brand->save();
            DB::commit();

            $data = array('name' => $brand->brand_name);
            return response(['success' => true,'data'=> $data,'message' => 'Brand Status Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }



    public function categoryCreate(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'category_name'  => 'required|unique:categories',
                'description'    => 'nullable',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }

            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            DB::beginTransaction($validator);
                $data = array('category_name' =>$validator->valid()['category_name'],'description' =>$validator->valid()['description']);
                if($this->isVendor($sub['sub'])){
                    $data['status'] = 0;
                }
                $categories  = Categories::create($data);
            DB::commit();
            return response(['success' => true,'data'=>  null,'message' => 'Category Created Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }



    public function categoryEdit(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'category_id'    => 'required|numeric',
                'category_name'  => 'required',
                'description'    => 'nullable',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$this->isRecodeExsist($validator->valid()['category_id'],'categories')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Category.'], 404);  
            }

            if(!$this->checkDataRelevantCategory($validator->valid()['category_id'],'category_name',$validator->valid()['category_name'])){
                return response(['success' => false,'data'=> null,'message' =>'The category name has already been taken'], 404);  
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }

            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }

            DB::beginTransaction($validator);
                if($this->isVendor($sub['sub'])){
                    $request->merge(['status' => 0]);
                }
                Categories::find($validator->valid()['category_id'])->fill($request->all())->save();
            DB::commit();
            return response(['success' => true,'data'=>  null,'message' => 'Category Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }



    public function changeCategoryStatus(Request $request){
       
        try {
           
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|numeric',
                'status'      => 'required|numeric',
            ]);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);   
            }

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            if(!$this->isRecodeExsist($validator->valid()['category_id'],'categories')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Category.'], 404);  
            }

            DB::beginTransaction($validator);
                $category           = Categories::find($validator->valid()['category_id']);
                $category->status   = $validator->valid()['status'];
                $category->save();
            DB::commit();

            $data = array('name' => $category->category_name);
            return response(['success' => true,'data'=> $data,'message' => 'Category Status Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }   
    }



    /*
    |--------------------------------------------------------------------------
    | Public function /  Product Create
    |--------------------------------------------------------------------------
    */
    public function productCreate(Request $request){
        try {

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }

            $validation_array =[
                "title"             => 'required|string|between:2,100|unique:products',
                'sku'               => 'required|string|between:2,100|unique:products',
                'stock'             => 'required',
                'short_description' => 'required|string',
                'long_description'  => 'required|string',
                'weight'            => 'required',
                'price'             => 'required',
                'brand'             => 'required|numeric',
                'category'          => 'required|numeric',
                'slug'              => 'required|regex:/^[a-z0-9-]+$/',
                'meta_title'        => 'nullable',
                'meta_description'  => 'nullable',
                'cover_image'       => 'required',
            ];

            if(!$this->isVendor($sub['sub'])){
                $validation_array['vendor'] = 'required|numeric';
                $request->merge(['status' => 'approved']);
            }else{
                $request->merge(['vendor' => $sub['sub']]);
                $request->merge(['status' =>'pending']);
            }

            $customMessages = [
                'regex' => 'Slug is not valid'
            ];

            $validator = Validator::make($request->all(), $validation_array,$customMessages);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);  
            } 
            if(!$this->isRecodeExsist($validator->valid()['brand'],'brands')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Brand.'], 404);  
            }
            if(!$this->isRecodeExsist($validator->valid()['category'],'categories')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Category.'], 404);  
            }
            DB::beginTransaction();
                $create_product = Products::create($request->all());
            DB::commit();
            return response(['success' => true, 'data' => null, 'message' => 'Product Created Success'], 200);
        } 
        catch (\Throwable $e){
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Public function /  Product Edit
    |--------------------------------------------------------------------------
    */
    public function productEdit(Request $request){
        try {

            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }

            $validation_array =[
                "product_id"        => 'required|numeric',
                "title"             => 'required|string|between:2,100',
                'sku'               => 'required|string|between:2,100',
                'stock'             => 'required',
                'short_description' => 'required|string',
                'long_description'  => 'required|string',
                'weight'            => 'required',
                'price'             => 'required',
                'brand'             => 'required|numeric',
                'category'          => 'required|numeric',
                'slug'              => 'required|regex:/^[a-z0-9-]+$/',
                'meta_title'        => 'nullable',
                'meta_description'  => 'nullable',
                'cover_image'       => 'nullable',
                'image_count'       => 'required',
            ];

            if(!$this->isVendor($sub['sub'])){
                $validation_array['vendor'] = 'required|numeric';
                // $request->merge(['status' => 'approved']);
            }else{
                $request->merge(['vendor' => $sub['sub']]);
                // $request->merge(['status' =>'pending']);
            }

            $customMessages = [
                'regex' => 'Slug is not valid'
            ];

            $validator = Validator::make($request->all(), $validation_array,$customMessages);

            if($validator->fails()){
                return response(['success' => false,'data'=> null,'message' => implode(" / ",$validator->messages()->all())], 422);  
            } 
            if(!$this->isRecodeExsist($validator->valid()['brand'],'brands')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Brand.'], 404);  
            }
            if(!$this->isRecodeExsist($validator->valid()['category'],'categories')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Category.'], 404);  
            }

            if(!$this->checkDataRelevantProduct($validator->valid()['product_id'],'title',$validator->valid()['title'])){
                return response(['success' => false,'data'=> null,'message' =>'The product title has already been taken'], 404);  
            }

            if(!$this->checkDataRelevantProduct($validator->valid()['product_id'],'sku',$validator->valid()['sku'])){
                return response(['success' => false,'data'=> null,'message' =>'The product sku has already been taken'], 404);  
            }
            DB::beginTransaction();
                Products::find($validator->valid()['product_id'])->fill($request->all())->save();
            DB::commit();
            return response(['success' => true, 'data' => null, 'message' => 'Product Updated Success'], 200);
        } 
        catch (\Throwable $e){
            DB::rollback();
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }

    }







    /*
    |--------------------------------------------------------------------------
    |private function add User Permission
    |--------------------------------------------------------------------------
    */ 
    private function addUserPermission($permission ,$id){
        foreach($permission as $key => $data){  
            $insert_data = array(
                'permissions'    => $data,
                'role_id'        => $id,  
            );
            UserPermissions::create($insert_data);  
        } 
    }



}
