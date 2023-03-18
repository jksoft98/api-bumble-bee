<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customers;
use App\Models\AffiliateUsers;
use App\Models\UserRoles;
use App\Models\UserPermissions;
use App\Models\Message;
use App\Models\Brands;
use App\Models\Categories;
use App\Models\Products;
use Validator;
use Carbon\Carbon;

class DataController extends Controller
{
    //
    public function getAllUserRoles(Request $request){

        if(!$request->header('authorization')){
            return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
        }
        $sub  = $this->getAuthorization($request);  
        $user = AffiliateUsers::find($sub['sub']);
        if($user){
            $data = UserRoles::all();
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);
        }
        else{
            return response(['success' => false,'data'=> null,'message' => "Auth user not found.",], 404);   
        }
    }


    public function getSingleUser(Request $request){

        try {
            $validation_array =[
                'user_id'         => 'required',
            ];
            $validator = Validator::make($request->all(), $validation_array);

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
            if(!$data = AffiliateUsers::find($validator->valid()['user_id'])){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. User not found.'], 404);  
            }

            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);
            
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getAllUsers(Request $request){

        try {
        
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = AffiliateUsers::with('user_role')->get();
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);

        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    
    /*
    |--------------------------------------------------------------------------
    |public function get user permissions  as pluck
    |--------------------------------------------------------------------------
    */ 
    public function getUserPermissionsAsPluck(Request $request){
        try {
            $validation_array =[
                'role_id'         => 'required|numeric',
            ];
            $validator = Validator::make($request->all(), $validation_array);

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
            $data = UserPermissions::where('role_id',$validator->valid()['role_id'])->get()->pluck('permissions');
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Successe.'], 200);
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
        
    }


    public function getSingleUserRole(Request $request){
        try {
            $validation_array =[
                'role_id'         => 'required|numeric',
            ];
            $validator = Validator::make($request->all(), $validation_array);

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
            $data = UserRoles::where('id',$validator->valid()['role_id'])->first();
            if(!$data ){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Data not found.'], 404);  
            }
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getAllowedNotificationUserRolePluck(Request $request){

        try {
            $validation_array =[
                'message'         => 'required',
            ];
            $validator = Validator::make($request->all(), $validation_array);

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
            
            $data = UserPermissions::where('permissions','allowed-notifications')->pluck('role_id')->toArray();
            array_push($data,1);

            foreach($data as $i){
                $users = AffiliateUsers::where('user_role',$i)->get();
                foreach($users as $user){
                    $this->createMessage($sub['sub'],$user->id,$validator->valid()['message']);
                }
            }

            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getSingleUserNotification(Request $request){

        try {
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = Message::with('from:id,first_name,last_name')->where('to',$sub['sub'])->where('is_read',0)->orderBy('id', 'DESC')->get();
            $array = [];
            foreach($data as $i){
                array_push($array,['id'=> $i->id ,'username' => $i->From->first_name.' '.$i->From->last_name, 'message' =>$i->message ,'time'=> Carbon::createFromTimeStamp(strtotime($i->created_at))->diffForHumans()]);
            }
            return response(['success' => true,'data'=> ['count'=>count($data),'messages'=>$array],'message' => 'Data Found Success.'], 200);
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getAllCustomers(Request $request){

        try {
        
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = Customers::orderBy('id', 'DESC')->get();
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);

        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getSingleCustomer(Request $request){

        try {
            $validation_array =[
                'customer_id'         => 'required',
            ];
            $validator = Validator::make($request->all(), $validation_array);

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
            if(!$data = Customers::find($validator->valid()['customer_id'])){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Customer not found.'], 404);  
            }

            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);
            
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getDashboardData(Request $request){

        try {
        
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = Customers::orderBy('id', 'DESC')->get();

            $data = array(
                'user_count'        => AffiliateUsers::count(),
                'customer_count'    => Customers::count(),
            );
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);

        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getAllBrands(Request $request){

        try {
        
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = Brands::orderBy('id', 'DESC')->get();
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);

        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getAllCategories(Request $request){

        try {
        
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = Categories::orderBy('id', 'DESC')->get();
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);

        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getAllVendors(Request $request){

        try {
        
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = AffiliateUsers::where('status',1)->where('user_role',2)->orderBy('id', 'DESC')->get();
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getSingleProduct(Request $request){

        try {
            $validation_array =[
                'product_id'         => 'required',
            ];
            $validator = Validator::make($request->all(), $validation_array);

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
            if(!$data = Products::find($validator->valid()['product_id'])){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Product not found.'], 404);  
            }

            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);
            
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    public function getAllProducts(Request $request){
        try {
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = Products::with('vendor:id,first_name,last_name','brand:id,brand_name','category:id,category_name'); 
            if($this->isVendor($sub['sub'])){
                $data = $data->where('vendor', $sub['sub']);
            }
            if($request->has('status')){
                if(!in_array($request->status, ['pending','approved','disapproved'])){
                    return response(['success' => false,'data'=> null,'message' =>'Opps!. Invalid Status.'], 404); 
                }
                $data = $data->where('status', $request->status);
            }
            $data = $data->orderBy('id', 'DESC')->get();
            $coutsArray = array( 'all'=> $this->getProductCounts($sub['sub']), 
                'pending'       => $this->getProductCounts($sub['sub'],'pending'),
                'approved'      => $this->getProductCounts($sub['sub'],'approved'),
                'disapproved'   => $this->getProductCounts($sub['sub'],'disapproved')
            );
            $response = array('products'=> $data, 'counts'=> $coutsArray);
            return response(['success' => true,'data'=> $response,'message' => 'Data Found Success.'], 200);
        }
        catch (\Throwable $e){
            return response(['success' => false,'data'=> null,'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    private function getProductCounts($sub,$status=null){
        $data = DB::table('products');
        if($this->isVendor($sub)){
            $data = $data->where('vendor', $sub);
        }
        if($status!=null){
            $data = $data->where('status', $status);
        }
        $data = $data->count();
        return $data;
    }


}
