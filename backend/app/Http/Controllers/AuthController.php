<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
       $this->middleware('auth:api', ['except' => ['login','signup']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Email or Password is Incorrect'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
    public function signup(Request $request){
       $validated = $request->validate([
           'name'=>'required',
           'email'=>'required|email|unique:users',
           'password'=>'required',
           'password_confirmation'=>'required|same:password'
       ]);
        $userData = User::create(['name'=>$request->name,'email'=>$request->email,'password'=>$request->password]);
        return response()->json(["message"=>"User Added",'userData'=>$userData],200);
    }
    public function listXlsx(){
        $myDir = '../storage/app/generated_xlsx';
        $myFilesXlsx = array();
        $myFilesXlsx = scandir($myDir);
        rsort($myFilesXlsx);
        $myFilesXlsx = array_slice($myFilesXlsx,0,count($myFilesXlsx)-2);
        return $myFilesXlsx;
    }
    public function downloadXls($fileName){
        return response()->download(storage_path().'/app/public/upload_xls/'.$fileName);
    }
    public function downloadXlsx($fileName){
            return response()->download(storage_path().'/app/public/generated_xlsx/'.$fileName);
        }
    public function listXls(){
        $myDir = '../storage/app/upload_xls';
        $myFiles = array();
        $myFiles = scandir($myDir);
        rsort($myFiles);
        $myFiles = array_slice($myFiles,0,count($myFiles)-2);
        return $myFiles;
    }
    function upload(Request $req){
        $result = $req->file('file')->store('/upload_xls');
        rename(storage_path().'/app/'.$result,storage_path().'/app/upload_xls/'."AW-ATT-" . date('20y-m-d_h-i-s') . ".xls");
        return ['result'=>$result];
    }
}
