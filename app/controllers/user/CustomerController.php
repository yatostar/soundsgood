<?php namespace User;

use Illuminate\Routing\Controllers\Controller;
use View, Input, Redirect, Session, Validator , Response;
use User as UserModel;
use City as CityModel;
use Category as CategoryModel;
use Level as LevelModel;
use Language as LanguageModel;
use Type as TypeModel;
use Cart as CartModel;
use Apply as ApplyModel;
use Pattern as PatternModel;
use Job as JobModel;
use Location as LocationModel;
use Post as PostModel;
use Video as VideoModel;
use Image as ImageModel;
use Customer as CustomerModel;
use PostImage as PostImageModel;     
use PostVideo as PostVideoModel;
use Recipe as RecipeModel;
use Store as StoreModel;
use Admin as AdminModel;
use CustomerStore as CustomerStoreModel;
use CustomerProduct as CustomerProductModel; 
use Recipt as ReciptModel;
use ReciptIngredient as ReciptIngredientModel; 
use Mail;

class CustomerController extends \BaseController {
	
	public function view($id) {
		$param['user'] = UserModel::find($id);
		return View::make('user.dashboard.view')->with($param);
	}
	
	public function login() {
        $param['pageNo'] = 98;
        
        $token = Input::get('token'); 
        
        $user = CustomerModel::whereRaw('token = ? and is_active = ?', array( $token , '0'))->get();
       
        
       
        if (count($user) != 0) {
            
            $id = $user[0]->id;
        
            $user =  new CustomerModel;
            $user = CustomerModel::find($id);
         
            $user->is_active = 1;   //set active after approve
            $user->save();
            
            $alert['msg'] = 'You have approved your account.Please login';
            $alert['type'] = 'success';
            
        } else {
            
            
            $alert['msg'] = 'The token is invaild';
            $alert['type'] = 'success';
            
        }
        
        $param['alert'] =  $alert;
        return View::make('user.customer.login')->with($param);        
    }
	public function signin() {
		
        $email = Input::get('email');
        $password = Input::get('password');
        
        $user = CustomerModel::whereRaw('email = ? and password = md5(concat(salt, ?))', array($email, $password))->get();
        
        if (count($user) != 0) {
            Session::set('user_id', $user[0]->id);
			Session::set('user_type', "customer");
			return Redirect::route('customer.home');
        } else {
            $alert['msg'] = 'Email & Password is incorrect';
            $alert['type'] = 'danger';
            return Redirect::route('user.customer.login')->with('alert', $alert);
        }
    }
	
	public function home() {
		
		$param['users'] = CustomerModel::paginate(10);
		$param['locations'] = LocationModel::all();
		$param['recipes'] = RecipeModel::all(); 
		$param['pageNo'] = 5;
	
		if ($alert = Session::get('alert')) {
			$param['alert'] = $alert;
		}
		return View::make('customer.home.index')->with($param);
	}
    
    public function buy() {
        $customerId = Session::get('user_id');
        $buyProducts = CustomerProductModel::where('customer_id', $customerId)->get();   
        
        $param['products'] = $buyProducts; 
        $param['recipes'] = RecipeModel::all();
        $param['pageNo'] = 8;
    
        if ($alert = Session::get('alert')) {
            $param['alert'] = $alert;
        }
        return View::make('customer.home.buy')->with($param);
    }
    
    public function cabinet() {
        $customerId = Session::get('user_id');
        
        $recipt = ReciptModel::where('customer_id', $customerId )->get();
        $reciptIngredient =  array();
       
        if (count($recipt) != 0) {
            
            $reciptId = $recipt[0]->id;
            
            
            $reciptIngredient = ReciptIngredientModel::where('recipt_id', $reciptId )->get();
            
            $alert['msg'] = 'You have approved your account.Please login';
            $alert['type'] = 'success';
            
        } else {
            
            
            $alert['msg'] = 'The token is invaild';
            $alert['type'] = 'success';
            
        }
         
        $param['reciptIngredients'] = $reciptIngredient; 
        $param['recipes'] = RecipeModel::all();
        
        $param['pageNo'] = 6;
    
        if ($alert = Session::get('alert')) {
            $param['alert'] = $alert;
        }
        return View::make('customer.home.cabinet')->with($param);
    }
    
    public function ingredient() {
        $customerId = Session::get('user_id');
        $buyProducts = CustomerProductModel::where('customer_id', $customerId)->get();   
        
        $param['products'] = $buyProducts; 
        $param['recipes'] = RecipeModel::all();
        $param['pageNo'] = 6;
    
        if ($alert = Session::get('alert')) {
            $param['alert'] = $alert;
        }
        return View::make('customer.home.buy')->with($param);
    }
    
    public function postApply($id) {
        
        
        $customerId = Session::get('user_id');
        
        $receipt = str_random(32);
        
        $customerStoreNew = new CustomerStoreModel;//set new object of customer model
        
        $customerStoreNew->customer_id = $customerId;
        $customerStoreNew->store_id = $id;
        $customerStoreNew->receipt = $receipt;
        
        $customerStoreNew->save();
        
        //send email about application
        //get the administration email
        
        $admin = AdminModel::find(1);
        $adminEmail = $admin->email;
        //get the store name
        $store = StoreModel::find($id);
        $storeName = $store->name;
        //get the customer name
        $customer = CustomerModel::find($customerId);
        $customerName = $customer->name;
     
        $data = array();
        $data = ['email' => $adminEmail , 'storeName'=> $storeName , 'customerName'=>$customerName ,'receipt'=>$receipt];
        
        Mail::send('mail_template.applicationReceived', $data, function ($message) use ($adminEmail) {
            $message->from('mickyjack123@gmail.com');
            $message->to( $adminEmail , '')->subject('New Application Received!');
        });
        
        return Redirect::route('customer.home.apply');
    }
    public function deleteApply() {
        
        $param['users'] = CustomerModel::paginate(10);
        $param['locations'] = LocationModel::all();
        $param['recipes'] = RecipeModel::all();
        $param['stores'] = StoreModel::paginate(10); 
        $param['pageNo'] = 5;
        if ($alert = Session::get('alert')) {
            $param['alert'] = $alert;
        }
        return View::make('customer.home.apply')->with($param);
    }
    
	public function signup() {
		
        $param['pageNo'] = 99;
        $param['locations'] = LocationModel::all();
      
        if ($alert = Session::get('alert')) {
            $param['alert'] = $alert;   
        }
        return View::make('user.customer.signup')->with($param);
    }
    
	public function doSignup(){
		
		  $rules = ['password'   => 'required|confirmed',
                  'password_confirmation' => 'required',
                  'name'       => 'required',
                  'email'	   => 'required|email|unique:customer',
                  'accNumber' =>  'required'
                ];
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput();
        } else {
            $user = new CustomerModel;
            
            $host = $_SERVER['HTTP_HOST'];
            
            $user->name = Input::get('name');
            $user->email = Input::get('email');
            $user->accountNumber = Input::get('accNumber'); 
            $user->salt = str_random(8);
            $user->password = md5($user->salt.Input::get('password'));
            $user->is_active = 0;
            
            $token = str_random(32);
            $user->token = $token ;
            
            $email = Input::get('email');
            
            $user->save();
            
            $alert['msg'] = 'Customer has been signed up successfully. Please verify confirmation email';
            $alert['type'] = 'success';
            
            $data = array();
            $data = ['email' => $email , 'url' => 'http://'.$host.'/customer/login?token='.$token];
            
            Mail::send('mail_template.approveMailToSponsor', $data, function ($message) use ($email) {
                $message->from('mickyjack123@gmail.com');
                $message->to( $email , '')->subject('Welcome!');
            });
            
            return Redirect::route('user.auth.signup')->with('alert', $alert);            
        }
	}
	
	
	public function post() {
		
		$param['posts'] = PostModel::paginate(10);
		$param['pageNo'] = 5;
			
		return View::make('user.dashboard.appliedPosts')->with($param);
		
	}
	public function postView($etc){
		
		$post = PostModel::where('title', $etc)->get(); 
		
		$postVideos = PostVideoModel::where('postId', $post[0]->id)->get(); 
		$postImages = PostImageModel::where('postId', $post[0]->id)->get();
		
		$param['posts'] = $post;
		
		$videos = array();
		$images = array();
		
	
		foreach($postVideos as $postVideo){
			$videos[] = VideoModel::where('id', $postVideo->videoId)->get(); 
		}
		foreach($postImages as $postImage){
			$images[] = ImageModel::where('id', $postImage->imageId)->get();
		}
		
		$param['postVideos'] = $videos;
		$param['postImages'] = $images;
		
		return View::make('user.dashboard.postView')->with($param);
	}
	public function profileEdit(){
		
		$param['user'] = "";
		$param['locations'] = LocationModel::all();
		
		return View::make('user.dashboard.profileEdit')->with($param);
	}
	
	public function chat() {
	
		if (!Session::has('user_id')) {
			return Redirect::route('user.auth.login');
		}else {
			$param['pageNo'] = 3;
			$param['user'] = UserModel::find(Session::get('user_id'));
			
			return View::make('user.customer.chat')->with($param);
		}
	}
	
	public function profile() {
		if (!Session::has('user_id')) {
			return Redirect::route('user.auth.login');
		}else {
			$param['pageNo'] = 3;
			$param['user'] = UserModel::find(Session::get('user_id'));
			$param['posts'] = PostModel::all();
			return View::make('user.dashboard.dashboard')->with($param);
		}
	}
	 	
	public function saveProfile() {
		$rules = [
					'name'       => 'required',
					'birthday'   => 'required',
					'year'       => 'numeric',
					'city_id'    => 'required',
					'renumeration_amount' => 'numeric',
					];
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			return Redirect::back()
			->withErrors($validator)
			->withInput();
		} else {
			$password = Input::get('password');
			$id = Input::get('user_id');
			$user = UserModel::find($id);
			$is_active = Input::get('is_active');
		
			if ($password !== '') {
				$user->secure_key = md5($user->salt.$password);
			}
			
			$user->is_active = $is_active;		
			$user->name = Input::get('name');
			$user->email = Input::get('email');
			$user->gender = Input::get('gender');
			$user->birthday = Input::has('birthday') ? Input::get('birthday') : '';
			$user->year = Input::get('year');
			$user->category_id = Input::get('category_id');
			$user->city_id = Input::get('city_id');
			if (Input::hasFile('profile_image')) {
				$filename = str_random(24).".".Input::file('profile_image')->getClientOriginalExtension();
				Input::file('profile_image')->move(ABS_PHOTO_PATH, $filename);
				$user->profile_image = $filename;
			}
			if (Input::hasFile('cover_image')) {
				$filename = str_random(24).".".Input::file('cover_image')->getClientOriginalExtension();
				Input::file('cover_image')->move(ABS_PHOTO_PATH, $filename);
				$user->cover_image = $filename;
			}
			$user->about = Input::has('about') ? Input::get('about') : '';
			$user->professional_title = Input::has('professional_title') ? Input::get('professional_title'): '';
			$user->level_id = Input::get('level_id');
			$user->communication_value = Input::has('communication_value') ? Input::get('communication_value') : 0;
			$user->communication_note =  Input::has('communication_note') ? Input::get('communication_note') : '';
			$user->organisational_value = Input::get('organisational_value');
			$user->organisational_note = Input::has('organisational_note') ? Input::get('organisational_note') : '';
			$user->job_related_value = Input::get('job_related_value');
			$user->job_related_note = Input::has('job_related_note') ? Input::get('job_related_note') : '';
			$user->native_language_id = Input::get('native_language_id');
			$user->hobbies = Input::has('hobbies') ? Input::get('hobbies') : '';
			$user->renumeration_amount = Input::has('renumeration_amount') ? Input::get('renumeration_amount') : 0;
			$user->is_freelance = Input::has('is_freelance') ? Input::get('is_freelance') : 0;
			$user->is_parttime = Input::has('is_parttime') ? Input::get('is_parttime') : 0;
			$user->is_fulltime = Input::has('is_fulltime') ? Input::get('is_fulltime') : 0;
			$user->is_internship = Input::has('is_internship') ? Input::get('is_internship') : 0;
			$user->is_volunteer = Input::has('is_volunteer') ? Input::get('is_volunteer'): 0;
			$user->phone = Input::has('phone') ? Input::get('phone') : '';
			$user->address = Input::has('address') ? Input::get('address') : '';
			$user->website = Input::has('website') ? Input::get('website') : '';
			$user->facebook = Input::has('facebook') ? Input::get('facebook') : '';
			$user->linkedin = Input::has('linkedin') ? Input::get('linkedin') : '';
			$user->twitter = Input:: has('twitter') ? Input::get('twitter') : '';
			$user->google = Input:: has('google') ? Input::get('google') : '';
			$user->lat = Input::get('lat');
			$user->lng = Input::get('lng');
			$user->is_finished = Input::get('is_finished');
			$user->is_published = Input::has('is_published') ? Input::get('is_published') : 0;
		
			$user->save();
		
			UserSkillModel::where('user_id', $user->id)->delete();
			
			if (Input::has('skill_name')) {
				$count = 0;
		
				foreach (Input::get('skill_name') as $sname) {
					
					if ($sname == '') break;
					 
					$skill = new UserSkillModel;
					 
					$skill->user_id = $user->id;
					$skill->name = $sname;
					$skill->value = Input::get('skill_value')[$count];
					 
					$skill->save();
		
					$count ++;
				}
			}
		
			UserLanguageModel::where('user_id', $user->id)->delete();
		
			if (Input::has('foreign_language_id')) {
				$count = 0;
				 
				foreach (Input::get('foreign_language_id') as $lid) {
					
					if ($lid == '') break;
		
					$language = new UserLanguageModel;
		
					$language->language_id = $lid;
					$language->user_id = $user->id;
					$language->understanding = Input::get('understanding')[$count];
					$language->speaking = Input::get('speaking')[$count];
					$language->writing = Input::get('writing')[$count];
		
					$language->save();
		
					$count ++;
				}
			}
		
			UserEducationModel::where('user_id', $user->id)->delete();
		
			if (Input::has('institution_name')) {
				$count = 0;
				 
				foreach(Input::get('institution_name') as $iname) {
					
					if ($iname == '') break;
		
					$education = new UserEducationModel;
		
					$education->user_id = $user->id;
					$education->name = $iname;
					$education->start = Input::get('period_start')[$count];
					$education->end = Input::get('period_end')[$count];
					$education->faculty = Input::get('qualification')[$count];
					$education->notes = Input::get('institution_note')[$count];
					$education->location = Input::get('location')[$count];
		
					$education->save();
		
					$count ++;
		
				}
			}
		
			UserAwardsModel::where('user_id', $user->id)->delete();
		
			if (Input::has('competition_name')) {
				$count = 0;
				 
				foreach (Input::get('competition_name') as $cname) {
					
					if ($cname == '') break;
		
					$awards = new UserAwardsModel;
		
					$awards->user_id = $user->id;
					$awards->name = $cname;
					$awards->prize = Input::get('prize')[$count];
					$awards->year = Input::get('competition_year')[$count];
					$awards->location = Input::get('competition_location')[$count];
		
					$awards->save();
		
					$count ++;
				}
			}
		
			UserExperienceModel::where('user_id', $user->id)->delete();
			
			if (Input::has('organisation_name')) {
				$count = 0;
				 
				foreach (Input::get('organisation_name') as $oname) {
					
					if ($oname == '') break;
		
					$experience = new UserExperienceModel;
		
					$experience->user_id = $user->id;
					$experience->name = $oname;
					$experience->position = Input::get('job_position')[$count];
					$experience->type_id = Input::get('work_job_type')[$count];
					$experience->notes = Input::get('work_note')[$count];
					$experience->start = Input::get('work_period_start')[$count];
					$experience->end = Input::get('work_period_end')[$count];
		
					$experience->save();
		
					$count ++;
				}
				 
			}
			
			UserTestimonialModel::where('user_id', $user->id)->delete();
		
			if (Input::has('testimonial_name')) {
				$count = 0;
			
				foreach (Input::get('testimonial_name') as $tname) {
					
					if ($tname == '') break;
						
					$testimonial = new UserTestimonialModel;
						
					$testimonial->user_id = $user->id;
					$testimonial->name = $tname;
					$testimonial->organisation = Input::get('testimonial_organisation')[$count];
					$testimonial->notes = Input::get('testimonial_note')[$count];
						
					$testimonial->save();
						
					$count ++;
				}
			}

			$alert['msg'] = 'Profile has been saved successfully';
			$alert['type'] = 'success';
		
			return Redirect::route('user.dashboard.profile')->with('alert', $alert);
		}
	}

}
