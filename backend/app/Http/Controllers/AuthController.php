<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\Files;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'signup']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
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
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);
        $userData = User::create(['name' => $request->name, 'email' => $request->email, 'password' => $request->password]);
        return response()->json(["message" => "User Added", 'userData' => $userData], 200);
    }
    public function listXlsx()
    {
        $myDir = '../storage/app/generated_xlsx';
        $myFilesXlsx = array();
        $myFilesXlsx = scandir($myDir);
        rsort($myFilesXlsx);
        $myFilesXlsx = array_slice($myFilesXlsx, 0, count($myFilesXlsx) - 2);
        return $myFilesXlsx;
    }
    public function downloadXls($fileName)
    {
        return response()->download(storage_path() . '/app/upload_xls/' . $fileName);
    }
    public function downloadXlsx($fileName)
    {
        return response()->download(storage_path() . '/app/generated_xlsx/' . $fileName);
    }
    public function listXls()
    {
        $myDir = '../storage/app/upload_xls';
        $myFiles = array();
        $myFiles = scandir($myDir);
        rsort($myFiles);
        $myFiles = array_slice($myFiles, 0, count($myFiles) - 2);
        return $myFiles;
    }
    public function ListFiles()
    {
        $myDir = '../storage/app/upload_xls';
        $myFiles = array();
        $myFiles = scandir($myDir);
        rsort($myFiles);
        $myFiles = array_slice($myFiles, 0, count($myFiles) - 2);

        $myDirX = '../storage/app/generated_xlsx';
        $myFilesXlsx = array();
        $myFilesXlsx = scandir($myDirX);
        rsort($myFilesXlsx);
        $myFilesXlsx = array_slice($myFilesXlsx, 0, count($myFilesXlsx) - 2);

        $Files = array();
        $xlsLenght = count($myFiles);
        $j = 0;
        for ($i = 0; $i < $xlsLenght; $i++) {
            $num = $i + 1;
            $xls = $myDir . '/' . $myFiles[$i];
            $nameXls = pathinfo($xls);
            $xlsx = $myDirX . '/' . $myFilesXlsx[$j];
            $nameXlsx = pathinfo($xlsx);
            if ($nameXls['filename'] == $nameXlsx['filename']) {

                $fileXlsx = $myFilesXlsx[$j];
                $j++;
            } else {
                $fileXlsx = 'No Generate Yet!!';
            }

            $file = new Files();
            $file->no = $num;
            $file->xls = $myFiles[$i];
            $file->xlsx = $fileXlsx;
            array_push($Files, $file);
        }
        return response($Files);
    }
    function upload(Request $req)
    {
        $result = $req->file('file')->store('/upload_xls');
        $inputFileName = storage_path() . '/app/upload_xls/' . "AW-ATT-" . date('20y-m-d_h-i-s') . ".xls";
        rename(storage_path() . '/app/' . $result, $inputFileName);
        exec('"C:\Program Files\LibreOffice\program\soffice.exe" --convert-to csv ' . $inputFileName, $output, $r);
        $path_parts = pathinfo($inputFileName);
        rename(public_path() . '/' . $path_parts['filename'] . '.csv', storage_path() . '/app/csv/' . $path_parts['filename'] . '.csv');
        return ['result' => $result];
    }
}
