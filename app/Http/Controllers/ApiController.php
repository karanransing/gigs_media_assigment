<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function register(Request $request)
    {
    	//Validate data
        $data = $request->only('firstname','lastname','email','mobile','password','age','gender','city');
        $validator = Validator::make($data, [
            'firstname' => 'required|alpha_spaces',
			'lastname' => 'required|alpha_spaces',
            'email' => 'required|email|unique:users',
			'mobile' => 'required|unique:users|digits:10',
			'age' => 'required|min:1|max:200|numeric',
			'gender'=> 'required|in:m,f,o',
			'city' => 'required|alpha_spaces',
            'password' => 'required|string|min:6|max:15'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $user = User::create([
        	'firstname' => $request->firstname,
			'lastname' => $request->lastname,
        	'email' => $request->email,
			'mobile' => $request->mobile,
			'age' => $request->age,
			'gender'=>$request->gender,
			'city' => $request->city,
        	'password' => bcrypt($request->password)
        ]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }
 
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email','password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }
 	
 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
 
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
        $user = JWTAuth::authenticate($request->token);
        return response()->json(['user' => $user]);
    }

        /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $bookid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$u_id)
    {
        $data = $request->only('firstname','lastname','mobile','age','gender','city');
        $validator = Validator::make($data, [
            'firstname' => 'required|alpha_spaces',
			'lastname' => 'required|alpha_spaces',
			'mobile' => 'required||digits:10|unique:users,mobile,'.$u_id.',u_id',
			'age' => 'required|min:1|max:200|numeric',
			'gender'=> 'required|in:m,f,o',
			'city' => 'required|alpha_spaces',
            
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, update User details
        //Request is valid, create new user
        $user = User::where('u_id',$u_id)->update([
        	'firstname' => $request->firstname,
			'lastname' => $request->lastname,
			'mobile' => $request->mobile,
			'age' => $request->age,
			'gender'=>$request->gender,
			'city' => $request->city
        ]);

        return (!$user) ?  response()->json([
            'success' => false,
            'message' => 'User Profile Not Updated Something Went Wrong',
        ], Response::HTTP_INTERNAL_SERVER_ERROR) :   
         response()->json([
            'success' => true,
            'message' => 'User Profile Updated Successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Display the User Profile.
     *
     * @param  int $userId
     * @return \Illuminate\Http\Response
     */
    public function viewprofine($u_id)
    {   
        $user = User::where('u_id',$u_id)->get();
        return (!empty($user[0])) ? response()->json([
            'success' => true,
            'message' => 'success',
            'user'=>$user
        ]) : response()->json([
            'success' => false,
            'message' => 'User Profile Not Found',
            'books'=>$user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $userId
     * @return \Illuminate\Http\Response
     */
    public function destroy($u_id)
    {
        $flag = User::where('u_id',$u_id)->delete();

        return (!$flag) ?  response()->json([
            'success' => false,
            'message' => 'User Not Deleted Something Went Wrong',
        ], Response::HTTP_INTERNAL_SERVER_ERROR) :   
         response()->json([
            'success' => true,
            'message' => 'User Deleted Successfully',
        ], Response::HTTP_OK);
    }
}
