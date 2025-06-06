<?php

namespace App\Http\Controllers;

use App\Service\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        // Gọi service
        $result = $this->authService->loginService($request->username, $request->password);

        // Kiểm tra và trả về kết quả
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 400);
        } else {
            return response()->json($result);
        }
    }
}
