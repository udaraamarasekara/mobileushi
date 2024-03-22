<?php
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\CommonController; 
Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);
 
    $user = User::where('email', $request->email)->first();
 
    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
 
    return $user->createToken($request->device_name)->plainTextToken;
});

Route::middleware('auth:sanctum')->group(function () {
  Route::get('allPosts',[CommonController::class,'allPosts']);  
  Route::post('newPost',[CommonController::class,'newPost']);  
  Route::post('editPost/{id}',[CommonController::class,'editPost']);  
  Route::delete('deletePost/{id}',[CommonController::class,'deletePost']);  
  Route::post('comment/{id}',[CommonController::class,'comment']);  
  Route::post('like/{id}',[CommonController::class,'like']);  

});
Route::get('errorMsg',function(){return "login first";})->name('login');  
Route::post('register',[CommonController::class,'register']);