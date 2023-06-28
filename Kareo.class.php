<?php
// 
// https://kareocustomertraining.s3.amazonaws.com/Help%20Center/Guides/Kareo_IntegrationAPI_TechnicalGuide.pdf
// 
  define('BASE_DIR', "/var/www/kareo/"); 
	class Kareo { 
		public $user;
    public $password;
    public $customerKey; 
    public $wsdl;
    public $client; 
    public $ids; 
//
//===================================
		public function __construct( $d=array() ){
			$this->user = "KAREO_USER_NAME"; 
			$this->password = "KAREO_PASSWORD"; 
			$this->customerKey = "KAREO_CUSTOMER_KEY"; 
			$this->wsdl = 'https://webservice.kareo.com/services/soap/2.1/KareoServices.svc?singleWsdl'; 
			$this->client = new SoapClient( $this->wsdl );
			$this->ids = array( 
        // strange thing, some functions require guid instead id, but, i didn't find them 
        // in account, only in source code of account page by parse api requests
				'ServiceLocationId'=> array( 
					  array( 
                "serviceLocationId"=> 466111, 
                "serviceLocationGuid"=> "fd0d7de1-eb7e-0307-e053-98341e0adbfc", 
                "practiceId"=> 245912, 
                "name"=> "LOCATION_NAME" 
            )
				), 
				'AppointmentReasonId'=> array(
  					array( 
              "appointmentReasonUuid"=> "94f76b93-5250-44c0-b68a-287b95d5deef", 
              "appointmentReasonId"=> 1981955, 
              "practiceId"=> 245912, 
              "duration"=> 10, 
              "name"=> "Counseling" 
            )
				), 
				'Practice'=> array( 
            "practiceId"=> 245912, 
            "name"=> "PRACTICE_NAME", 
            "practiceGuid"=> "ec363eae-7890-4830-9fd5-24cfe79335df", 
            "customerId"=> 102481, 
            "npi"=> "1871204073" 
          ) 
			); 
			// var_dump( $this->client->__getFunctions() );
		}
//		
//===================================
        public function log( $text="" ){
        	$fp = @fopen(BASE_DIR."/logs/kareo/". date("Y-m-d") .".log", "a");	
            if( $fp ){ 
            	@fwrite( $fp, $text ); 
            	@fclose( $fp );
            }
        } 
//		
//===================================
        private static function _prepare( $value="" ){
    			$value = strval($value);
    			$value = stripslashes($value);
    			$value = str_ireplace(array("\0", "\a", "\b", "\v", "\e", "\f"), ' ', $value);
    			$value = htmlspecialchars_decode($value, ENT_QUOTES);	
    			return $value;
    		}
//		
//===================================
        public static function text( $value="", $default="" ){
    			$value = self::_prepare($value);
    			$value = str_ireplace(array("\t"), ' ', $value);			
    			$value = preg_replace(array(
    				'@<\!--.*?-->@s',
    				'@\/\*(.*?)\*\/@sm',
    				'@<([\?\%]) .*? \\1>@sx',
    				'@<\!\[CDATA\[.*?\]\]>@sx',
    				'@<\!\[.*?\]>.*?<\!\[.*?\]>@sx',	
    				'@\s--.*@',
    				'@<script[^>]*?>.*?</script>@si',
    				'@<style[^>]*?>.*?</style>@siU', 
    				'@<[\/\!]*?[^<>]*?>@si',			
    			), ' ', $value);		
    			$value = strip_tags($value); 		
    			$value = str_replace(array('/*', '*/', ' --', '#__'), ' ', $value); 
    			$value = preg_replace('/[ ]+/', ' ', $value);			
    			$value = trim($value);
    			$value = htmlspecialchars($value, ENT_QUOTES);	
    			return (strlen($value) == 0) ? $default : $value;
    		}
// "http://www.kareo.com/api/schemas/KareoServices/CreatePatient"
//===================================
		public function CreatePatient( $post=array() ){ 
			$response = array();
			try {
				$request = array(
			        'RequestHeader' => array(
			        	'User' => $this->user, 
			        	'Password' => $this->password, 
			        	'CustomerKey' => $this->customerKey, 
			        ),
			        'Patient'		=>	array( 
		        		'FirstName'=> isset( $post['FirstName'] ) ? self::text( $post['FirstName'] ) : "", 
		        		'LastName'=> isset( $post['LastName'] ) ? self::text( $post['LastName'] ) : "", 
		        		'DateofBirth'=> isset( $post['DateofBirth'] ) ? $post['DateofBirth'] : "", 
		        		'Gender'=> isset( $post['Gender'] ) ? self::text( $post['Gender'] ) : "", 
		        		'MobilePhone'=> isset( $post['MobilePhone'] ) ? $post['MobilePhone']  : "", 
		        		'EmailAddress'=> isset( $post['EmailAddress'] ) ? self::text( $post['EmailAddress'] ) : "", 
		        		'Practice'=> array(
			        		'PracticeID'=> isset( $post['PracticeID'] ) ? (int)$post['PracticeID'] : 245912, 
			        		'PracticeName'=> isset( $post['PracticeName'] ) ? self::text( $post['PracticeName'] ) : "" 
			        	)
		        	) 
			    ); 
			    $params = array('request' => $request);
    			$response = $this->client->CreatePatient($params); 
    			$response = json_decode( json_encode( $response ), 1, 1024 );
    		}
    		catch ( Exception $err ){ $response['error'] = $err->getMessage(); } 
    		return $response; 
		} 
// "http://www.kareo.com/api/schemas/KareoServices/GetPatient"
//===================================
		public function GetPatient( $post=array() ){ 
			$response = array();
			try {
				$request = array(
			        'RequestHeader' => array(
			        	'User' => $this->user, 
			        	'Password' => $this->password, 
			        	'CustomerKey' => $this->customerKey 
			        ),
			        'Filter'        => array( ), 
			        'Fields' 		=> array(
			            'ID'=> true, 
			            'FirstName'=> true, 
			            'LastName'=> true, 
			            'DateOfBirth'=> true, 
			            'Age'=> true,  
			            'Gender'=> true, 
			            'MobilePhone'=> true, 
			            'EmailAddress'=> true, 
			            'PracticeID'=> true, 
			            'PracticeName'=> true 
			        ) 
			    ); 
			    if( isset( $post['filters'] ) ){
			    	foreach( $post['filters'] as $k=>$v ){
			    		$request['Filter'][$k] = $v;
			    	}
			    }
			    $params = array('request' => $request);
    			$response = $this->client->GetPatient($params);;
    			$response = json_decode( json_encode( $response ), 1, 1024 );
    		}
    		catch ( Exception $err ){ $response['error'] = $err->getMessage(); } 
    		return $response; 
		}
// "http://www.kareo.com/api/schemas/KareoServices/GetPatients" 
//===================================
		public function GetPatients( $post=array() ){ 
			$response = array();
			try {
				$request = array(
			        'RequestHeader' => array(
			        	'User' => $this->user, 
			        	'Password' => $this->password, 
			        	'CustomerKey' => $this->customerKey 
			        ),
			        'Filter'        => array( ), 
			        /*'Fields' 		=> array(
			            'ID'=> true, 
			            'PatientId'=> true, 
			            'FirstName'=> true, 
			            'LastName'=> true, 
			            'DateOfBirth'=> true, 
			            'DOB'=> true, 
			            'Age'=> true,  
			            'Gender'=> true, 
			            'MobilePhone'=> true, 
			            'EmailAddress'=> true, 
			            'PracticeID'=> true, 
			            'PracticeName'=> true 
			        )*/
			    ); 
			    if( isset( $post['filters'] ) ){
			    	foreach( $post['filters'] as $k=>$v ){
			    		$request['Filter'][$k] = $v;   
			    	}
			    }
			    $params = array('request' => $request);
    			$response = $this->client->GetPatients($params);; 
    			$response = json_decode( json_encode( $response ), 1, 1024 );
    			if( $response && isset( $response['GetPatientsResult']['Patients'] ) ){
    				$return = array(); 
    				foreach( $response['GetPatientsResult']['Patients'] as $k=>$v ){
    					array_push( $return, $v ); 
    				}
    				return $return; 
    			} 
    			else {
    				$response = array('error'=>1, 'data'=>$response); 
    			}
    		}
    		catch ( Exception $err ){ $response['error'] = $err->getMessage(); } 
    		return $response; 
		} 
// "http://www.kareo.com/api/schemas/KareoServices/GetAllPatients"
//===================================
		public function GetAllPatients( $post=array() ){ 
			return array(); 
		}
// "http://www.kareo.com/api/schemas/KareoServices/UpdatePatient" 
//===================================
		public function UpdatePatient( $post=array() ){ 
			return array(); 
		} 	
// "http://www.kareo.com/api/schemas/KareoServices/CreateAppointment"
//===================================
// this function doesn't works with standard швыб 
// request require uids, without them api returs error "cant't decode practice is ... etc"
		public function CreateAppointment( $post=array() ){ 
			$response = array();
			try {
				$request = array(
			        'RequestHeader' => array(
			        	'User' => $this->user, 
			        	'Password' => $this->password, 
			        	'CustomerKey' => $this->customerKey, 
			        ), 
			        'Appointment'=>	array( 
			        	'PracticeId'=> isset( $post['PracticeId'] ) ? $post['PracticeId'] : "EC363EAE-7890-4830-9FD5-24CFE79335DF",  
			        	'ServiceLocationId'=> isset( $post['ServiceLocationId'] ) ? $post['ServiceLocationId'] : "fd0d7de1-eb7e-0307-e053-98341e0adbfc", 
			        	'AppointmentStatus'=> 'Scheduled', 
			        	'StartTime'=> isset( $post['StartTime'] ) ? $post['StartTime'] : "2023-06-16T14:30:00.000Z", 
			        	'EndTime'=> isset( $post['EndTime'] ) ? $post['EndTime'] : "2023-06-16T15:00:00.000Z", 
			        	'PatientSummary'=> array(
			        		  'PatientId'=> isset( $post['PatientId'] ) ? $post['PatientId'] : 2
			        	), 
			        	'AppointmentReasonId'=> isset( $post['AppointmentReasonId'] ) ? $post['AppointmentReasonId'] : "fd0d7de1-eb7e-0307-e053-98341e0adbfc", 
			        	'Notes'=> isset( $post['Notes'] ) ? $post['Notes'] : "Test Appointment",  
			        	'AppointmentType'=> isset( $post['AppointmentType'] ) ? $post['AppointmentType'] : 'P', 
			        	'WasCreatedOnline'=> true, 
			        	'PatientSummaries'=> array() 
			        	'IsRecurring'=> false 
			        ) 
			    ); 
			    $params = array('request' => $request);
    			$response = $this->client->CreateAppointment($params); 
    			$response = json_decode( json_encode( $response ), 1, 1024 );
    		}
    		catch ( Exception $err ){ $response['error'] = $err->getMessage(); } 
    		return $response; 
		}
// "http://www.kareo.com/api/schemas/KareoServices/GetAppointment"
//===================================
		public function getAppointment( $post=array() ){ 
			$response = array();
			try {
				$request = array(
			        'RequestHeader' => array(
			        	'User' => $this->user, 
			        	'Password' => $this->password, 
			        	'CustomerKey' => $this->customerKey 
			        ),
			        'Filter' => array(  ), 
			        'Fields' => array(
			        	'ID'=> true, 
			        	'CreatedDate'=> true, 
			        	'PracticeName'=> true, 
			        	'Type'=> true, 
			        	'PatientID'=> true, 
			        	'PatientFullName'=> true, 
			        	'StartDate'=> true, 
			        	'EndDate'=> true, 
			        	'AppointmentReason'=> true, 
			        	'Notes'=> true, 
			        	'PracticeID'=> true, 
			        	'ServiceLocationName'=> true
			        )
			    ); 
			    if( isset( $post['filters'] ) ){
			    	foreach( $post['filters'] as $k=>$v ){
			    		$request['Filter'][$k] = $v; 
			    	}
			    }
			    $params = array('request' => $request);
    			$response = $this->client->GetAppointments($params); 
    			$response = json_decode( json_encode( $response ), 1, 1024 );
    		}
    		catch ( Exception $err ){ $response['error'] = $err->getMessage(); } 
    		return $response; 
		} 
// "http://www.kareo.com/api/schemas/KareoServices/GetAppointments" 
//===================================
		public function GetAppointments( $post=array() ){ 
			$response = array();
			try {
				$request = array(
			        'RequestHeader' => array(
			        	'User' => $this->user, 
			        	'Password' => $this->password, 
			        	'CustomerKey' => $this->customerKey 
			        ),
			        'Filter' => array( ), 
			        'Fields' => array(
			        	'ID'=> true, 
			        	'CreatedDate'=> true, 
			        	'PracticeName'=> true, 
			        	'Type'=> true, 
			        	'PatientID'=> true, 
			        	'PatientFullName'=> true, 
			        	'StartDate'=> true, 
			        	'EndDate'=> true, 
			        	'AppointmentReason'=> true, 
			        	'Notes'=> true, 
			        	'PracticeID'=> true, 
			        	'ServiceLocationName'=> true
			        )
			    ); 
			    if( isset( $post['filters'] ) ){
			    	foreach( $post['filters'] as $k=>$v ){
			    		$request['Filter'][ $k ] = $v;
			    	}
			    }
			    $params = array('request' => $request);
    			$response = $this->client->GetAppointments($params); 
    			$response = json_decode( json_encode( $response ), 1, 1024 );
    			if( $response && isset( $response['GetAppointmentsResult']['Appointments'] ) && is_array( $response['GetAppointmentsResult']['Appointments'] ) ){
    				$return = array(); 
    				foreach( $response['GetAppointmentsResult']['Appointments'] as $k=>$v ){
    					if( $v['StartDate'] ){
    						array_push( $return, $v );
    					}
    				} 
    				return $return; 
    			}
    			else {
    				$response = array('error'=>1, 'data'=>$response); 
    			}
    		}
    		catch ( Exception $err ){ $response['error'] = $err->getMessage(); } 
    		return $response; 
		} 
// "http://www.kareo.com/api/schemas/KareoServices/UpdateAppointment"
//===================================
		public function updateAppointment( $post=array() ){ 
			return array(); 
		}
// "http://www.kareo.com/api/schemas/KareoServices/DeleteAppointment"
//===================================
		public function deleteAppointment( $post=array() ){ // 
			return array(); 
		}
// "http://www.kareo.com/api/schemas/KareoServices/GetProviders"
//===================================
		public function GetProviders( $post=array() ){
			return array(); 
		}
// "http://www.kareo.com/api/schemas/KareoServices/GetPractices"
//===================================
		public function GetPractices( $post=array() ){
			return array(); 
		}
// "http://www.kareo.com/api/schemas/KareoServices/GetServiceLocations"
//===================================
		public function GetServiceLocations( $post=array() ){
			return array(); 
		}
// "http://www.kareo.com/api/schemas/KareoServices/GetProcedureCodes"
//===================================
		public function GetProcedureCodes( $post=array() ){
			return array(); 
		}
//
//===================================
		


//
//===================================

//
//===================================		
	}
//
//
//
