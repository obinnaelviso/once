<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\User;
use App\Bic;
use App\Inside;
use App\StudentInfo;
use App\Post;
use App\Facilitator;
use App\citedkpi;
use App\lecturernote;
use App\ctes;
use App\studentlist;
use App\Certificate;
use App\bookinfo;
use App\admindocument;
use App\borangpemetaan;
use App\Intech;
use App\Intechpro;
use App\Inventor;
use App\Mail\Booking;
use App\Mail\InsideRegister;
use App\Theme;
use App\Module;
use App\FacilitatorComment;

use Auth;

class AdminController extends Controller
{


public function myregister(Request $request){
  $this->userValidator($request->all())->validate();
  $user = new User;
  $user->firstname = $request->firstname;
  $user->lastname = $request->lastname;
  $user->usertype = $request->usertype;
  $user->idnum = $request->idnum;
  $user->program = $request->program;
  $user->email = $request->email;
  $user->password = $request->password;
  $user->question = $request->question;
  $user->answer = $request->answer;
  $user->save();
  if($user->usertype == 0) {
    $student_info = new StudentInfo;
    $user->student_info()->save($student_info);
  }
  return view('registersuccessful');
}



  public function mylogin(Request $request){

    $rules = array(

    'email' => 'required',
    'password' => 'required|min:8',
    );


    $feedbackmsg = array(
      'email.required' => 'Please provide a valid email address',
      'password.required' => 'Password is incorrect',
    );




    $validator = Validator::make($request->all(),$rules,$feedbackmsg);

    if($validator->fails()){
      return back()->withErrors($validator);
      return;
    }



   $user = User::where('email',$request->email)->where('password',$request->password)->first();
   $bic = Bic::where('username',$request->email)->where('password',$request->password)->first();
   $facilitator = Facilitator::where('username',$request->email)->where('password',$request->password)->first();

   if($user == null){
     if($bic == null) {
        if($facilitator == null)
             return back()->withErrors(['Invalid email and/or password']);
        else {
          Auth::guard('facilitator')->login($facilitator);
          return redirect()->route('facilitatorDashboard');
        }
      }
      else {
        Auth::guard('bic')->login($bic);
        return redirect()->route('bicDashboard');
      }
   }else{
     //rediredt userpage


     Auth::login($user);
//0 == students
//1 == lecturers
//2  == Visitor
//3 == Admin


     if($user->usertype == 0){
       //redirect to student
       return redirect()->route('student.index');
     }else if($user->usertype == 1){
         //redirect to lecturers
         return redirect()->intended('lecturerdashboard');
       }else if($user->usertype == 2){
         //redirect to public
          return redirect()->intended('stationhome');
       }else if($user->usertype == 3){
           //redirect to admin
  return redirect()->intended('admin_dashboard');
       }else{
         //redirect unauthorized
       }





     return;
   }

  }





public function getcitied() {

  $allkpi = Citedkpi::all();

  return view('dashboard.admin',compact('allkpi'));
}
  public function addcitedkpi(Request $request){


    //echo json_encode($request->all());
  $rules = array(
  'category' => 'required',
  'kpi' => 'required',
  'number' => 'required|numeric',
  'description' => 'required'
  );



  $feedbackmsg = array(
    'category.required' => 'Please Select Category',
    'category.alpha' => 'Please Select Category',
    'kpi.required' => 'Please Select KPI',
    'kpi.alpha' => 'Please Select KPI',
    'number.required' => 'Please enter number',
    'number.numeric' => 'Please enter number',
    'description.required' => 'Enter a valid Description'

    );

  $validator = Validator::make($request->all(),$rules,$feedbackmsg);

  if($validator->fails()){
    return back()->withErrors($validator);
    return;
  }


  $cited = new Citedkpi();
  $cited->category = $request->category;
  $cited->kpi = $request->kpi;
  $cited->number = $request->number;
  $cited->description = $request->description;
  $cited->save();
    return redirect()->intended('admin');

}
public function getlecturer(){
$userid = Auth::user()->id;
  $alllecturer = Lecturernote::where('userid',$userid)->get();
    return view('dashboard.lecturernote',compact('alllecturer'));
}

public function getlecturer2(){
$userid = Auth::user()->id;
$alllecturer = Lecturernote::where('userid',Auth::user()->id)->get();
    return view('dashboard.lecturernotelecturer',compact('alllecturer'));
}

public function getstationhome(){

  $allbooks = bookinfo::all();
    return view('station.index',compact('allbooks'));
}


public function addlecturernote(Request $request){
  //echo json_encode($request->all());
$rules = array(
'lecturer' => 'required',
'document' => 'required',
'description' => 'required',
'lecturefile' => 'required',
'userid' => 'required',

);

$feedbackmsg = array(
  'lecturer.required' => 'Please select Lecturer',
  'document.required' => 'Please select Document type',
  'description.required' => 'Enter Description',
  'lecturefile.required' => 'Kindly attach note/document file',
'userid.required' => 'Session timed out, please login',

);
$validator = Validator::make($request->all(),$rules,$feedbackmsg);

if($validator->fails()){
  return back()->withErrors($validator);
  return;
}

      $file = $request->file('lecturefile');
      //Move Uploaded File
      $destinationPath = storage_path(). '/public/uploads/lecturefile';
      if($file == null){
          echo "error file is empty";
        return;
      }

      $booklink =  storage_path().'/public/uploads/lecturefile/'.str_replace(' ', '_', $file->getClientOriginalName());
      $file->storeAs('public/uploads/lecturefile',str_replace(' ', '_', $file->getClientOriginalName()));

  $lecturern = new Lecturernote();
  $lecturern->lecturer = $request->lecturer;
  $lecturern->course = "";
  $lecturern->document = $request->document;
  $lecturern->description = $request->description;
  $lecturern->lecturefile = $booklink;
    $lecturern->userid = $request->userid;
  $lecturern->save();
    return redirect()->intended('lecturernote');

}

public function addlecturernotelecturer(Request $request){
  //echo json_encode($request->all());
$rules = array(
  'session' => 'required',
  'semester' => 'required',
'course' => 'required',
'document' => 'required',
'description' => 'required',
'lecturefile' => 'required',
'userid' => 'required',

);

$feedbackmsg = array(
  'session.required' => 'Please select Session',
  'semester.required' => 'Please select Semester',
  'course.required' => 'Please select Course',
  'document.required' => 'Please select Document type',
  'description.required' => 'Enter Description',
  'lecturefile.required' => 'Kindly attach note/document file',
  'userid.required' => 'Session timed out, please login',

);
$validator = Validator::make($request->all(),$rules,$feedbackmsg);

if($validator->fails()){
  return back()->withErrors($validator);
  return;
}

$file = $request->file('lecturefile');
//Move Uploaded File
$destinationPath = storage_path(). '/public/uploads/lecturefile';
if($file == null){
    echo "error file is empty";
  return;
}

$booklink =  storage_path().'/public/uploads/lecturefile/'.str_replace(' ', '_', $file->getClientOriginalName());
$file->storeAs('public/uploads/lecturefile',str_replace(' ', '_', $file->getClientOriginalName()));

  $lecturern = new lecturernote();
  $lecturern->lecturer = "";
    $lecturern->session = $request->session;
      $lecturern->semester = $request->semester;
  $lecturern->course = $request->course;
  $lecturern->document = $request->document;
  $lecturern->description = $request->description;
    $lecturern->lecturefile = $booklink;
      $lecturern->userid = $request->userid;
  $lecturern->save();
    return redirect()->intended('lecturernotelecturer');

}

public function logout()
{
  if(Auth::guard('web')->check())
    Auth::guard('web')->logout();
  elseif(Auth::guard('bic')->check())
    Auth::guard('bic')->logout();
  else
    Auth::guard('facilitator')->logout();
  return redirect()->intended('home');
}





public function question(Request $request){

  $rules = array(

  'answer' => 'required',

  );

  $feedbackmsg = array(
    'answer.required' => 'Please provide a valid security answer',
  );


  $validator = Validator::make($request->all(),$rules,$feedbackmsg);

  if($validator->fails()){
    return back()->withErrors($validator);
    return;
  }

}


public function enteremailprocess(Request $request){

  $rules = array(

  'email' => 'required',

  );

  $feedbackmsg = array(
    'email.required' => 'Please enter email',
  );


  $validator = Validator::make($request->all(),$rules,$feedbackmsg);

  if($validator->fails()){
    return back()->withErrors($validator);
    return;
  }

  $user = User::where('email',$request->email)->first();
  if($user == null){
    //redirect failed

      return back()->withErrors(['Email Address not found']);
  }else{
    //return view with the security question from the user modal
    return view('question')->with('securityquestion', $user->question)->with('email', $user->email);
     }



}

public function securityanswer(Request $request){

  $rules = array(

  'answer' => 'required',

  );

  $feedbackmsg = array(
    'answer.required' => 'Please enter security answer',
  );


  $validator = Validator::make($request->all(),$rules,$feedbackmsg);

  if($validator->fails()){
    return back()->withErrors($validator);
    return;
  }


  $user = User::where('email',$request->email)->first();
  if($user == null){
    //redirect failed
      return back()->withErrors(['Error reset failed']);
  }else{


    if(strtolower($request->answer) === strtolower($user->answer)){
        return view('resetpassword')->with('email', $user->email);
    }else{
        return back()->withErrors(['Security answer is invalid']);
      //return view with the security question from the user modal

    }

     }



}

public function resetpassword(Request $request){

  $rules = array(

    'password' => 'required|alphaNum|min:8',
    'confirmpassword' => 'same:password',

  );

  $feedbackmsg = array(
    'password.same' => 'Password does not match',
    'confirmpassword.same' => 'Password does not match',
  );


  $validator = Validator::make($request->all(),$rules,$feedbackmsg);

  if($validator->fails()){
    return back()->withErrors($validator);
    return;
  }


  $user = User::where("email", $request->email)->first();
  if($user == null){
    //redirect failed
      return back()->withErrors(['user account error']);
  }else{
$user->password = $request->password;
$user->save();
  return view('resetsuccessful');


     }
}

public function bookinfo2() {
  $alllecturer = book::all();
    return view('dashboard.addbook',compact('addbook'));
}

public function registerBicsForm() {
    $themes = Theme::all();
    return view('register-bics', compact('themes'));
}

 public function registerInsideForm() {
    return view('registerinside');
  }

  public function registerInside(Request $request) {
    $this->insideValidator($request->all())->validate();
    $user = Inside::create($request->all());
    Mail::to('notifications@citedum.com')->send(new InsideRegister($request, 'admin'));
    Mail::to($request->email)->send(new InsideRegister($request, 'user'));
    Auth::guard('inside')->login($user);
    return "Registration Successful!!!";
  }

public function registerBics(Request $request) {
    $this->bicValidator($request->all())->validate();
    if($request->usertype == "bic") {
      $user = Bic::create($request->all());
      Auth::guard('bic')->login($user);
    }
    else {
      $user = Facilitator::create($request->all());
      Auth::guard('facilitator')->login($user);
      return redirect()->route('facilitatorDashboard');
    }

    return redirect()->route('bicDashboard');
}

public function bicDashboard() {
  $user = Auth::guard('bic')->user();
  $facilitators = $user->theme->facilitators;
  return view('station.bic-dashboard', compact('user', 'facilitators'));
}

public function facilitatorDashboard() {
  $user = Auth::guard('facilitator')->user();
  $bics = $user->theme->bics;
  return view('station.facilitator-dashboard', compact('user', 'bics'));
}

public function changePasswordPage() {
  $user = null;
  $isFacilitator = Auth::guard('facilitator')->check();
  if($isFacilitator)
    $user = Auth::guard('facilitator')->user();
  else
    $user = Auth::guard('bic')->user();
  return view('dashboard.change-password', compact('user'));
}

public function changePassword(Request $request) {
  $user = null;
  $isFacilitator = Auth::guard('facilitator')->check();
  if($isFacilitator)
    $user = Auth::guard('facilitator')->user();
  else
    $user = Auth::guard('bic')->user();

  $this->validate(request(), [
      'old_password' => 'required|min:8',
      'password' => 'required|min:8|confirmed'
  ]);

  if($user->password === $request->old_password) {
      $user->password = $request->password;
      $user->save();
      
      session()->flash('success', 'Password changed successfully!');
      return redirect()->route('changePasswordPage');       
  }
  session()->flash('error','Wrong Password. Please try again!');
  return redirect()->back();
}

public function uploadModule(Post $post, Request $request) {
  $user = null;
  $isFacilitator = Auth::guard('facilitator')->check();
  if($isFacilitator)
    $user = Auth::guard('facilitator')->user();
  else
    $user = Auth::guard('bic')->user();
  if($request->hasFile('module')) {
    $module_name = $request->file('module')->getClientOriginalName();
    $file_url = $request->file('module')->storeAs('/public/uploads/modules', $module_name);
    $module_url = str_replace("public/", "", $file_url);
    $module = new Module;
    $module->name = $module_name;
    $module->user = $user->first_name.' '.$user->last_name;
    $module->description = $request->description;
    $module->url = $file_url;
    $post->modules()->save($module);
  }
  return redirect()->route('viewPost', $post->id)->with('success', 'File Uploaded Successfully!!!');
}

public function addFacilitatorComment(Post $post, Request $request) {
  $user = null;
  $isFacilitator = Auth::guard('facilitator')->check();
  if($isFacilitator)
    $user = Auth::guard('facilitator')->user();
  else
    $user = Auth::guard('bic')->user();

  $comment = new FacilitatorComment;
  $comment->post_id = $post->id;
  $comment->username = $user->username;
  $comment->usertype = $isFacilitator ? 'f' : 'u';
  $comment->message = $request->message;
  $comment->save();

  return redirect()->route('viewPost', $post->id)->with('successComment', 'Comment submitted successfully!!!');
}

public function addbookprocess2(Request $request){
  //echo json_encode($request->all());
$rules = array(
'bookcategory' => 'required',
'bookname' => 'required',
'bookauthor' => 'required',
'bookprice' => 'required',
'bookdesc' => 'required',
'bookyear' => 'required',
'bookfile' => 'required',
'coverimage' => 'required',
);

$feedbackmsg = array(
  'bookcategory.required' => 'Please select Product Category',
  'bookname.required' => 'Please enter Product Name',
  'bookauthor.required' => 'Please enter Product Author',
  'bookprice.required' => 'Please select Product Price',
  'bookdesc.required' => 'Please enter Product Description',
  'bookyear.required' => 'Please enter Product Year',
    'bookfile.required' => 'Please add the Product file',
    'coverimage.required' => 'Please add the Cover Image',
);

$validator = Validator::make($request->all(),$rules,$feedbackmsg);

if($validator->fails()){
  return back()->withErrors($validator);
  return;
}


      $file = $request->file('bookfile');
        $coverimage = $request->file('coverimage');
   /*
      //Display File Name
      echo 'File Name: '.$file->getClientOriginalName();
      echo '<br>';

      //Display File Extension
      echo 'File Extension: '.$file->getClientOriginalExtension();
      echo '<br>';

      //Display File Real Path
      echo 'File Real Path: '.$file->getRealPath();
      echo '<br>';

      //Display File Size
      echo 'File Size: '.$file->getSize();
      echo '<br>';

      //Display File Mime Type
      echo 'File Mime Type: '.$file->getMimeType();
   */
      //Move Uploaded File
      $destinationPath = '/app/public/uploads/bookfile';
      if($file == null){
          echo "error file is empty";
        return;
      }
      $coverlink =  '/storage/uploads/bookcover/'.str_replace(' ', '_', $coverimage->getClientOriginalName());
      $coverimage->storeAs('public/uploads/bookcover',str_replace(' ', '_', $coverimage->getClientOriginalName()));


      $booklink =  '/storage/uploads/bookfile/'.str_replace(' ', '_', $file->getClientOriginalName());
      $file->storeAs('public/uploads/bookfile',str_replace(' ', '_', $file->getClientOriginalName()));



  $bookinfo = new bookinfo();
  $bookinfo->bookcategory = $request->bookcategory;
  $bookinfo->bookname = $request->bookname;
  $bookinfo->bookauthor = $request->bookauthor;
  $bookinfo->bookprice = $request->bookprice;
  $bookinfo->bookdesc = $request->bookdesc;
  $bookinfo->bookyear = $request->bookyear;
  $bookinfo->imageurl = $coverlink;
  $bookinfo->booklink = $booklink;
  $bookinfo->save();
    return redirect()->intended('addbooklecturer');


}


public function bookinfo(){

  $alllecturer = book::all();
    return view('dashboard.addbooklecturer',compact('addbooklecturer'));
}


public function addbookprocess(Request $request){
  //echo json_encode($request->all());
$rules = array(
'bookcategory' => 'required',
'bookname' => 'required',
'bookauthor' => 'required',
'bookprice' => 'required',
'bookdesc' => 'required',
'bookyear' => 'required',
'bookfile' => 'required',
'coverimage' => 'required',
);

$feedbackmsg = array(
  'bookcategory.required' => 'Please select Product Category',
  'bookname.required' => 'Please enter Product Name',
  'bookauthor.required' => 'Please enter Product Author',
  'bookprice.required' => 'Please select Product Price',
  'bookdesc.required' => 'Please enter Product Description',
  'bookyear.required' => 'Please enter Product Year',
    'bookfile.required' => 'Please add the Product file',
    'coverimage.required' => 'Please add the Cover Image',
);

$validator = Validator::make($request->all(),$rules,$feedbackmsg);

if($validator->fails()){
  return back()->withErrors($validator);
  return;
}


      $file = $request->file('bookfile');
        $coverimage = $request->file('coverimage');
   /*
      //Display File Name
      echo 'File Name: '.$file->getClientOriginalName();
      echo '<br>';

      //Display File Extension
      echo 'File Extension: '.$file->getClientOriginalExtension();
      echo '<br>';

      //Display File Real Path
      echo 'File Real Path: '.$file->getRealPath();
      echo '<br>';

      //Display File Size
      echo 'File Size: '.$file->getSize();
      echo '<br>';

      //Display File Mime Type
      echo 'File Mime Type: '.$file->getMimeType();
   */
      //Move Uploaded File
      $destinationPath = '/app/public/uploads/bookfile';
      if($file == null){
          echo "error file is empty";
        return;
      }
      $coverlink =  '/storage/uploads/bookcover/'.str_replace(' ', '_', $coverimage->getClientOriginalName());
      $coverimage->storeAs('public/uploads/bookcover',str_replace(' ', '_', $coverimage->getClientOriginalName()));


      $booklink =  '/storage/uploads/bookfile/'.str_replace(' ', '_', $file->getClientOriginalName());
      $file->storeAs('public/uploads/bookfile',str_replace(' ', '_', $file->getClientOriginalName()));



  $bookinfo = new bookinfo();
  $bookinfo->bookcategory = $request->bookcategory;
  $bookinfo->bookname = $request->bookname;
  $bookinfo->bookauthor = $request->bookauthor;
  $bookinfo->bookprice = $request->bookprice;
  $bookinfo->bookdesc = $request->bookdesc;
  $bookinfo->bookyear = $request->bookyear;
  $bookinfo->imageurl = $coverlink;
  $bookinfo->booklink = $booklink;
  $bookinfo->save();
    return redirect()->intended('addbook');


}
// public function borangpemetaaan(Request $request){
//   echo json_encode($request->all());
// }

public function borangpemetaaan(Request $request){
  $user = Auth::user();
  if($user->usertype == 3)
    return back();
  return view('dashboard.borangpemetaan');
}

public function edit_borangpemetaan(borangpemetaan $borangpemetaan,Request $request) {
  $rules = array(
'faculty' => 'required',
'session' => 'required',
'semester' => 'required',
'jabatan' => 'required',
'program' => 'required',
'description' => 'required',
'borangfile' => 'required',

);

$feedbackmsg = array(
  'faculty.required' => 'Please select Faculty',
  'session.required' => 'Please select Session',
  'semester.required' => 'Please select Semester',
  'jabatan.required' => 'Kindly enter Jabatan',
  'program.required' => 'Kindly enter Program',
  'description.required' => 'Kindly enter d description',
  'borangfile.required' => 'Kindly upload a borang file',
  'userid.required' => 'Session timed out, please login',

);
$validator = Validator::make($request->all(),$rules,$feedbackmsg);

  $borangfile = '';
  $borangfile_name = '';
  if($request->hasFile('borangfile')) {
      $course = $request->course;
      $borangfile_name = $request->file('borangfile')->getClientOriginalName();
      $file_url = $request->file('borangfile')->storeAs('public/uploads/borang/'.$course, $borangfile_name);
      $borangfile = str_replace("public/", "", $file_url);
  }

  $borangpemetaan->faculty = $request->faculty;
  $borangpemetaan->session = $request->session;
  $borangpemetaan->semester = $request->semester;
  $borangpemetaan->jabatan = $request->jabatan;
  $borangpemetaan->course = $request->course;
  $borangpemetaan->description = $request->description;
  $borangpemetaan->borangfile = $borangfile;
  $borangpemetaan->borangfile_name = $borangfile_name;

    $borangpemetaan->userid = Auth::user()->id;
  $borangpemetaan->save();

  return redirect()->route('viewborangpemetaan')->with('success', 'Form editted successfully!');
}

public function deleteborangpemetaan(borangpemetaan $borangpemetaan) {
  $borangpemetaan->delete();
  return redirect()->route('viewborangpemetaan');
}

public function createPostPage() {
  return view('dashboard.create-post');
}

public function createPost(Request $request) {
  $rules = array(
  'title' => 'required',
  'body' => 'required',

  );

  $feedbackmsg = array(
    'title.required' => 'Kindly enter title',
    'body.required' => 'Kindly enter body',
  );
  $validator = Validator::make($request->all(),$rules,$feedbackmsg);


    $post = new Post;
    $post->title = $request->title;
    $post->body = $request->body;
    $post->theme_id = Auth::guard('facilitator')->user()->theme->id;
    $post->facilitator_id = Auth::guard('facilitator')->user()->id;
    $post->save();
    return redirect()->route('createPost')->with('success', 'Posts created successfully!');

}

public function createBicPostPage() {
  return view('dashboard.create-bic-post');
}

public function createBicPost(Request $request) {
  $rules = array(
  'title' => 'required',
  'body' => 'required',

  );

  $feedbackmsg = array(
    'title.required' => 'Kindly enter title',
    'body.required' => 'Kindly enter body',
  );
  $validator = Validator::make($request->all(),$rules,$feedbackmsg);


    $post = new BicPost;
    $post->title = $request->title;
    $post->body = $request->body;
    $post->facilitator_id = Auth::guard('facilitator')->user()->id;
    $post->save();
    return redirect()->route('createBicPost')->with('success', 'Posts created successfully!');

}

public function editPostPage(Post $post) {
  return view('dashboard.edit-post', compact('post'));
}
public function editPost(Post $post, Request $request) {
  $rules = array(
  'title' => 'required',
  'body' => 'required',

  );

  $feedbackmsg = array(
    'title.required' => 'Kindly enter title',
    'body.required' => 'Kindly enter body',
  );
  $validator = Validator::make($request->all(),$rules,$feedbackmsg);

    $post->title = $request->title;
    $post->body = $request->body;
    $post->save();
    return redirect()->route('editPost', $post->id)->with('success', 'Posts editted successfully!');

}

public function certificationPage() {
  $user = Auth::guard('facilitator')->user();
  $bics = $user->theme->bics;
  return view('dashboard.certification', compact('bics'));
}

public function issueCertificate(Bic $bic, Request $request) {
  $user = Auth::guard('facilitator')->user();
  $certificate = new Certificate;
  $certificate->facilitator_id = $user->id;
  $certificate->bic_id = $bic->id;
  $certificate->theme_id = $user->theme->id;
  $certificate->save();
  return redirect()->route('certification');
}

public function printCertificate() {
  $user = Auth::guard('bic')->user();
  if(!$user->certificate) {
    return redirect()->route('viewCertificate');
  }
  return view('dashboard.certificate', compact('user'));
}

public function viewCertificate() {
  $user = Auth::guard('bic')->user();

  return view('dashboard.view-certification', compact('user'));
}

public function posts() {
  $user = null;
  if(Auth::guard('bic')->check())
    $user = Auth::guard('bic')->user();
  else
    $user = Auth::guard('facilitator')->user();
  $posts = $user->theme->posts()->latest()->paginate(20);
  return view('dashboard.posts', compact('posts'));
}

public function bicPosts() {
  $user = null;
  if(Auth::guard('bic')->check())
    $user = Auth::guard('bic')->user();
  else
    $user = Auth::guard('facilitator')->user();
  $posts = $user->theme->bicPosts()->latest()->paginate(20);
  return view('dashboard.bic-posts', compact('posts'));
}

public function viewPost(Post $post) {
  $modules = $post->modules;
  $comments = FacilitatorComment::where('post_id', $post->id)->orderBy('created_at', 'desc')->get();
  return view('dashboard.view-post', compact('post', 'modules', 'comments'));
}

public function deletePost(Post $post) {
  $post->delete();
  return redirect()->route('posts')->with('success', 'Post deleted successfully!');
}

public function borangpemetaan(Request $request) {
  $rules = array(
  'faculty' => 'required',
  'session' => 'required',
  'semester' => 'required',
  'jabatan' => 'required',
  'program' => 'required',
  'description' => 'required',
  'borangfile' => 'required',

  );

  $feedbackmsg = array(
    'faculty.required' => 'Please select Faculty',
    'session.required' => 'Please select Session',
    'semester.required' => 'Please select Semester',
    'jabatan.required' => 'Kindly enter Jabatan',
    'program.required' => 'Kindly enter Program',
    'description.required' => 'Kindly enter d description',
    'borangfile.required' => 'Kindly upload a borang file',
    'userid.required' => 'Session timed out, please login',

  );
  $validator = Validator::make($request->all(),$rules,$feedbackmsg);

  // if($validator->fails()){
  //   return back()->withErrors($validator);
  //   return;
  // }

      /*  $file = $request->file('lecturefile');
        //Move Uploaded File
       $destinationPath = storage_path(). '/public/uploads/lecturefile';
        if($file == null){
            echo "error file is empty";
          return;
        }

        $booklink =  storage_path().'/public/uploads/lecturefile/'.str_replace(' ', '_', $file->getClientOriginalName());
        $file->storeAs('public/uploads/lecturefile',str_replace(' ', '_', $file->getClientOriginalName()));
  */
    $borangfile = '';
    $borangfile_name = '';
    if($request->hasFile('borangfile')) {
        $course = $request->course;
        $borangfile_name = $request->file('borangfile')->getClientOriginalName();
        $file_url = $request->file('borangfile')->storeAs('public/uploads/borang/'.$course, $borangfile_name);
        $borangfile = str_replace("public/", "", $file_url);
    }

    $borangpemetaan = new borangpemetaan();
    $borangpemetaan->faculty = $request->faculty;
    $borangpemetaan->session = $request->session;
    $borangpemetaan->semester = $request->semester;
    $borangpemetaan->jabatan = $request->jabatan;
    $borangpemetaan->course = $request->course;
    $borangpemetaan->description = $request->description;
    $borangpemetaan->borangfile = $borangfile;
    $borangpemetaan->borangfile_name = $borangfile_name;

      $borangpemetaan->userid = Auth::user()->id;
    $borangpemetaan->save();
      return redirect()->route('viewborangpemetaan')->with('success', 'Form added successfully!');

}

public function addctes(Request $request){
$rules = array(
'faculty' => 'required',
'session' => 'required',
'semester' => 'required',
'jabatan' => 'required',
'program' => 'required',
'description' => 'required',
'borangfile' => 'required',
'student' => 'required',

);

$feedbackmsg = array(
  'faculty.required' => 'Please select Faculty',
  'session.required' => 'Please select Session',
  'semester.required' => 'Please select Semester',
  'jabatan.required' => 'Kindly enter Jabatan',
  'program.required' => 'Kindly enter Program',
  'description.required' => 'Kindly enter d description',
  'borangfile.required' => 'Kindly upload a borang file',
  'userid.required' => 'Session timed out, please login',
  'student.required' => 'Please select a Student',

);
$validator = Validator::make($request->all(),$rules,$feedbackmsg);

  $borangfile = '';
  $borangfile_name = '';
  if($request->hasFile('borangfile')) {
      $course = $request->course;
      $borangfile_name = $request->file('borangfile')->getClientOriginalName();
      $file_url = $request->file('borangfile')->storeAs('public/uploads/CTES/'.$course, $borangfile_name);
      $borangfile = str_replace("public/", "", $file_url);
  }

  $ctes = new ctes();
  $ctes->faculty = $request->faculty;
  $ctes->session = $request->session;
  $ctes->semester = $request->semester;
  $ctes->jabatan = $request->jabatan;
  $ctes->course = $request->course;
  $ctes->description = $request->description;
  $ctes->borangfile = $borangfile;
  $ctes->borangfile_name = $borangfile_name;
  $ctes->studentid = $request->student;
  $ctes->userid = $request->userid;
  $ctes->save();
    return redirect()->route('ctes')->with('success', 'CTES add successfully!');

}

public function viewborangpemetaan() {
  $user = Auth::user();
  if($user->usertype == 1)
    $borangpemetaans = borangpemetaan::where('userid', $user->id)->get();
  if($user->usertype == 3)
    $borangpemetaans = borangpemetaan::all();
  return view('dashboard.viewborangpemetaan', compact(['borangpemetaans', 'user']));
}

public function ctes() {
  $user = Auth::user();
  if($user->usertype == 3)
    return back();
  $students = studentlist::all();
  return view('dashboard.ctes', compact('students'));
}

public function viewctes() {
  $user = Auth::user();
  if($user->usertype == 1) {
    $ctes = ctes::where('userid', $user->id)->get();
    $students = studentlist::all();
  }
  if($user->usertype == 3)
    $ctes = ctes::all();
  return view('dashboard.viewctes', compact(['ctes','students','user']));
}

public function editctes(ctes $ctes, Request $request) {
  $rules = array(
'faculty' => 'required',
'session' => 'required',
'semester' => 'required',
'jabatan' => 'required',
'program' => 'required',
'description' => 'required',
'borangfile' => 'required',
'student' => 'required',

);

$feedbackmsg = array(
  'faculty.required' => 'Please select Faculty',
  'session.required' => 'Please select Session',
  'semester.required' => 'Please select Semester',
  'jabatan.required' => 'Kindly enter Jabatan',
  'program.required' => 'Kindly enter Program',
  'description.required' => 'Kindly enter d description',
  'borangfile.required' => 'Kindly upload a borang file',
  'userid.required' => 'Session timed out, please login',
  'student.required' => 'Please select a Student',

);
$validator = Validator::make($request->all(),$rules,$feedbackmsg);

  $borangfile = '';
  $borangfile_name = '';
  if($request->hasFile('borangfile')) {
      $course = $request->course;
      $borangfile_name = $request->file('borangfile')->getClientOriginalName();
      $file_url = $request->file('borangfile')->storeAs('public/uploads/CTES/'.$course, $borangfile_name);
      $borangfile = str_replace("public/", "", $file_url);
  }

  $ctes->faculty = $request->faculty;
  $ctes->session = $request->session;
  $ctes->semester = $request->semester;
  $ctes->jabatan = $request->jabatan;
  $ctes->course = $request->course;
  $ctes->description = $request->description;
  $ctes->borangfile = $borangfile;
  $ctes->borangfile_name = $borangfile_name;
  $ctes->studentid = $request->student;
  $ctes->userid = Auth::user()->id;
  $ctes->save();
  return redirect()->route('viewctes')->with('success', 'CTES editted successfully!');
}

public function deletectes(ctes $ctes) {
  $ctes->delete();
  return redirect()->route('viewctes')->with('success', 'CTES deleted successfully!');
}

public function admindocument(Request $request){
  //echo json_encode($request->all());
$rules = array(
'document' => 'required',
'year' => 'required',
'description' => 'required',
'adminfile' => 'required',
'userid' => 'required',

);

$feedbackmsg = array(
  'document.required' => 'Please select Document',
  'year.required' => 'Please select Document Year',
  'description.required' => 'Enter Description',
  'adminfile.required' => 'Kindly attach required file',
'userid.required' => 'Session timed out, please login',

);
$validator = Validator::make($request->all(),$rules,$feedbackmsg);

if($validator->fails()){
  return back()->withErrors($validator);
  return;
}

      $file = $request->file('lecturefile');
      //Move Uploaded File
      $destinationPath = storage_path(). '/public/uploads/adminfile';
      if($file == null){
          echo "error file is empty";
        return;
      }

      $booklink =  storage_path().'/public/uploads/adminfile/'.str_replace(' ', '_', $file->getClientOriginalName());
      $file->storeAs('public/uploads/adminfile',str_replace(' ', '_', $file->getClientOriginalName()));

  $admindocumentn = new admindocument();
  $admindocumentn->document = $request->document;
  $admindocumentn->year = $request->year;
    $admindocumentn->description = $request->description;
  $admindocumentn->adminfile = $booklink;
    $admindocumentn->userid = $request->userid;
  $admindocumentn->save();
    return redirect()->intended('viewadminfile');

}

public function intech(Request $request) {
  $this->validate(request(), [
    'firstname' => 'required',
    'lastname' => 'required',
    'user_type' => 'required',
    'idnum' => 'required',
    'email' => 'required|email',
    'hours' => 'required|max:5',
    'date_time' => 'required',
    'purpose' => 'required',
    'users_count' => 'required|max:20',
  ]);

  $intech = new Intech();
  $intech->firstname = $request->firstname;
  $intech->lastname = $request->lastname;
  $intech->user_type = $request->user_type;
  $intech->idnum = $request->idnum;
  $intech->email = $request->email;
  $intech->hours = $request->hours;
  $intech->date_time = $request->date_time;
  $intech->purpose = $request->purpose;
  $intech->users_count = $request->users_count;

  $intech->save();

  Mail::to(config('mail.from.address'))->send(new Booking($request, 'Intech'));
  // return new Booking($request, 'Intech');
  return redirect()->route('intech')->with('success', "Your booking is being processed!");
}

public function intechpro(Request $request) {
  $this->validate(request(), [
    'firstname' => 'required',
    'lastname' => 'required',
    'user_type' => 'required',
    'idnum' => 'required',
    'email' => 'required|email',
    'hours' => 'required|max:5',
    'date_time' => 'required',
    'purpose' => 'required',
    'users_count' => 'required|max:20',
  ]);

  $intechpro = new Intechpro();
  $intechpro->firstname = $request->firstname;
  $intechpro->lastname = $request->lastname;
  $intechpro->user_type = $request->user_type;
  $intechpro->idnum = $request->idnum;
  $intechpro->email = $request->email;
  $intechpro->hours = $request->hours;
  $intechpro->date_time = $request->date_time;
  $intechpro->purpose = $request->purpose;
  $intechpro->users_count = $request->users_count;

  $intechpro->save();
  Mail::to(config('mail.from.address'))->send(new Booking($request, 'Intechpro'));
  return redirect()->route('intechpro')->with('success', "Your booking is being processed!");
}

public function inventor(Request $request) {
  $this->validate(request(), [
    'firstname' => 'required',
    'lastname' => 'required',
    'user_type' => 'required',
    'idnum' => 'required',
    'email' => 'required|email',
    'hours' => 'required|max:5',
    'date_time' => 'required',
    'purpose' => 'required',
    'users_count' => 'required|max:20',
  ]);

  $inventor = new Inventor();
  $inventor->firstname = $request->firstname;
  $inventor->lastname = $request->lastname;
  $inventor->user_type = $request->user_type;
  $inventor->idnum = $request->idnum;
  $inventor->email = $request->email;
  $inventor->hours = $request->hours;
  $inventor->date_time = Carbon::create($request->date_time);
  $inventor->purpose = $request->purpose;
  $inventor->users_count = $request->users_count;

  $inventor->save();
  Mail::to(config('mail.from.address'))->send(new Booking($request, 'Inventor'));

  return redirect()->route('inventor')->with('success', "Your booking is being processed!");
}

public function mybook(Request $request){
  //echo json_encode($request->all());
  $rules;
  if ( $request->usertype == "student"){

    if ( $request->program == "OTHERS"){
      $rules = array(
      'firstname' => 'required|regex:/^[\pL\s\-]+$/u',
      'lastname' => 'required|regex:/^[\pL\s\-]+$/u',
      'idnum' => 'required',
      'email' => 'required|email|unique:users,email',
      'quantity' => 'required|num|',
      'book' => 'required|num',
      'purpose' => 'required',
      'users' => 'required',

      );
    } else{
    $rules = array(
    'firstname' => 'required|regex:/^[\pL\s\-]+$/u',
    'lastname' => 'required|regex:/^[\pL\s\-]+$/u',
    'idnum' => 'required',
    'email' => 'required|email|unique:users,email',
    'quantity' => 'required|num|',
    'book' => 'required|num',
    'purpose' => 'required',
    'users' => 'required',

    );
  }
  }else{
    $rules = array(
    'firstname' => 'required|regex:/^[\pL\s\-]+$/u',
    'lastname' => 'required|regex:/^[\pL\s\-]+$/u',
    'idnum' => 'required',
    'email' => 'required|email|unique:users,email',
    'quantity' => 'required|num|',
    'book' => 'required|num',
    'purpose' => 'required',
    'users' => 'required',

    );
  }




$feedbackmsg = array(
  'firstname.required' => 'Please provide your first name',
  'firstname.alpha' => 'First Name cannot include numbers',
  'firstname.min' => 'First Name cannot be less than two alphabets',
  'lastname.required' => 'Please provide your last name',
  'lastname.alpha' => 'Last Name cannot include numbers',
  'lastname.min' => 'Last Name cannot be less than two alphabets',
  'idnum.required' => 'If you are a student kindly enter your matric number, if you are a staff kindly enter your Staff ID, If you are a vistor Kindly enter your IC/Passport Number',
  'email.required' => 'Please provide a valid email address',
  'quantity.required' => 'Please provide number of hours',
  'book.required' => 'Please choose a date and time',
  'purpose.required' => 'Explain the purpose of using our space',
  'users.required' => 'Please Enter the number of users',
);




$validator = Validator::make($request->all(),$rules,$feedbackmsg);

if($validator->fails()){
  return back()->withErrors($validator);
  return;
}

  $user = new bookspaces ();
   $user->firstname = $request->firstname;
   $user->lastname = $request->lastname;
   $user->usertype = $request->usertype;
   $user->idnum = $request->idnum;
   $user->email = $request->email;
   $user->quantity = $request->quantity;
   $user->book = $request->book;
   $user->purpose = $request->purpose;
   $user->users = $request->users;
   $user->save();
 return view('bookingsuccessful');
 }

protected function bicValidator(array $data)
{
  return Validator::make($data, [
    'first_name' => ['required', 'string', 'max:255'],
    'last_name' => ['required', 'string', 'max:255'],
    'theme_id' => ['required', 'string'],
    'school' => ['required', 'string', 'max:255'],
    'username' => ['required', 'string', 'max:255', 'unique:bics', 'unique:facilitators'],
    'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
  ]);
}

  protected function userValidator(array $data)
  {
    return Validator::make($data, [
      'firstname' => ['required', 'string', 'max:255'],
      'lastname' => ['required', 'string', 'max:255'],
      'usertype' => ['required', 'numeric'],
      'idnum' => ['required'],
      'email' => ['required', 'email', 'string', 'max:255', 'confirmed'],
      'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
      'question' => ['required', 'string', 'max:255'],
      'answer' => ['required', 'string', 'max:255'],
    ]);
  }

  protected function insideValidator(array $data)
  {
    return Validator::make($data, [
      'firstname' => ['required', 'string', 'max:255'],
      'lastname' => ['required', 'string', 'max:255'],
      'catype' => ['required', 'string'],
      'idnum' => ['required', 'string', 'max:255'],
      'correspondence' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'max:255'],
      'mailaddress' => ['required', 'string', 'max:255'],
      'telephone' => ['required', 'string', 'max:255'],
      'whatsapp' => ['required', 'string', 'max:255'],
      'schoolname' => ['required', 'string', 'max:255'],
      'participants' => ['required', 'string', 'max:255'],
      'productname' => ['required', 'string', 'max:255'],
      'cert' => ['required', 'string', 'max:255'],
      'medal' => ['required', 'string', 'max:255'],
      'password' => ['required', 'string', 'max:255', 'confirmed'],
    ]);
  }
}