<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\customer_services;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Validator;

class CustomerServicesController extends Controller
{
    use GeneralTrait;
    public function Register(Request $request)
    {
        try
        {
            ///////////// validation ///////////////
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customer_services,email',
                'password' => 'required|min:8',
                'com_password'=>'required|min:8'

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }
            ////////////////////  save  ///////////////////
            if ($request->password == $request->com_password){
                customer_services::create([
                    'name'=> $request->name,
                    'email'=> $request->email,
                    'password'=>bcrypt($request->password),

                ]);
                return $this->ReturnSuccess('200','Save Successfully');
            }
            else{
                return $this->ReturnError('000',"Don't match password");
            }
        }
        catch (\Exception $ex)
        {
            return $this->ReturnError($ex->getCode(),$ex->getMessage());
        }

    }

    public function show()
    {
        try {
            $customer= customer_services::selection()->orderBy('id','desc')->get();
            return $this->ReturnData('customers',$customer,'200');
        }
        catch (\Exception $ex)
        {
            return $this->ReturnError($ex->getCode(),$ex->getMessage());
        }
    }

    public function edit($id)
    {
        try
        {
            $customer= customer_services::find($id);
            if (!$customer)
            {
                return $this->ReturnError('404','Not Found');
            }
            $customer->Selection()->where('id',$id)->get();
            return $this->ReturnData('customer',$customer,'200');
        }
        catch (\Exception $ex)
        {
            return $this->ReturnError($ex->getCode(),$ex->getMessage());
        }
    }

    public function update(Request $request ,$id)
    {
        try
        {
            //////////////// validation ////////////////////
            $rules = [
                'name' => 'required',
                'email' => 'required',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }

            /////////////////////  update  ///////////

            $customer= customer_services::find($id);
            if (!$customer)
            {
                return $this->ReturnError('404','Not Found');
            }
            $customer->where('id',$id)->update([
                'name'=> $request->name,
                'email'=> $request->email,
            ]);
            return $this->ReturnSuccess('200','Updated Successfully');
        }
        catch (\Exception $ex)
        {
            return $this->ReturnError($ex->getCode(),$ex->getMessage());
        }
    }

    public function delete($id)
    {
        try
        {
            $customer= customer_services::find($id);
            if (!$customer)
            {
                return $this->ReturnError('404','Not Found');
            }
            $customer->where('id',$id)->delete();
            return $this->ReturnSuccess('200','deleted Successfully');

        }
        catch (\Exception $ex)
        {
            return $this->ReturnError($ex->getCode(),$ex->getMessage());
        }
    }


    public function login(Request $request)
    {
        try
        {
            $rules = [
                'email' => 'required',
                'password' => 'required',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }
            ///////  login  ////
            $incremental=$request->only(['email','password']);
            $token=Auth::guard('user-api')->attempt($incremental);
            if (!$token)
            {
                return $this->ReturnError('E001','information is not correct');
            }
            $user=Auth::guard('user-api')->user();
            $user->api_token=$token;
            return $this->ReturnData('user',$user,'login successfully');

        }
        catch (\Exception $ex){
            return $this->ReturnError($ex->getCode(),$ex->getCode());
        }
    }

}
