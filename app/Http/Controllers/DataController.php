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
use App\Models\InstallmentPlan;
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
            $response=[];
            foreach($data as $item){
                $loans = DB::table('loans')->where('customer',$item->id)->where('is_settled',0)->sum('amount');
                if($loans != null){
                    $item->installment_plan = "Credit";
                    $item->used_amount = $loans;
                }else{
                    $item->installment_plan = "Not Processing";
                    $item->used_amount = 0;
                    $order = DB::table('orders')->where('customer',$item->id)->where('installment_plan',1)->first();
                    if($order){
                        $item->installment_plan = "Full Payment";
                        $item->used_amount = $order->total_amount;
                    }
                }
                $item->loan_balance = 0;
                array_push($response,$item);
            }
            return response(['success' => true,'data'=>  $response,'message' => 'Data Found Success.'], 200);

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
                'user_count'            => AffiliateUsers::count(),
                'customer_count'        => Customers::count(),
                'loan_count'            => DB::table('loans')->where('is_settled',0)->count(),
                'order_count'           => DB::table('orders')->count(),
                'sales_chart_data'      => $this->getSalesChartData(),
                'sales_item_chart_data' => $this->getSalesItemChartData(),
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
            $data = Products::with('vendor:id,first_name,last_name,business_name','brand:id,brand_name','category:id,category_name'); 
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



    public function getSearchProducts(Request $request){
        $data = Products::with('vendor:id,first_name,last_name,business_name','brand:id,brand_name','category:id,category_name')->where('status', 'approved')->where('title', 'like', '%' . $request->q . '%')->get();
        foreach($data as $key=> $i){
            $i->p_id = $i->id;
            $i->id = $key;
        }
        $array = ['success' => true,'data'=>  $data,  'total_count' => count($data) , 'message' => 'Data Found Success.'];
        return response()->json($array);
    }



    public function getAllInstallmentPlans(Request $request){

        try {
        
            if(!$request->header('authorization')){
                return response(['success' => false,'data'=> null,'message' => "Opps!. token is required.",], 422);   
            }
            $sub  = $this->getAuthorization($request);  
            if(!$this->isRecodeExsist($sub['sub'],'affiliate_users')){
                return response(['success' => false,'data'=> null,'message' =>'Opps!. Auth user not found.'], 404);  
            }
            $data = InstallmentPlan::orderBy('id', 'ASC')->get();
            return response(['success' => true,'data'=>  $data,'message' => 'Data Found Success.'], 200);

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


    private function getSalesChartData(){

        $daily = DB::table('orders')
                ->selectRaw("DATE_FORMAT(orders.created_date,'%Y-%m-%d') AS date")
                ->selectRaw("SUM(total_amount) as grand_total")
                ->selectRaw("COUNT(id) as orders")
                ->groupByRaw("DATE_FORMAT(orders.created_date,'%Y-%m-%d')")
                ->orderByRaw("DATE_FORMAT(orders.created_date,'%Y-%m-%d'),'DESC'")
                ->whereDate('orders.created_date','>=',Carbon::now()->subDays(30))
                ->get();
       

        $weekly = DB::table('orders')
                ->selectRaw("WEEK(orders.created_date) AS week")
                ->selectRaw("SUM(total_amount) as grand_total")
                ->selectRaw("COUNT(id) as orders")
                ->groupByRaw("WEEK(orders.created_date)")
                ->orderByRaw("WEEK(orders.created_date), 'DESC'")
                ->whereDate('orders.created_date','>=',Carbon::now()->subDays(60))
                ->get();
       

        $monthly = DB::table('orders')
                ->selectRaw("DATE_FORMAT(orders.created_date,'%Y / %m') AS month")
                ->selectRaw("SUM(total_amount) as grand_total")
                ->selectRaw("COUNT(id) as orders")
                ->groupByRaw("DATE_FORMAT(orders.created_date,'%Y / %m')")
                ->orderByRaw("DATE_FORMAT(orders.created_date,'%Y / %m'),'DESC'")
                ->whereDate('orders.created_date','>=',Carbon::now()->subMonths(12))
                ->get();

        return ['daily' => $daily, 'weekly' => $weekly, 'monthly' => $monthly];
    }



    private function getSalesItemChartData(){

        $lastThirtyDays = DB::table('order_items')
                        ->join('orders', 'orders.id', '=', 'order_items.order')
                        ->join('products', 'products.id', '=', 'order_items.product')
                        ->selectRaw("products.title AS product")
                        ->selectRaw("SUM(order_items.quantity) as sales_qty")
                        ->groupByRaw("products.title")
                        ->orderByRaw("SUM(order_items.quantity)")
                        ->whereDate('orders.created_date','>=',Carbon::now()->subDays(30))
                        ->limit(5)
                        ->get();

        $lastTwowellMonths = DB::table('order_items')
                        ->join('orders', 'orders.id', '=', 'order_items.order')
                        ->join('products', 'products.id', '=', 'order_items.product')
                        ->selectRaw("products.title AS product")
                        ->selectRaw("SUM(order_items.quantity) as sales_qty")
                        ->groupByRaw("products.title")
                        ->orderByRaw("SUM(order_items.quantity)")
                        ->whereDate('orders.created_date','>=',Carbon::now()->subMonths(12))
                        ->limit(5)
                        ->get();
                        
        foreach($lastThirtyDays as $key1 => $day){
            $lastThirtyDays[$key1]->color   = $this->getRgbColorCode($key1);
            $lastThirtyDays[$key1]->product = substr_replace($day->product, "", 15);

        }                  
        foreach($lastTwowellMonths as $key2 => $month){
            $lastTwowellMonths[$key2]->color = $this->getRgbColorCode($key2);
            $lastTwowellMonths[$key2]->product = substr_replace($month->product, "", 15);
        }                
                        
        return ['last_thirty_days' => $lastThirtyDays, 'last_twowell_months' => $lastTwowellMonths];                
    }


    private function getRgbColorCode($index){
        $color = 'rgb(255, 99, 132)';
        if($index == 0){
            $color = 'rgb(255, 99, 132)';
        }
        if($index == 1){
            $color = 'rgb(54, 162, 235)';
        }
        if($index == 2){
            $color = 'rgb(248, 196, 113)';
        }
        if($index == 3){
            $color = 'rgb(39, 174, 96)';
        }
        if($index == 4){
            $color = 'rgb(255, 205, 86)';
        }
        return $color;
    }

}
