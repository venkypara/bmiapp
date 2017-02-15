<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once("Rest.inc.php");
   
  
class API extends REST {
     
    public $data = "";
    //Enter details of your database
    const DB_SERVER = "localhost";   //bmiapp.db.8615801.hostedresource.com
    const DB_USER = "root";         //bmiapp
    const DB_PASSWORD = "";        //Demo@123
    const DB = "userbmi";         //bmiapp
     
    public $db = NULL;
 
    public $baseurl="http://localhost/bmiapp";  //http://conationconsulting.com/bmiapp
 
    public function __construct(){
        parent::__construct();              // Init parent contructor
        $this->dbConnect();                 // Initiate Database connection
}
     
public function dbConnect(){
    		$this->conn = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}
     
    /*
     * Public method for access api.
     * This method dynmically call the method based on the query string
     *
     */
public function processApi(){
        $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
        if((int)method_exists($this,$func) > 0)
            $this->$func();
        else
            $this->response('Error code 404, Page not found',404);   // If the method not exist with in this class, response would be "Page not found".
}





public function user_login()
{    
    // Cross validation if the request method is GET else it will return "Not Acceptable" status
    if($this->get_request_method() != "POST"){
        $this->response('',406);
    }
	
    $username=$this->_request['username'];   
	$password=$this->_request['password'];

	
	if(!empty($username) && !empty($password))
	{ 

     $user_sqll="select first_name,last_name,verify_status,user_id from users where emailid='".$username."' and password='".md5($password)."'  and status='1'";
	 $user_qry=$this->conn->query($user_sqll);
	   $user_row=$user_qry->num_rows;
	   if($user_row>0)
	   {
		 $user_data=$user_qry->fetch_assoc(); 
		 if($user_data['verify_status']==1)
		 {
		   $row['msg']="Successfully Logged";
		   $row['redirect']="dashboard";
		   $row['user_id']=$user_data['user_id'];
		   $row['fname']=$user_data['first_name'];
		   $row['lname']=$user_data['last_name'];
		   $row['status']="1"; 
		   
		 }
		 else
		 {
		   $row['msg']="Please Reset Password";
		   $row['redirect']="resetpass";
		   $row['user_id']=$user_data['user_id'];
		   $row['exist_pass']=$password;
		   $row['status']="1"; 			 
		 }
 
			$data=$this->json($row);
			$this->response($data, 200);  
         		 
	   }
	   {
			$row['msg']="Invalid User Id/Password";
			$row['status']="0"; 
			$data=$this->json($row);
			$this->response($data, 400);   
	   }
			$this->conn->close();
		
	}
	else
	{
		   $row['msg']="Invalid Request";
		   $row['status']="0"; 
		   $data=$this->json($row);
		   $this->response($data, 400); 
	}
  
} 


 
public function userregister()
{    
    // Cross validation if the request method is GET else it will return "Not Acceptable" status
    if($this->get_request_method() != "POST"){
        $this->response('',406);
    }
    
    $fname=$this->_request['fname'];
	$lname=$this->_request['lname'];
	$emailid=$this->_request['emailid'];
	$phoneno=$this->_request['phoneno'];
	$address1=$this->_request['address1'];
	$address2=$this->_request['address2'];
	$city=$this->_request['city'];
	$state=$this->_request['state'];
	$country=$this->_request['country'];
	$qid=$this->_request['question_id'];
	$answer=$this->_request['answer'];
	
	$device_id=$this->_request['device_id'];
	$device_token=$this->_request['device_token'];

	
	if(!empty($fname) && !empty($emailid) && !empty($phoneno) && !empty($device_id) && !empty($device_token))
	{
	
		$chk_email_sql="select user_id from users where emailid='".$emailid."' and status='1' ";
		$chk_email=$this->conn->query($chk_email_sql);
		$email_row=$chk_email->num_rows;
		if($email_row>0)
		{
			$vals=$chk_email->fetch_assoc();
			$userid=$vals['user_id'];
		}
		else
			$userid=0;

	
		
        if($email_row>0)
		{
		$row['msg']="Email Id Already Exists";
		$row['status']="0"; 
		$data=$this->json($row);
		$this->response($data, 200); 
		}
        else
		{		
$query1="insert into users (`first_name`,`last_name`,`emailid`,`phoneno`,`address1`,`address2`,`city`,`state`,`country`,`question_id`,`answer`,`created_date`,`status`) values ('".$fname."','".$lname."','".$emailid."','".$phoneno."','".$address1."','".$address2."','".$city."','".$state."','".$country."','".$qid."','".$answer."',NOW(),'1')";
			//echo $query1;
			$q1=$this->conn->query($query1);
			$userid=$this->conn->insert_id;

			$query2="insert into user_devices (`user_id`,`device_id`,`device_token`,`created_date`,`status`) values ('".$userid."','".$device_id."','".$device_token."',NOW(),'1')";
			$q2=$this->conn->query($query2);
			$last_device=$this->conn->insert_id; 
		}	

		if($userid>0)
		{

	/* random password */
	 $chrs = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
     $chrsLength = strlen($chrs);
     $randomPass = '';
     for ($i = 0; $i < 10; $i++) {
        $randomPass .= $chrs[rand(0, $chrsLength - 1)];
     }

	 $encode_pass=base64_encode($randomPass);
	 $encode_uid=base64_encode($userid);
	 
	/* send random temporary password email  */
	/*$mail_msg="<html><body>
	           <p>Dear ".$fname." ".$lname."</p>
			   <p>Thank you for Registering with us</p>
			   <p>Please <a href='".$this->baseurl."/verifyuser?uid=".$encode_uid."&ups=".$encode_pass."'>Click here</a> to Activate your account</p>
	           </body></html>"; */
               $mail_msg="<html><body>
	           <p>Dear ".$fname." ".$lname."</p>
			   <p>Thank you for Registering with us</p>
			   <p>Username: ".$emailid."<br>
			      Temparory Password: ".$randomPass."</p>
	           </body></html>";

			   
			   
	$mail_sub="Sampath Mobile App User Registration";
	$mail_to=$emailid;
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
	$headers .= "From: venky.para@gmail.com" . "\r\n" .
	"Reply-To: venky.para@gmail.com" . "\r\n" .
	"X-Mailer: PHP/" . phpversion();
	
	$sendmail= mail($mail_to, $mail_sub, $mail_msg, $headers);
	if($sendmail)
	{
		$row['mail_status']="1";
		$row['mail_msg']="Mail Sent Successfully";		
	}
	else
	{
		$row['mail_status']="0";
		$row['mail_msg']="Failed to send mail";		
	}

	
	
	/* update user temparory password */
		$update_sql="update users set password='".md5($randomPass)."' where user_id='".$userid."' ";
		$update=$this->conn->query($update_sql);	
	

		   $row['user_id']=$userid;
		   $row['msg']="Successfully Registered. Password has been sent to you email";
		   $row['status']="1";  
		}
		else
		{
		   $row['user_id']="";
		   $row['msg']="Error in Storing Data";
		   $row['status']="0";    
		}

		$this->conn->close();
		
		$data=$this->json($row);
		$this->response($data, 200); 
	
	}
	else
	{
		   $row['msg']="Invalid Parameters";
		   $row['status']="0"; 
		   $data=$this->json($row);
		   $this->response($data, 400); 
	}
  
} 


public function verifyuser()
{    
    // Cross validation if the request method is GET else it will return "Not Acceptable" status
    if($this->get_request_method() != "GET"){
        $this->response('',406);
    }
	
    $user_id=base64_decode($this->_request['uid']);   
	$upass=base64_decode($this->_request['ups']);
	  //$user_id=$this->_request['uid'];   
	 // $upass=$this->_request['ups'];
	
	if(!empty($user_id) && !empty($upass))
	{ 

     $user_sqll="select user_id from users where user_id='".$user_id."' and password='".md5($upass)."'  and status='1'";
	 $user_qry=$this->conn->query($user_sqll);
	   $user_row=$user_qry->num_rows;
	   if($user_row>0)
	   {
		 $user_data=$user_qry->fetch_assoc(); 
		 if($user_data['verify_status']==1)
		 {
		   $row['msg']="Already Verified";
		   $row['redirect']="login";
		   $row['user_id']='';
		   $row['exist_pass']='';
		   $row['status']="0"; 
		   
		 }
		 else
		 {
		   $row['msg']="Not Verified";
		   $row['redirect']="resetpass";
		   $row['user_id']=$user_id;
		   $row['exist_pass']=$upass;
		   $row['status']="1"; 			 
		 }
 
			$data=$this->json($row);
			$this->response($data, 200);  
         		 
	   }
	   {
			$row['msg']="Invalid User Id/Password";
			$row['status']="0"; 
			$data=$this->json($row);
			$this->response($data, 400);   
	   }
			$this->conn->close();
		
	}
	else
	{
		   $row['msg']="Invalid Request";
		   $row['status']="0"; 
		   $data=$this->json($row);
		   $this->response($data, 400); 
	}
  
} 



public function resetpass()
{    
    // Cross validation if the request method is GET else it will return "Not Acceptable" status
    if($this->get_request_method() != "POST"){
        $this->response('',406);
    }
	
    $user_id=$this->_request['user_id'];   
	$exist_pass=$this->_request['exist_pass'];
	$newpass1=$this->_request['newpass1'];
	$newpass2=$this->_request['newpass2'];
	
	if(!empty($user_id) && !empty($exist_pass) && !empty($newpass1))
	{
      
	 if($newpass1==$newpass2)
	 {
		 
     $user_sqll="select user_id from users where user_id='".$user_id."' and password='".md5($exist_pass)."'  and status='1'";
	 $user_qry=$this->conn->query($user_sqll);
	   $user_row=$user_qry->num_rows;
	   if($user_row>0)
	   {

		$update_sql="update users set password='".md5($newpass1)."',verify_status='1',updated_date=NOW() where user_id='".$user_id."' ";
		$update=$this->conn->query($update_sql);
   
		 	$row['msg']="Verified Successfully";
			$row['redirect']="login";
 		    $row['status']="1"; 	
			$data=$this->json($row);
			$this->response($data, 200);  
         		 
	   }
	   {
			$row['msg']="Invalid User Id/Password";
			$row['status']="0"; 
			$data=$this->json($row);
			$this->response($data, 400);   
	   }
			$this->conn->close();
			
	 }
	}
	else
	{
		   $row['msg']="Invalid Request";
		   $row['status']="0"; 
		   $data=$this->json($row);
		   $this->response($data, 400); 
	}
  
}




public function fotgotpass()
{    
    // Cross validation if the request method is GET else it will return "Not Acceptable" status
    if($this->get_request_method() != "POST"){
        $this->response('',406);
    }
	
    $emailid=$this->_request['emailid'];   
	
	if(!empty($emailid))
	{
      	 
     $user_sqll="select * from users where emailid='".$emailid."' and status='1'";
	 $user_qry=$this->conn->query($user_sqll);
	   $user_row=$user_qry->num_rows;
	   if($user_row>0)
	   {
		   
        $udata=$user_qry->fetch_assoc();
		 
	/* random password */
	 $chrs = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
     $chrsLength = strlen($chrs);
     $randomPass = '';
     for ($i = 0; $i < 10; $i++) {
        $randomPass .= $chrs[rand(0, $chrsLength - 1)];
     }

	 $encode_pass=base64_encode($randomPass);
	 $encode_uid=base64_encode($udata['user_id']);
	 
	/* send random temporary password email  */
	/* $mail_msg="<html><body>
	           <p>Dear ".$udata['fname']." ".$udata['lname']."</p>
			   <p>Please <a href='".$this->baseurl."/verifyuser?uid=".$encode_uid."&ups=".$encode_pass."'>Click here</a> to Reset your Password</p>
	           </body></html>"; */
			   
               $mail_msg="<html><body>
	           <p>Dear ".$udata['fname']." ".$udata['lname']."</p>
			   <p>Thank you for Registering with us</p>
			   <p>Username: ".$udata['emailid']."<br>
			      Temparory Password: ".$randomPass."</p>
	           </body></html>";			   
			   
			   
	$mail_sub="Sampath Mobile App User Forgot Password";
	$mail_to=$udata['emailid'];
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
	$headers .= "From: venky.para@gmail.com" . "\r\n" .
	"Reply-To: venky.para@gmail.com" . "\r\n" .
	"X-Mailer: PHP/" . phpversion();
	
	$sendmail= mail($mail_to, $mail_sub, $mail_msg, $headers);
	if($sendmail)
	{
		$row['mail_status']="1";
		$row['mail_msg']="Mail Sent Successfully";		
	}
	else
	{
		$row['mail_status']="0";
		$row['mail_msg']="Failed to send mail";		
	}

	
	/* update user temparory password */
		$update_sql="update users set password='".md5($randomPass)."',verify_status='0' where user_id='".$udata['user_id']."' ";
		$update=$this->conn->query($update_sql);	
	

		   $row['user_id']=$udata['user_id'];
		   $row['msg']="Password Link sent to your Email";
		   $row['status']="1";  	
			$data=$this->json($row);
			$this->response($data, 200);  
         		 
	   }
	   {
			$row['msg']="Invalid Email ID";
			$row['status']="0"; 
			$data=$this->json($row);
			$this->response($data, 400);   
	   }
			$this->conn->close();
			
	}
	else
	{
		   $row['msg']="Invalid Request";
		   $row['status']="0"; 
		   $data=$this->json($row);
		   $this->response($data, 400); 
	}
  
}




public function change_password()
{    
    // Cross validation if the request method is GET else it will return "Not Acceptable" status
    if($this->get_request_method() != "POST"){
        $this->response('',406);
    }
	
    $user_id=$this->_request['user_id'];   
	$exist_pass=$this->_request['current_pass'];
	$newpass1=$this->_request['newpass1'];
	$newpass2=$this->_request['newpass2'];
	
	if(!empty($user_id) && !empty($exist_pass) && !empty($newpass1))
	{
      
     if($newpass1==$newpass2)
	 {
		 
     $user_sqll="select user_id from users where user_id='".$user_id."' and password='".md5($exist_pass)."'  and status='1'";
	 $user_qry=$this->conn->query($user_sqll);
	   $user_row=$user_qry->num_rows;
	   if($user_row>0)
	   {

		$update_sql="update users set password='".md5($newpass1)."',updated_date=NOW() where user_id='".$user_id."' ";
		$update=$this->conn->query($update_sql);
   
		 	$row['msg']="Password Changed Successfully";
 		    $row['status']="1"; 	
			$data=$this->json($row);
			$this->response($data, 200);  
         		 
	   }
	   {
			$row['msg']="Invalid User Id/Password";
			$row['status']="0"; 
			$data=$this->json($row);
			$this->response($data, 400);   
	   }
			$this->conn->close();
			
	 }else
	 {
			$row['msg']="Passwords Not Matched";
			$row['status']="0"; 
			$data=$this->json($row);
			$this->response($data, 400);  
	 }
	 
	}
	else
	{
		   $row['msg']="Invalid Request";
		   $row['status']="0"; 
		   $data=$this->json($row);
		   $this->response($data, 400); 
	}
  
}




public function submitdetails()
{    
    // Cross validation if the request method is GET else it will return "Not Acceptable" status
    if($this->get_request_method() != "POST"){
        $this->response('',406);
    }
	
    $user_id=$this->_request['user_id'];   
	$bp=$this->_request['bp'];
	$sugar=$this->_request['sugar'];
	$heart_beat=$this->_request['heart_beat'];
	
	if(!empty($user_id))
	{
  
		 
     $insert_sql="insert into userbmi (user_id,bp,sugar,heartbeat,addeddate) values('$user_id','$bp','$sugar','$heart_beat',NOW())";
		$qry=$this->conn->query($insert_sql);
		$lastid=$this->conn->insert_id;

 	         $row['msg']="Successfully Saved";
 		    $row['status']="1"; 	
			$data=$this->json($row);
			$this->response($data, 200);  

			$this->conn->close();
			
	}
	else
	{
		   $row['msg']="Invalid Request";
		   $row['status']="0"; 
		   $data=$this->json($row);
		   $this->response($data, 400); 
	}
  
}




 
public function get_questions()
{    


		$query="select * from security_questions";
		$result=$this->conn->query($query);
		$rowcount=$result->num_rows;
		if($rowcount>0)
		{
			$n=0;
			while($row=$result->fetch_assoc())
			{
				$data[$n]['question_id']=$row['qid'];
				$data[$n]['question']=$row['question'];
				$n++;				
			}
			
			$jsondata=$this->json($data);
			$this->response($jsondata, 200); 
		}
		else
		{
			$data['msg']="No Questions Available";
			$data['status']="0";
			$jsondata=$this->json($data);
			$this->response($jsondata, 400); 				
		}
		$result->free();
		$this->conn->close();

 }  
 






 
public function get_countries(){    


		$query="select * from countries";
		$result=$this->conn->query($query);
		$rowcount=$result->num_rows;
		if($rowcount>0)
		{
			$n=0;
			while($row=$result->fetch_assoc())
			{
				$data[$n]['country_id']=$row['id'];
				$data[$n]['country_name']=$row['name'];
				$n++;				
			}
			
			$jsondata=$this->json($data);
			$this->response($jsondata, 200); 
		}
		else
		{
			$data['msg']="No Countries Available";
			$data['status']="0";
			$jsondata=$this->json($data);
			$this->response($jsondata, 400); 				
		}
		$result->free();
		$this->conn->close();

 }  
 
 
 
 
public function get_states()
{    

    if($this->get_request_method() != "GET"){
        $this->response('',406);
    }
	
	
	$country_id=$this->_request['country_id'];
	if(!empty($country_id))
	{
		$query="select * from states where country_id='".$country_id."' ";
		$result=$this->conn->query($query);
		$rowcount=$result->num_rows;
		if($rowcount>0)
		{
			$n=0;
			while($row=$result->fetch_assoc())
			{
				$data[$n]['state_id']=$row['id'];
				$data[$n]['state_name']=$row['name'];
				$n++;				
			}
			
			$jsondata=$this->json($data);
			$this->response($jsondata, 200); 
		}
		else
		{
				$data['msg']="No States Available";
				$data['status']="0";
				
			$jsondata=$this->json($data);
			$this->response($jsondata, 200); 				
		}
		$result->free();
		$this->conn->close();
	}
	else
	{
		$data['msg']="Invalid Parameter";
		$data['status']="0";
		
		$jsondata=$this->json($data);
		$this->response($jsondata, 400); 
	}		
 
}  
 

public function get_cities()
{    

    if($this->get_request_method() != "GET"){
        $this->response('',406);
    }
	
	
	$state_id=$this->_request['state_id'];
	if(!empty($state_id))
	{
		$query="select * from cities where state_id='".$state_id."' ";
		$result=$this->conn->query($query);
		$rowcount=$result->num_rows;
		if($rowcount>0)
		{
			$n=0;
			while($row=$result->fetch_assoc())
			{
				$data[$n]['city_id']=$row['id'];
				$data[$n]['city_name']=$row['name'];
				$n++;				
			}
			
			$jsondata=$this->json($data);
			$this->response($jsondata, 200); 
		}
		else
		{
				$data['msg']="No Cities Available";
				$data['status']="0";
				
			$jsondata=$this->json($data);
			$this->response($jsondata, 200); 				
		}
		$result->free();
		$this->conn->close();
	}
	else
	{
		$data['msg']="Invalid Parameter";
		$data['status']="0";
		
		$jsondata=$this->json($data);
		$this->response($jsondata, 400); 
	}		
 
}

 


 
     
    /*
     *  Encode array into JSON
    */
    public function json($data){
        if(is_array($data)){
            return json_encode($data);
        }
    }
}
 
    // Initiiate Library
     
    $api = new API;
    $api->processApi();
?>