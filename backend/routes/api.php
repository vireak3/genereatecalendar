<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Generate;
use GrahamCampbell\ResultType\Success;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('signup', [AuthController::class, 'signup']);
Route::post('login', [AuthController::class, 'login']);
Route::group([

    'middleware' => 'api'

], function ($router) {


    //    Route::post('logout', 'AuthController@logout');
    //    Route::post('refresh', 'AuthController@refresh');
    //    Route::post('me', 'AuthController@me');

});

Route::get('/generate/{fileName}', function ($fileName) {
    $inputFileName = "../storage/app/public/upload_xls/" . $fileName;
    $path_parts = pathinfo($inputFileName);
    $filename = "../storage/app/csv/" . $path_parts['filename'] . '.csv';
    $employee = array();
    $file = fopen($filename, 'r');
    while (($line = fgetcsv($file)) !== FALSE) {
        $employee[] = $line;
    }
    fclose($file);


    $dateComponents = $employee[1][4];
    $month = date("n", strtotime($dateComponents));
    $dateObj = DateTime::createFromFormat('!m', $month);
    $monthName = $dateObj->format('F');
    $year = date("Y", strtotime($dateComponents));
    $row = 3;
    $spreadsheet = new Spreadsheet();
    $generate = new Generate($spreadsheet);
    $sheet = $spreadsheet->getActiveSheet();
    $recs = $generate->getRecOfAnEmp($employee);
    $count = $generate->empCount($employee);

    $generate->header($monthName, $year, $employee);

    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $rowCount = 0;
    $numberDays = date('t', $firstDayOfMonth);
    for ($i = 0; $i < $count; $i++) {
        $rowCount = $generate->generateCalendar($dateComponents, $row);
        if ($numberDays == 31 && $rowCount > 5) {
            $row += 13;
        } else {
            $row += 11;
        }
    }
    $row = 3;
    $generate->generateEmployeeReport($dateComponents, $employee, $recs, $count, $rowCount, $row);

    $writer = new Xlsx($spreadsheet);
    $writer->save("../storage/app/generated_xlsx/" . $path_parts['filename'] . '.xlsx',);
    // $writer->save( $path_parts['filename'] . '.xlsx',);


    // $writer->save('../storage/app/public/upload_xls/hello world.xlsx');
    $statusSucces = array('success' => true);
    // return json_encode($statusSucces);
    return response($statusSucces, 200);
});

Route::get('/listxls', [AuthController::class, 'listXls']);
Route::get('/listxlsx', [AuthController::class, 'listXlsx']);
Route::get('/downloadxls/{fileName}', [AuthController::class, 'downloadXls']);
Route::get('/downloadxlsx/{fileName}', [AuthController::class, 'downloadXlsx']);
Route::post('/upload', [AuthController::class, 'upload']);
Route::get('/listfiles', [AuthController::class, 'ListFiles']);
