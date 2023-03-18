<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\AffiliateUsers;
use App\Models\Customers;
use App\Models\UserPermissions;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['adminLogin', 'customerRegister', 'adminLogout', 'userCreate', 'userEdit', 'customerEdit']]);
    }

    public function adminLogin(Request $request)
    {

        try {

            $validation_array = [
                'email' => 'required|email',
                'password' => 'required|string|regex:/^(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z\d@$!^%*#?&]{5,}$/',
            ];
            $customMessages = [
                'regex' => 'Password should be minimum 5 characters, at least one uppercase letter and one lowercase letter'
            ];

            $validator = Validator::make($request->all(), $validation_array, $customMessages);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'data' => null, 'message' => implode(" / ", $validator->messages()->all())], 422);
            }

            if (!$token = auth()->attempt($validator->validated())) {
                return response()->json(['success' => false, 'data' => null, 'message' => 'Email or password does not exsist'], 404);
            }

            if (!auth()->user()->status) {
                Auth::guard('api')->logout();
                return response()->json(['success' => false, 'data' => null, 'message' => 'Your account has been temporarily locked. Please contact the administrator!'], 404);
            }
            return $this->createNewToken($token);

        } catch (\Throwable $e) {
            return response(['success' => false, 'data' => null, 'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }

    }

    public function customerRegister(Request $request)
    {
        try {
            $validation_array = [
                "phone" => 'required|min:11|numeric|unique:customers',
                'full_name' => 'required|string|between:2,100',
                'email' => 'required|email|unique:customers',
                'address' => 'required|string|between:2,100',
                'dob' => 'required|date',
                'nic' => 'required|string',
                'password' => 'required|string|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z\d@$!^%*#?&]{5,}$/',
            ];

            $customMessages = [
                'regex' => 'Password should be minimum 5 characters, at least one uppercase letter and one lowercase letter'
            ];

            $validator = Validator::make($request->all(), $validation_array, $customMessages);

            if ($validator->fails()) {
                return response(['success' => false, 'data' => null, 'message' => implode(" / ", $validator->messages()->all())], 422);
            }

            $age = Carbon::parse($validator->valid()['dob'])->age;
            if ($age < 18) {
                return response(['success' => false, 'data' => null, 'message' => 'Must be above 18 years of age'], 422);
            }

            $customer = Customers::where('email', $validator->valid()['email'])->first();

            if (!$customer) {
                DB::beginTransaction();
                $create_customer = Customers::create($request->all());
                DB::commit();
                return response(['success' => true, 'data' => null, 'message' => 'Customer Registration Success.'], 200);
            } else {
                return response(['success' => false, 'data' => null, 'message' => 'Opps!. Customer is already exist.'], 404);
            }

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false, 'data' => null, 'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Public function / User Create
    |--------------------------------------------------------------------------
    */
    public function userCreate(Request $request)
    {
        try {
            $validation_array = [
                'first_name' => 'required|string|between:2,100',
                'last_name' => 'required|string|between:2,100',
                "phone" => 'required|min:11|numeric|unique:affiliate_users',
                'password' => 'required|string|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z\d@$!^%*#?&]{5,}$/',
                'email' => 'required|email|unique:affiliate_users',
                'user_role' => 'required|numeric',
            ];

            $customMessages = [
                'regex' => 'Password should be minimum 5 characters, at least one uppercase letter and one lowercase letter'
            ];

            $validator = Validator::make($request->all(), $validation_array, $customMessages);

            if ($validator->fails()) {
                return response(['success' => false, 'data' => null, 'message' => implode(" / ", $validator->messages()->all())], 422);
            }

            if (!$request->header('authorization')) {
                return response(['success' => false, 'data' => null, 'message' => "Opps!. token is required.",], 422);
            }

            if (!$this->isRecodeExsist($validator->valid()['user_role'], 'user_roles')) {
                return response(['success' => false, 'data' => null, 'message' => 'Opps!. Invalid User Role.'], 404);
            }
            $sub = $this->getAuthorization($request);
            if (!$this->isRecodeExsist($sub['sub'], 'affiliate_users')) {
                return response(['success' => false, 'data' => null, 'message' => 'Opps!. Auth user not found.'], 404);
            }

            DB::beginTransaction();
            $create_user = AffiliateUsers::create($request->all());
            DB::commit();
            return response(['success' => true, 'data' => null, 'message' => 'User Created Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false, 'data' => null, 'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Public function / User Edit
    |--------------------------------------------------------------------------
    */
    public function userEdit(Request $request)
    {
        //return response(['success' => false,'data'=> null,'message' =>'Opps!. User not found.'], 404);  
        try {
            $validation_array = [
                'first_name' => 'required|string|between:2,100',
                'last_name' => 'required|string|between:2,100',
                "phone" => 'required|min:11|numeric',
                'email' => 'required|email',
                'user_role' => 'required|numeric',
                'user_id' => 'required|numeric',
                'reset_password' => 'nullable',
            ];

            if ($request->has('reset_password')) {
                $validation_array['password'] = 'required|string|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z\d@$!^%*#?&]{5,}$/';
            }

            $customMessages = [
                'regex' => 'Password should be minimum 5 characters, at least one uppercase letter and one lowercase letter'
            ];

            $validator = Validator::make($request->all(), $validation_array, $customMessages);

            if ($validator->fails()) {
                return response(['success' => false, 'data' => null, 'message' => implode(" / ", $validator->messages()->all())], 422);
            }

            if (!$this->checkDataRelevantUser($validator->valid()['user_id'], 'phone', $validator->valid()['phone'])) {
                return response(['success' => false, 'data' => null, 'message' => 'The phone has already been taken'], 404);
            }

            if (!$this->checkDataRelevantUser($validator->valid()['user_id'], 'email', $validator->valid()['email'])) {
                return response(['success' => false, 'data' => null, 'message' => 'The email has already been taken'], 404);
            }

            if (!$request->header('authorization')) {
                return response(['success' => false, 'data' => null, 'message' => "Opps!. token is required.",], 422);
            }

            if (!$this->isRecodeExsist($validator->valid()['user_role'], 'user_roles')) {
                return response(['success' => false, 'data' => null, 'message' => 'Opps!. Invalid User Role.'], 404);
            }
            $sub = $this->getAuthorization($request);
            if (!$this->isRecodeExsist($sub['sub'], 'affiliate_users')) {
                return response(['success' => false, 'data' => null, 'message' => 'Opps!. Auth user not found.'], 404);
            }
            if (!$this->isRecodeExsist($validator->valid()['user_id'], 'affiliate_users')) {
                return response(['success' => false, 'data' => null, 'message' => 'Opps!. User not found.'], 404);
            }

            DB::beginTransaction();
            if ($request->has('reset_password')) {
                //$request->merge(['password' => bcrypt($validator->valid()['password'])]);
                $request->merge(['password' => $validator->valid()['password']]);
            }
            AffiliateUsers::find($validator->valid()['user_id'])->fill($request->all())->save();
            DB::commit();
            $data = array('user_role' => AffiliateUsers::find($sub['sub'])->user_role, 'permissions' => $this->getAuthUserPermissionsAsPluck($sub['sub']));
            return response(['success' => true, 'data' => $data, 'message' => 'User Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false, 'data' => null, 'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Public function / Customer Edit
    |--------------------------------------------------------------------------
    */
    public function customerEdit(Request $request)
    {
        try {
            $validation_array = [
                "phone" => 'required|min:11|numeric',
                'full_name' => 'required|string|between:2,100',
                'email' => 'required|email',
                'address' => 'required|string|between:2,100',
                'dob' => 'required|date',
                'nic' => 'required|string',
                'customer_id' => 'required|numeric',
                'reset_password' => 'nullable',
            ];

            if ($request->has('reset_password')) {
                $validation_array['password'] = 'required|string|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z\d@$!^%*#?&]{5,}$/';
            }

            $customMessages = [
                'regex' => 'Password should be minimum 5 characters, at least one uppercase letter and one lowercase letter'
            ];

            $validator = Validator::make($request->all(), $validation_array, $customMessages);

            if ($validator->fails()) {
                return response(['success' => false, 'data' => null, 'message' => implode(" / ", $validator->messages()->all())], 422);
            }

            if (!$this->checkDataRelevantCustomer($validator->valid()['customer_id'], 'phone', $validator->valid()['phone'])) {
                return response(['success' => false, 'data' => null, 'message' => 'The phone has already been taken'], 404);
            }

            if (!$this->checkDataRelevantCustomer($validator->valid()['customer_id'], 'email', $validator->valid()['email'])) {
                return response(['success' => false, 'data' => null, 'message' => 'The email has already been taken'], 404);
            }

            $age = Carbon::parse($validator->valid()['dob'])->age;
            if ($age < 18) {
                return response(['success' => false, 'data' => null, 'message' => 'Must be above 18 years of age'], 422);
            }

            if (!$request->header('authorization')) {
                return response(['success' => false, 'data' => null, 'message' => "Opps!. token is required.",], 422);
            }

            $sub = $this->getAuthorization($request);
            if (!$this->isRecodeExsist($sub['sub'], 'affiliate_users')) {
                return response(['success' => false, 'data' => null, 'message' => 'Opps!. Auth user not found.'], 404);
            }
            if (!$this->isRecodeExsist($validator->valid()['customer_id'], 'customers')) {
                return response(['success' => false, 'data' => null, 'message' => 'Opps!. Customer not found.'], 404);
            }

            DB::beginTransaction();
            if ($request->has('reset_password')) {
                $request->merge(['password' => $validator->valid()['password']]);
            }
            Customers::find($validator->valid()['customer_id'])->fill($request->all())->save();
            DB::commit();
            return response(['success' => true, 'data' => null, 'message' => 'Customer Updated Success'], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            return response(['success' => false, 'data' => null, 'message' => "Opps!. Something went wrong. Please try again later!", 'error' => $e->getMessage()], 500);
        }
    }


    protected function createNewToken($token)
    {

        $data = array(
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user(),
            'permissions' => $this->getAuthUserPermissionsAsPluck(auth()->user()->id),
        );

        return response(['success' => true, 'data' => $data, 'message' => 'Login success'], 200);

    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLogout()
    {
        //auth()->logout();
        Auth::guard('api')->logout();
        return response(['success' => true, 'message' => 'User successfully logout'], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }
}