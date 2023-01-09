<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\VerifyCodeRequest;
use Carbon\Carbon;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Interfaces\API\Services\AuthServiceInterface;

class AuthController extends BaseController
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Đăng ký tài khoản
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $inputData = $request->all();
        $inputData['uuid'] = md5(str_shuffle('abcdefghijklmnopqrstuvwxyz' . time()));

        $userData = [
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'gender' => $request->input('gender'),
            'status' => 0,
            'password' => bcrypt($request->input('password')),
            'mobile_no' => $request->input('phone'),
            'uuid' => $inputData['uuid'],
            'role_id' => 1,
            'activation_date' => Carbon::now()->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
        ];
        $hasFile = $request->hasFile('thumbnail');
        $thumbnail = $request->thumbnail;

        try {
            $result = $this->authService->handleRegister($inputData, $userData, $hasFile, $thumbnail);
            return $this->sendResponse($result, 'Send mail success !');
        } catch (\Exception$e) {
            return $this->sendError($e->getMessage(), null);
        }
    }

    /**
     * Người dùng nhấn link xác thực đăng ký tài khoản
     * @param string $uuid
     * @return mixed
     */
    public function verifyAccount($uuid)
    {
        try {
            $this->authService->handleVerifyAccount($uuid);
            return $this->sendResponse([],'Account verification successful !');
        } catch (\Exception$e) {
            return $this->sendError($e->getMessage(), null);
        }
    }

    /**
     * Xác thực OTP
     * @param mixed $userId
     * @param VerifyCodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyWithOtp($userId, VerifyCodeRequest $request)
    {
        $otp = $request->otp;
        $userId = $request->user_id;
        try {
            $this->authService->handleVerifyWithOtp($userId, $otp);
            return $this->sendResponse([],'Verification OTP successful !');
        } catch (\Exception$e) {
            return $this->sendError($e->getMessage(), null);
        }
    }
}
