<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function login(Request $request){

        $titleEmail = 'Email';
        $titlePassword = 'Password';

        $nameEmail = 'email';
        $namePassword = 'password';

        $valueEmail = $request->input($nameEmail);
        $valuePassword = $request->input($namePassword);

        $hasErrors = false;
        $errors = [];

        $name = $nameEmail;
        $title = $titleEmail;
        $value = $valueEmail;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . 'is required';
        }

        $name = $namePassword;
        $title = $titlePassword;
        $value = $valuePassword;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . 'is required';
        }

        if ($hasErrors){
            return response()->json([
                "done" => FALSE,
                "code" => 101,
                "msg" => "validation error",
                "data" => $errors
            ]);
        }

        if (!User::where('email',$valueEmail)->first()){
            return response()->json([
                'done' => FALSE,
                'code' => '401',
                'msg' => 'validation error',
                'data' => [
                    'email' => $titleEmail .' not registered'
                ]
            ]);
        }

        if (!User::where('email',$valueEmail)->where('active','1')->first()){
            return response()->json([
                'done' => FALSE,
                'code' => '401',
                'msg' => 'activation error',
                'data' => [
                    'active' => 'Activate Your Account'
                ]
            ]);
        }

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){

            $user = Auth::user();

            $token =  $user->createToken('NK_Xplore')->accessToken;

            return response()->json([
                'done' => TRUE,
                'code' => '401',
                'msg' => 'Successfully Login',
                'data' => [
                    'token' => $token,
                    'id' => $user->user_id
                ]
            ]);
        }
        else{
            return response()->json([
                'done' => FALSE,
                'code' => '401',
                'msg' => 'validation error',
                'data' => [
                    'email' => $titleEmail .' or '.$titlePassword.' are invalid'
                ]
            ]);


        }

    }

    public function register(Request $request)

    {
        $titleName = 'Name';
        $titleEmail = 'Email';
        $titlePassword = 'Password';
        $titleConfirmPassword = 'Confirm Password';

        $nameName = 'name';
        $nameEmail = 'email';
        $namePassword = 'password';
        $nameConfirmPassword = 'confirm_password';

        $valueName = $request->input($nameName);
        $valueEmail = $request->input($nameEmail);
        $valuePassword = $request->input($namePassword);
        $valueConfirmPassword = $request->input($nameConfirmPassword);

        $hasErrors = false;
        $errors = [];

        $name = $nameName;
        $title = $titleName;
        $value = $valueName;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif (!ctype_alpha(str_replace(' ', '', $value))){
            $hasErrors = TRUE;
            $errors[$name] = "Only alphabets are allowed for " . $title;
        }
        elseif (strlen($value) > 32){
            $hasErrors = true;
            $errors[$name] = 'Minimum 32 characters allowed for '.$title ;
        }

        $name = $nameEmail;
        $title = $titleEmail;
        $value = $valueEmail;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif (!filter_var($value,FILTER_VALIDATE_EMAIL)){
            $hasErrors = true;
            $errors[$name] = 'invalid ' . $title;
        }
        elseif (strlen($value) > 100){
            $hasErrors = true;
            $errors[$name] = 'Minimum 100 characters allowed for '.$title ;
        }

        $name = $namePassword;
        $title = $titlePassword;
        $value = $valuePassword;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif (strlen($value) < 6){
            $hasErrors = true;
            $errors[$name] = 'Minimum 6 characters allowed for '.$title ;
        }
        elseif (strlen($value) > 20){
            $hasErrors = true;
            $errors[$name] = 'Minimum 20 characters allowed for '.$title ;
        }
        $name = $nameConfirmPassword;
        $title = $titleConfirmPassword;
        $value = $valueConfirmPassword;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif($value != $valuePassword){
            $hasErrors = true;
            $errors[$name] = 'Password don\'t match';
        }

        if ($hasErrors){
            return response()->json([
                "done" => FALSE,
                "code" => 101,
                "msg" => "validation error",
                "data" => $errors
            ]);
        }

        if (User::where('email',$valueEmail)->first()){
            return response()->json([
                "done" => FALSE,
                "code" => 101,
                "msg" => "validation error",
                "data" => [
                    'email' => 'Email Already Exists'
                ]
            ]);
        }



        $input = $request->all();

        $input['user_id'] = mt_rand(100000000000000,100099999999999);

        $input['password'] = bcrypt($input['password']);

        $input['activation_code'] = str_random(32);

        $mail = new MailController();

        $subject = "Activate Your Account | NK_Xplore";

        $body = view('emails.welcome',['name'=> $valueName,'code'=>$input['activation_code']]);

        if (!$mail->Mail($valueEmail,$valueName,$subject,$body)){

            return response()->json([
                    'done' => FALSE,
                    'code' => '401',
                    'msg' => 'some error'
                ]
            );

        }

        $user = User::create($input);

        return response()->json([
                'done' => TRUE,
                'code' => '401',
                'msg' => 'Successfully Registered',
            ]
        );

    }

    public function verify($activationCode){

        if (User::where('activation_code',$activationCode)->where('active','1')->first()){
            return response()->json([
                    'done' => TRUE,
                    'code' => '401',
                    'msg' => 'Already Activated'
                ]
            );
        }

        $user = User::where('activation_code',$activationCode)->first();

        $user->active = '1';

        if(!$user->save()){
            return response()->json([
                'done' => FALSE,
                'code' => '401',
                'msg' => 'Database error occurred'
            ]);
        }
        return response()->json([
                'done' => TRUE,
                'code' => '401',
                'msg' => 'Account Successfully Activated'
            ]
        );
    }

    public function forgotPassword(Request $request){

        $nameEmail = 'email';
        $titleEmail = 'Email';
        $valueEmail = $request->input($nameEmail);

        $hasErrors = false;
        $errors = [];

        $name = $nameEmail;
        $title = $titleEmail;
        $value = $valueEmail;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif (!filter_var($value,FILTER_VALIDATE_EMAIL)){
            $hasErrors = true;
            $errors[$name] = 'invalid ' . $title;
        }
        elseif (strlen($value) > 100){
            $hasErrors = true;
            $errors[$name] = 'Minimum 100 characters allowed for '.$title ;
        }
        elseif (!User::where('email',$value)->first()){
            $hasErrors = true;
            $errors[$name] = $title . ' is not Register';
        }


        if ($hasErrors){
            return response()->json([
                "done" => FALSE,
                "code" => 101,
                "msg" => "validation error",
                "data" => $errors
            ]);
        }

        $user = User::where('email',$valueEmail)->first();

        $name = $user->name;

        $token = str_random(32);

        $subject = "Password Reset Link | NK_Xplore";

        $body = view('emails.forgot',['name'=>$name,'token'=>$token]);

        $mail = new MailController();

        if(!$mail->Mail($valueEmail,$name,$subject,$body)){

            return response()->json([
                    'done' => TRUE,
                    'code' => '401',
                    'msg' => 'Cannot send email',
                ]
            );

        }

        DB::beginTransaction();

        $id = DB::insert('
                INSERT INTO
                password_resets
                (
                user_id,
                email,
                token
                )
                VALUES (
                  ?,?,?
                );
                ',[$user->id,$valueEmail,$token]);

        DB::commit();

        if (!$id){
            return response()->json([
                    'done' => FALSE,
                    'code' => '401',
                    'msg' => 'Database error occurred',
                ]
            );
        }

        return response()->json([
                'done' => TRUE,
                'code' => '401',
                'msg' => 'Password Reset email sent Successfully',
            ]
        );

    }

    public function resetPassword(Request $request,$token){

        $namePassword = 'password';
        $nameConfirmPassword = 'confirm_password';

        $titlePassword = 'Password';
        $titleConfirmPassword = 'Confirm Password';

        $valuePassword = $request->input($namePassword);
        $valueConfirmPassword = $request->input($nameConfirmPassword);

        $hasErrors = false;
        $errors = [];

        $name = $namePassword;
        $title = $titlePassword;
        $value = $valuePassword;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif (strlen($value) < 6){
            $hasErrors = true;
            $errors[$name] = 'Minimum 6 characters allowed for '.$title ;
        }
        elseif (strlen($value) > 20){
            $hasErrors = true;
            $errors[$name] = 'Minimum 20 characters allowed for '.$title ;
        }
        $name = $nameConfirmPassword;
        $title = $titleConfirmPassword;
        $value = $valueConfirmPassword;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif($value != $valuePassword){
            $hasErrors = true;
            $errors[$name] = 'Password don\'t match';
        }

        if($token == ''){
            $hasErrors = true;
            $errors['token'] = 'Token is Required';
        }

        if ($hasErrors){
            return response()->json([
                "done" => FALSE,
                "code" => 101,
                "msg" => "validation error",
                "data" => $errors
            ]);
        }

        $row = DB::table('password_resets')->where('token',$token)->first();

        $password = bcrypt($valuePassword);

        $user = User::where('id',$row->user_id)->where('email',$row->email)->update(['password' => $password]);

        if(!$user){
            return response()->json([
                'done' => FALSE,
                'code' => '401',
                'msg' => 'Database error occurred'
            ]);
        }

        return response()->json([
                'done' => TRUE,
                'code' => '401',
                'msg' => 'Password Reset Successfully'
            ]
        );
    }

    public function changePassword(Request $request){

        $namePassword = 'password';
        $nameConfirmPassword = 'confirm_password';

        $titlePassword = 'Password';
        $titleConfirmPassword = 'Confirm Password';

        $valuePassword = $request->input($namePassword);
        $valueConfirmPassword = $request->input($nameConfirmPassword);

        $hasErrors = false;
        $errors = [];

        $name = $namePassword;
        $title = $titlePassword;
        $value = $valuePassword;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif (strlen($value) < 6){
            $hasErrors = true;
            $errors[$name] = 'Minimum 6 characters allowed for '.$title ;
        }
        elseif (strlen($value) > 20){
            $hasErrors = true;
            $errors[$name] = 'Minimum 20 characters allowed for '.$title ;
        }
        $name = $nameConfirmPassword;
        $title = $titleConfirmPassword;
        $value = $valueConfirmPassword;
        if ($value == ""){
            $hasErrors = true;
            $errors[$name] = $title . ' is required';
        }
        elseif($value != $valuePassword){
            $hasErrors = true;
            $errors[$name] = 'Password don\'t match';
        }

        if ($hasErrors){
            return response()->json([
                "done" => FALSE,
                "code" => 101,
                "msg" => "validation error",
                "data" => $errors
            ]);
        }

        $user = Auth::user();

        $password = bcrypt($valuePassword);

        $user = User::where('id',$user->id)->where('email',$user->email)->update(['password' => $password]);

        if(!$user){
            return response()->json([
                'done' => FALSE,
                'code' => '401',
                'msg' => 'Database error occurred'
            ]);
        }

        return response()->json([
                'done' => TRUE,
                'code' => '401',
                'msg' => 'Password Changed Successfully'
            ]
        );

    }

    public function profile()
    {

        $user = Auth::user();

        return response()->json([
            "done" => FALSE,
            "code" => 101,
            "msg" => "success",
            "data" => $user
        ]);

    }

}
