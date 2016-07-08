<?php namespace Admin;

use Illuminate\Routing\Controllers\Controller;
use View, Input, Redirect, Session, Validator;
use Post as PostModel;

class PostController extends \BaseController {
	
	public function __construct() {
		$this->beforeFilter(function(){
			if (!Session::has('admin_id')) {
				return Redirect::route('admin.auth.login');
			}
		});
	}
    
	public function index() {
        $param['posts'] = PostModel::paginate(90);
        $param['pageNo'] = 9;
        
	    if ($alert = Session::get('alert')) {
            $param['alert'] = $alert;
        }
        
		return View::make('admin.post.index')->with($param);
	}
	
	public function create() {
		$param['pageNo'] = 9;
		
	    return View::make('admin.post.create')->with($param);
	}
	
	public function edit($id) {
	    $param['presence'] = PostModel::find($id);
	    $param['pageNo'] = 9;
	    
	    return View::make('admin.post.edit')->with($param);
	}
	
	public function store() {
	    
	    $rules = ['name'    => 'required'];
	    $validator = Validator::make(Input::all(), $rules);
	    
	    if ($validator->fails()) {
	        return Redirect::back()
	            ->withErrors($validator)
	            ->withInput();
	    } else {
	        $name = Input::get('name');
	        
	        if (Input::has('presence_id')) {
	            $id = Input::get('presence_id');
	            $presence = PresenceModel::find($id);
	            
	            $alert['msg'] = 'Presence has been updated successfully';
	            $alert['type'] = 'success';
	        } else {
	            $presence = new PresenceModel;	     

	            $alert['msg'] = 'Presence has been added successfully';
	            $alert['type'] = 'success';
	        }
	        
       
	        
	        $presence->name = $name;
	        $presence->save();
	          
	        return Redirect::route('admin.post')->with('alert', $alert);	        
	    }
	}
	
	public function delete($id) {
		
		try {
		    PresenceModel::find($id)->delete();
		    
		    $alert['msg'] = 'Presence has been deleted successfully';
		    $alert['type'] = 'success';
		} catch(\Exception $ex) {
			$alert['msg'] = 'This presence has been already used';
			$alert['type'] = 'danger';
		}

	    return Redirect::route('admin.post')->with('alert', $alert);
	}
}
