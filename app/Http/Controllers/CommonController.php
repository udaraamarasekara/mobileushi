<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Validator;
use Hash;
use Otp;
use Storage;
use App\Models\Post;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\User;
class CommonController extends Controller
{
    //
    public function register(Request $request)
    {
      $validator = validator::make($request->all(),[
        'name'=>'required|string|between:2,100',
        'email'=>'required|string|max:100|email|unique:users',
        'password'=>'required|string|confirmed|min:6'
      ]);
      if($validator->fails())
      {
        return response()->json([
            'message'=>$validator->errors()->first()
        ],401);
      }
      $otp= rand(1000, 9999); 
      User::create(array_merge($validator->validated(),['password'=>Hash::make($request->password),'otp'=>$otp]));
      //$this->mail($validator->validated()['email'],$otp);
    }

    public function newPost(Request $request)
    {
      $imageName=null;
      $validator = validator::make($request->all(),[
        'title'=>'required|string|between:2,100',
        'content'=>'required|string|max:10000|',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      ]);
      if($validator->fails())
      {
        return response()->json([
            'message'=>$validator->errors()->first()
        ],401);
      }
      if(isset($validator->validated()['photo']))
      {
        $imageName = time() . '.' . $validator->validated()['photo']->extension(); 
        Storage::disk('public')->putFileAs('posts',$validator->validated()['photo'],$imageName);
      }
      Post::create(array_merge($validator->validated(),['photo'=>$imageName ,'user_id'=>auth()->user()->id]));
     
    }

    public function allPosts()
    {
      return auth()->user()->posts()->paginate(5);
    }

    public function editPost(int $id,Request $request)
    { $post=  Post::find($id);
      $imageName=null;
      $validator = validator::make($request->all(),[
        'title'=>'required|string|between:2,100',
        'content'=>'required|string|max:10000|',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      ]);
      if($validator->fails())
      {
        return response()->json([
            'message'=>$validator->errors()->first()
        ],401);
      }
      if(isset($validator->validated()['photo']))
      {
        if (file_exists(public_path('posts/'.$post->photo)))
        {
           unlink(public_path('posts/'.$post->photo));
        }

        $imageName = time() . '.' . $validator->validated()['photo']->extension(); 
        Storage::disk('public')->putFileAs('posts',$validator->validated()['photo'],$imageName);
      }

      $post->update(array_merge($validator->validated(),['photo'=>$imageName]));
    }


    public function deletePost(int $id)
    { $post=  Post::find($id);
          
      if(isset($post->photo))
      {
        if (file_exists(public_path('posts/'.$post->photo)))
        {
           unlink(public_path('posts/'.$post->photo));
        }
      }
      $post->comments()->delete();  
      $post->likes()->delete();
      $post->delete();
    }

    public function comment(int $postId,Request $request)
    {
      $validator = validator::make($request->all(),[
        'comment'=>'required|string|max:10000|',
      ]);
      if($validator->fails())
      {
        return response()->json([
            'message'=>$validator->errors()->first()
        ],401);
      }
      Post::find($postId)->comments()->create(array_merge($validator->validated(),['user_id'=>auth()->user()->id]));

    }

    public function like(int $postId)
    {
      Post::find($postId)->likes()->create(['user_id'=>auth()->user()->id]);
    }

    public function mail($address,$otp)
    {
     $mail =new PHPMailer(true);
     $mail->isSMTP();
     $mail->Host       = 'smtp.gmail.com';
     $mail->SMTPAuth   = true;
     $mail->Username   = 'pathumgimhanfake@gmail.com'; // Update with your email
     $mail->Password   = 'Hamilton1@'; // Update with your email password
     $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
     $mail->Port       = 465;

     $mail->setFrom('pathumgimhanfake@gmail.com', 'DORA OTP');
     $mail->addAddress($address);
     $mail->isHTML(true);
     $mail->Subject = 'OTP Verification';
     $mail->Body = 'Your OTP for DORA signup: ' . $otp;

     $mail->send();
    }
}
