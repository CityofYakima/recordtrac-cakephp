<?php
class RequestsController extends AppController {

  public $components = array("BusinessDays","RequestHandler");
  
  public function index($query = null) {
    //variables
    $conditions = '';
    $status = '';
    $dateQuery = '';
    $dept = '';
    $requester = '';
    $userID = $this->Session->read('Auth.User.id');
    
    //if there is a filter submitted (GET), adjust query
    if(!empty($this->request->query)){
      //sanitize variables
      $term = filter_var($this->request->query["term"], FILTER_SANITIZE_STRING);
      $dept = filter_var($this->request->query["department_id"], FILTER_VALIDATE_INT);
      
      if(isset($this->request->query["requester"]) && $this->request->query["requester"]!=''){
        $requester = filter_var($this->request->query["requester"], FILTER_SANITIZE_STRING);
        $requester = "AND Requester.Alias LIKE '%$requester%'";
      }
      
      //iterate through statuses
      if(!empty($this->request->query["status"])){
        foreach($this->request->query["status"] as $statusID){
          if (!$this->Session->read('Auth.User')){ //if not a logged in user, overdue and due soon are just "open"
            if($statusID == 1){
              $status[] = 3;
              $status[] = 4;
            }
          }
          $status[] =  filter_var($statusID, FILTER_VALIDATE_INT);
        }
        $status = implode(",", $status);
        $status = "AND Request.Status_id IN ($status)";
      }
      //change dates so that we can use em
      if(isset($this->request->query["min_date"]) && $this->request->query["min_date"] != ''){
        $minDate = filter_var($this->request->query["min_date"], FILTER_SANITIZE_STRING);
        $cleanMinDate = explode("/",$minDate);
        $cleanMinDate = $cleanMinDate[2]."-".$cleanMinDate[0]."-".$cleanMinDate[1]." 00:00:00";
        $dateQuery = "AND Request.date_received > '$cleanMinDate'";
      }
      if(isset($this->request->query["max_date"]) && $this->request->query["max_date"] != ''){
        $maxDate = filter_var($this->request->query["max_date"], FILTER_SANITIZE_STRING);
        $cleanMaxDate = explode("/",$maxDate);
        $cleanMaxDate = $cleanMaxDate[2]."-".$cleanMaxDate[0]."-".$cleanMaxDate[1]." 00:00:00";
        $dateQuery = "AND Request.date_received < '$cleanMaxDate'";
      }
      if(isset($cleanMaxDate) && isset($cleanMinDate)){
        $dateQuery = "AND (Request.date_received BETWEEN '$cleanMinDate' AND '$cleanMaxDate')";
      }
      
      if(isset($dept) && $dept != ''){
        $dept = "AND Request.Department_id = $dept";
      }
      
      $conditions = array("Request.Text LIKE '%$term%' $status $dateQuery $dept $requester");
    }
    //if there is POST data, that's a direct link to a request
    if (!$query && $this->data) {
      $this->redirect(array('action' => 'view', $this->data['Track']['request_id']));
    }
    
    //auto-populates form based on query
    $this->params->data = array('Request' => $this->params->query);
    
    //for form advanced filter department dropdown
    $this->loadModel('Department');
    $this->set('departments',$this->Department->find('list', array('order' => array('Department.name' => 'asc'))));
    
    //statuses for form
    $this->loadModel('Status');
    if ($this->Session->read('Auth.User')){
      $this->set('statuses',$this->Status->find('list'));
    }else{
      $this->set('statuses',$this->Status->find('list', array(
        'conditions' => array('Status.type' => 'public')
      )));
    }

    //total results for title
    $this->set('total',$total = $this->Request->find('count'));
    
    
    //Staff Requests filtering
    if(!empty($this->request->query["my_filter"])){
      $poc = false;
      $helper = false;
      if(in_array("poc", $this->request->query["my_filter"])){
        $poc = true;
        //paginate results
        $this->paginate = array(
    				'limit' => 15,
    				'conditions' => $conditions,
    				'joins' =>  array(
            array(
              'table' => 'owners',
              'alias' => 'Owner',
              'type' => 'inner',
              'conditions' => array(
                  'Owner.request_id = Request.id',
                  'Owner.user_id' => $userID,
                  'Owner.active' => 1,
                  'Owner.is_point_person' => 1
               )
             )
           ),
          'contains' => array('Owner'),
          'group' => 'Owner.request_id',
          'recursive' => 1,
          'order' => array('Request.id' => 'desc')
    		);
      }
      if(in_array("helper", $this->request->query["my_filter"])){
        $helper = true;
        //paginate results
        $this->paginate = array(
    				'limit' => 15,
    				'conditions' => $conditions,
    				'joins' =>  array(
            array(
              'table' => 'owners',
              'alias' => 'Owner',
              'type' => 'inner',
              'conditions' => array(
                  'Owner.request_id = Request.id',
                  'Owner.user_id' => $userID,
                  'Owner.active' => 1,
                  'Owner.is_point_person' => 0
               )
             )
           ),
          'contains' => array('Owner'),
          'group' => 'Owner.request_id',
          'recursive' => 1,
          'order' => array('Request.id' => 'desc')
    		);
      }
      if($poc && $helper){
        //paginate results
        $this->paginate = array(
    				'limit' => 15,
    				'conditions' => $conditions,
    				'joins' =>  array(
            array(
              'table' => 'owners',
              'alias' => 'Owner',
              'type' => 'inner',
              'conditions' => array(
                  'Owner.request_id = Request.id',
                  'Owner.user_id' => $userID,
                  'Owner.active' => 1
               )
             )
           ),
          'contains' => array('Owner'),
          'group' => 'Owner.request_id',
          'recursive' => 1,
          'order' => array('Request.id' => 'desc')
    		);
      }
    }elseif(!isset($this->request->query["my_filter"]) && $this->Session->read('Auth.User')){ //for initial pagview
      //paginate results
        $this->paginate = array(
    				'limit' => 15,
    				'conditions' => $conditions,
    				'joins' =>  array(
            array(
              'table' => 'owners',
              'alias' => 'Owner',
              'type' => 'inner',
              'conditions' => array(
                  'Owner.request_id = Request.id',
                  'Owner.user_id' => $userID,
                  'Owner.active' => 1
               )
             )
           ),
          'contains' => array('Owner'),
          'group' => 'Owner.request_id',
          'recursive' => 1,
          'order' => array('Request.id' => 'desc')
    		);
    }else{
      //paginate results
      $this->paginate = array(
  				'limit' => 15,
  				'conditions' => $conditions,
          'order' => array('Request.id' => 'desc')
  		);
    }
    
		$records = $this->paginate('Request');
		
		//error handling in case there are no requests found
		if( ! empty($records)){
			$this->set('results', $records);
		}else{
			$this->Session->setFlash('No requests found.', 'danger');
			$this->set('results', $records);
		}
  }
  
  public function track(){
    $this->set("title_for_layout","Track a Request - " . $this->getAgencyName());
  }
  
  private function dateSort($a,$b){
    $dateA = strtotime($a['created']);
    $dateB = strtotime($b['created']);
    return ($dateB-$dateA);
  }
  
  public function view($id = null){
    $this->Request->id = $id;
    $this->set("title_for_layout","Request " . $id . " - View a Request - " . $this->getAgencyName());
    $request = $this->Request->read();
    $this->set('request', $request);
	    
    //organize and count reponses
    $responses = array_merge($request["Record"], $request["Note"]);
	  usort($responses, array($this,'dateSort'));
	  $this->set('responses', $responses);
	  $this->set('countResponses',count($responses));
	  
	  
    //the active staff Point of Contact for the Request
    $this->loadModel('Owner');
    $this->set('poc',$this->Owner->find('first', array(
      'conditions' => array('(Owner.active = 1 AND Owner.is_point_person = 1) AND Owner.request_id = '.$id)
    )));
    
    //the active staff Helpers for the Request
    $this->set('helpers',$this->Owner->find('all', array(
      'conditions' => array('(Owner.active = 1 AND Owner.is_point_person != 1) AND Owner.request_id = '. $id)
    )));
    
    //get routing history
    $this->set('history',$this->Owner->find('all', array(
      'conditions' => array('Owner.request_id = '. $id),
      'order' => array('Owner.created' => 'desc')
    )));
    
    
    if($this->Session->read('Auth.User')){ // only load this stuff if a staff member is logged in
    
      // to show max upload size in mb
      $max_upload = (int)(ini_get('upload_max_filesize'));
      $max_post = (int)(ini_get('post_max_size'));
      $memory_limit = (int)(ini_get('memory_limit'));
      $upload_mb = min($max_upload, $max_post, $memory_limit);
      $this->set('upload_mb', $upload_mb);
      
      //list of staff for assigning helpers and point of contact
      $this->loadModel('User');
      $this->set('users',$this->User->find('list', array(
        'joins' => array(
          array(
              'table' => 'departments',
              'alias' => 'DeptJoin',
              'type' => 'LEFT',
              'conditions' => array(
                  'User.department_id = DeptJoin.id'
              )
          )
        ),
        'fields' => array('User.id','User.alias','DeptJoin.name'),
        'conditions' => array('department_id IS NOT NULL')
      )));
      //extend request reasons
      $this->loadModel("ExtendReason");
      $this->set("extend_reasons",$this->ExtendReason->find('list', array('fields' => array('ExtendReason.reason','ExtendReason.name'))));
      
      //close request reasons
      $this->loadModel("ClosedReason");
      $this->set("closed_reasons",$this->ClosedReason->find('list', array('fields' => array('ClosedReason.reason','ClosedReason.name'))));
    }
  }

  public function is_public_record(){
    $this->autoRender = false;
    $this->request->onlyAllow('ajax'); // No direct access via browser URL - Note for Cake2.5: allowMethod()
    $text = $this->request->data["request_text"];
    //define some key terms, and the error that will show to the user.
    $notCity = array (
      "Certificate" => "The " . $this->getAgencyName() . " does not have copies of <strong>birth</strong>, <strong>death</strong>, or marriage <strong>certificates</strong>. Contact the Yakima County Auditor's Office for these records by calling (509) 574-1330 or visiting <a href='http://www.yakimacounty.us/auditor/Record.htm' target='_blank'>www.yakimacounty.us/auditor/Record.html</a>",
      "Divorce" => "The " . $this->getAgencyName() . " does not have copies of <strong>divorce</strong> decrees or judgements. Contact the Yakima County Auditor's Office for these records by calling (509) 574-1330 or visiting <a href='http://www.yakimacounty.us/auditor/Record.htm' target='_blank'>www.yakimacounty.us/auditor/Record.html</a>"
    );
    foreach ($notCity as $key => $value){
      $pattern = "/". $key."/i";
      preg_match($pattern, $text, $matches);
      if(!empty($matches)){
        $words[] = $matches[0];
      }
    }
    if(!empty($words)){
      $errors = '';
      foreach ($words as $word){
        $word = ucfirst($word);
        $errors .= $notCity[$word];
      }
      $data = $errors;
    }else{
      return false;
    }
    return new CakeResponse(array('body' =>$data));
  }

  public function create(){
    App::uses('CakeEmail', 'Network/Email');
    //query doctypes for dropdowm
    $this->loadModel('Doctype');
    $doctypes = $this->Doctype->find('all');
    $doctypeList = array();
    foreach ($doctypes AS $doctype){
      $doctypeList[] = array('value' => $doctype["Doctype"]["department_id"], 'name' => $doctype["Doctype"]["prettyDocName"]);
    }
    $this->set('departments',$doctypeList);
    
    //query for offline submission type
    $this->loadModel('OfflineSubmission');
    $submissions = $this->OfflineSubmission->find('list');
    $this->set('offlineSubmissions',$submissions);

    //save data
    if (!empty($this->request->data)) {
      //clean up the date if this is a manual entry
      if(isset($this->data["Request"]["date_received"])){
        $cleanDate = explode("/",$this->data["Request"]["date_received"]);
        $nowTime = date('h:i:s');
        $cleanDate = $cleanDate[2]."-".$cleanDate[0]."-".$cleanDate[1]." ".$nowTime;
        $this->request->data["Request"]["date_received"] = $cleanDate;
      }else{
        $today = date("Y-m-d H:i:s");
        $this->request->data["Request"]["date_received"] = $today;
      }
      //due date in 5 business days
      $this->request->data["Request"]["due_date"] = $this->BusinessDays->add_business_days($days=5, $date=$this->request->data["Request"]["date_received"], $format="Y-m-d H:i:s");
      $this->request->data["Subscriber"][0]["should_notify"] = 1;

      //get owners for request
      $this->loadModel('Department');
      $dept = $this->Department->find('first', array(
        'conditions' => array('Department.id' => $this->request->data["Request"]["department_id"])
      ));
      //set variables for Point Person
      $this->request->data["Owner"][0]["user_id"] = $dept["Contact"]["id"];
      $this->request->data["Owner"][0]["reason"] = "Point of Contact for ". $dept["Department"]["name"];
      $this->request->data["Owner"][0]["is_point_person"] = 1;
      
      //Set variable for Initial Helper (backup)
      $this->request->data["Owner"][1]["user_id"] = $dept["Backup"]["id"];      
      $this->request->data["Owner"][1]["reason"] = "Backup for ". $dept["Department"]["name"];
      $this->request->data["Owner"][1]["is_point_person"] = 0;
      
      
      //check if the user already exists (by email), if so, we'll just use that user id
      $this->loadModel('User');
      $emailExists = $this->User->find('first',array(
        'conditions' => array('User.email' => $this->request->data["Requester"]["email"])
      ));
      $userConditions = array('order' => array('User.id' => 'desc'));
      
      //if the email exists, unset all the form vars and set the user id
      if(!empty($emailExists)){
        $this->request->data["Requester"]["id"] = $emailExists["User"]["id"];
        unset($this->request->data["Requester"]["email"]);
        unset($this->request->data["Requester"]["alias"]);
        unset($this->request->data["Requester"]["phone"]);
        $userConditions = array('conditions' => array('User.id' => $emailExists["User"]["id"]));
      }

      if($this->Request->saveAll($this->request->data)){ 
        $requestID = $this->Request->getLastInsertId();
        
        $user = $this->User->find('first', $userConditions);
        $owner = $this->User->find('first', array(
          'conditions' => array('User.id' => $dept["Contact"]["id"])
        ));
        $helper = $this->User->find('first', array(
          'conditions' => array('User.id' => $dept["Backup"]["id"])
        ));

        $this->loadModel('Subscriber');
        $subscriber = $this->Subscriber->find('first', array(
          'order' => array('Subscriber.id' => 'desc')
        ));
        $this->Subscriber->id = $subscriber["Subscriber"]["id"];
        
        if($this->Subscriber->saveField('user_id', $user["User"]["id"])){
          if(isset($user["User"]["email"]) && $user["User"]["email"] != ''){
            //email requester
            $Email = new CakeEmail();
            $Email->template('requester')
                ->emailFormat('html')
                ->to($user["User"]["email"])
                ->from($this->getfromEmail())
                ->subject($this->getAgencyName().' Public Disclosure Request #' .$requestID)
                ->viewVars( array(
                    'agencyName' => $this->getAgencyName(),
                    'page' => '/requests/view/' . $requestID,
                    'ownerEmail' => $owner["User"]["email"],
                    'responseDays' => $this->getResponseDays()
                ))
                ->send();
          }
          //email owner
          $Email = new CakeEmail();
          $Email->template('owners')
              ->emailFormat('html')
              ->to($owner["User"]["email"])
              ->from($this->getfromEmail())
              ->subject('New Public Disclosure Request #' .$requestID)
              ->viewVars( array(
                  'agencyName' => $this->getAgencyName(),
                  'page' => '/requests/view/' . $requestID,
                  'ownerEmail' => $owner["User"]["email"],
                  'responseDays' => $this->getResponseDays()
              ))
              ->send();
          //email helper    
          $Email = new CakeEmail();
          $Email->template('owners')
              ->emailFormat('html')
              ->to($helper["User"]["email"])
              ->from($this->getfromEmail())
              ->subject('New Public Disclosure Request #' .$requestID)
              ->viewVars( array(
                  'agencyName' => $this->getAgencyName(),
                  'page' => '/requests/view/' . $requestID,
                  'ownerEmail' => $owner["User"]["email"],
                  'responseDays' => $this->getResponseDays()
              ))
              ->send();
          
          //things are good, redirect with message 
          if ($this->Session->read('Auth.User')){
            $this->Session->setFlash('<h4>The request has been submitted!</h4><p class="lead">The requester has been notified via email that they can expect to hear a response from the '. $this->getAgencyName() .' in the next 5 days. Requester will be automatically contacted with any updates.</p>', 'success');
          }else{
            $this->Session->setFlash('<h4>Your request has been submitted!</h4><p class="lead">You can expect a response from the  '. $this->getAgencyName() .'  in the next ' . $this->getResponseDays() . ' days. You will be contacted via email with any updates.</p> <p class="lead">All messages from the   '. $this->getAgencyName() .' and/or the information and documents you requested will be posted to this page. You can access <a href="/requests/view/' . $requestID . '">this page</a> at any time.</p>', 'success');
          }
          $this->redirect(array('action' => 'view', $this->Request->id));
        }
      }
    }
  }
  
  public function unsubscribe($id=null){
    if($id == null){
      $this->redirect(array('action' => 'index','controller'=> 'recordtrac'));
    }
    $this->loadModel('Subscriber');
    $subscriber = $this->Subscriber->find('first', array(
          'conditions' => array('Subscriber.id' => $id)
        ));
    $this->Subscriber->id = $id; 

    // don't notify
    if($this->Subscriber->saveField('should_notify', 0)){
      $this->Session->setFlash('<h4>Success!</h4><p class="lead">You will no longer receive updates for this request</p>', 'success');  
    }else{
      $this->Session->setFlash('<h4>ERROR</h4><p class="lead">Could not unsubscribe at this time</p>', 'danger');
    }

    $this->redirect(array('action' => 'view', $subscriber["Request"]["id"]));
  }
  
}