<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('claim_form');
});
//cashless
Route::post('/claim_submit',[HomeController::class,'claim_submit']);
Route::get('/dashboard',[HomeController::class,'dashboard']);
Route::get('/past_five_records',[HomeController::class,'past_five_records']);
Route::get('/auto_approve_amount_check',[HomeController::class,'auto_approve_amount_check']);
Route::get('/verification/{id}',[HomeController::class,'claim_verification']);
Route::get('/image/{image}',[HomeController::class,'image']);

//reimbursment
Route::get('/reimbursment_form',[HomeController::class,'reimbursment_form']);
Route::post('/reimbursment_submit',[HomeController::class,'reimbursment_submit']);
Route::get('/reimbursment_dashboard',[HomeController::class,'reimbursment_dashboard']);
Route::get('/approve/{id}',[HomeController::class,'approve_claim']);
Route::get('/reject/{id}',[HomeController::class,'reject_claim']);
Route::get('/manual_verification/{id}',[HomeController::class,'manual_verification']);
Route::get('/rit_approve/{id}',[HomeController::class,'approve_rit']);
Route::get('/rit_reject/{id}',[HomeController::class,'reject_rit']);

