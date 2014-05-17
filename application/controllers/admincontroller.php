<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
session_start();

class AdminController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('nav_top');
        $this->load->helper('flash_message');
        $this->load->model('spw_user_model');
        $this->load->model('spw_term_model');
        $this->load->model('spw_match_model');
        $this->load->library('email');
        $this->load->library('unit_test');
    }

    public function index() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email_address', 'Email Address', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $data = array();

        if ($this->form_validation->run() == true) {
            $data['credentials_error'] = "";
            $this->load->model('spw_user_model');
            $res = $this->spw_user_model->verify_user($this->input->post('email_address'), $this->input->post('password'));


            if ($res !== false) {
                $role = 'STUDENT';

                foreach ($res as $row) {
                    $role = $row->role;
                }

                if ($role == 'STUDENT') {
                    //verify againgst API

                    $s_url = $this->config->item('fiu_api_url') . $this->input->post('email_address');
                    $jason_return = file_get_contents($s_url);
                    $jason_return = json_decode($jason_return);

                    $panther_user_info = (object) array(
                                'valid' => $jason_return->valid,
                                'id' => $jason_return->id,
                                'email' => $jason_return->email,
                                'firstName' => $jason_return->firstName,
                                'lastName' => $jason_return->lastName,
                                'middle' => $jason_return->middle
                    );
                    if (!$panther_user_info->valid) {
                        $data['credentials_error'] = "Invalid Credentials";
                    } else {
                        //
                        foreach ($res as $row) {

                            $sess_array = array(
                                'id' => $row->id,
                                'email' => $row->email,
                                'using' => 'fiu_senior_project',
                                'role' => $row->role
                            );
                            $this->session->set_userdata('logged_in', $sess_array);
                        }
                        redirect('home', 'refresh');
                    }
                } else {
                    foreach ($res as $row) {

                        $sess_array = array(
                            'id' => $row->id,
                            'email' => $row->email,
                            'using' => 'fiu_senior_project',
                            'role' => $row->role
                        );
                        $this->session->set_userdata('logged_in', $sess_array);
                    }
                    redirect('home', 'refresh');
                }
            } else if ($this->spw_user_model->has_correct_credentials_and_is_inactive($this->input->post('email_address'), $this->input->post('password'))) {
                $data['credentials_error'] = "Your account has been deactivated. Contact the admin for more information.";
            } else {
                $data['credentials_error'] = "Invalid Credentials";
            }
        } else {
            $data['credentials_error'] = "";
        }
        $this->load->view('login_index', $data);
    }

    public function admin_dashboard() {
        $data['minimum'] = $this->spw_match_model->getMinimum();
        if (isUserLoggedIn($this) && isHeadProfessor($this))
            $this->load->view('admin_dashboard', $data);
        else
            redirect('home', 'refresh');
    }

    public function milestones_view() {
        
        //if the user is logged in, then grant access
        if( isUserLoggedIn($this))
            $this->load->view('milestones_view');
        else
           redirect('home','refresh');     
    }
	
  	/* Added for SPW v. 3 */
	public function act_as_user( )
	{
		$email_address = $this->input->post( 'email_address' );
		$password = $this->input->post( 'password' );
		$role = $this->input->post( 'role' );
		$id = $this->input->post( 'id' );

		if( $role == 'STUDENT' )
		{ 
		  $sess_array = array( 
			  'id' => $id,
			  'email' => $email_address,
			  'using' => 'fiu_senior_project',
			  'role' => $role, 
			  'head_professor' => 1 );

		  $this->session->set_userdata( 'logged_in', $sess_array );
		  redirect( 'home', 'refresh' );
		}
		
		$res = $this->spw_user_model->verify_user( $email_address, $password );
		foreach ($res as $row) 
		{
			$sess_array = array(
                                'id' => $row->id,
                                'email' => $row->email,
                                'using' => 'fiu_senior_project',
                                'role' => $row->role,
									'head_professor' => 1
                             );
			$this->session->set_userdata( 'logged_in', $sess_array );
			redirect( 'home', 'refresh' );
       }
	}
	
	/* Added to SPW v.3 for User Management System */
	public function return_to_head( )
	{
		$query = $this->db->query( 'SELECT id, email, hash_pwd
								  			FROM spw_user
								  			WHERE role = "HEAD"' );
		foreach( $query->result_array( ) as $row )
		{
			$sess_array = array(
                                'id' => $row[ 'id' ],
                                'email' => $row[ 'email' ],
                                'using' => 'fiu_senior_project',
                                'role' => 'HEAD'
                             );
			$this->session->set_userdata( 'logged_in', $sess_array );
			redirect( 'home', 'refresh' );
		}
	}
	
	/* Added for SPW v. 3 */
	public function user_filters( )
	{
		if( isUserLoggedIn($this) &&  isHeadProfessor($this) )
            $this->load->view('admin_user_management');
        else
           redirect('home','refresh');
	}

  /* Added to SPW v.3 for User Management System */
  public function register_new_user( )
	{
		$this->load->library('form_validation');
    $this->form_validation->set_rules('email_address', 'Email Address', 'required|valid_email');
    $this->form_validation->set_rules('role', 'Role', 'required');
    $data = array();
    
    if ($this->form_validation->run( ) !== false)
    {
      $this->load->model( 'spw_user_model' );
      
      $res = $this->spw_user_model->is_spw_registered( $this->input->post( 'email_address' ) );
      if ($res == false)
      {
        $user_id = $this->spw_user_model->create_new_user($this->input->post('email_address'), $this->input->post('role'), $this->input->post('first_name'), $this->input->post('last_name') );        
        $query_msg = $this->db->query( 'SELECT intro FROM email_template WHERE id = "' . $this->input->post('role') . '"');
        foreach( $query_msg->result_array( ) as $row )
        {
	  $message = $row[ 'intro' ];
	}

		$message = $message . '<br><a href="http://srprog-devel.cis.fiu.edu/senior-project-devel/admin/email_activation/' . $this->reversible_encryption( $user_id ) . '"> http://srprog-devel.cis.fiu.edu/senior-project-devel/admin/email_activation/'. $this->reversible_encryption( $user_id ) . '</a>';    	
                send_email($this, $this->input->post('email_address'), 'Senior Project Website Account', $message );
                
                $msg = 'Successfully created a user with the email: ' . $this->input->post('email_address') . '. 
				  A confirmation email will be sent for verification.';
                setFlashMessage($this, $msg);
      }
			else
			{
        $msg = 'Cannot create a user with the email:
				' . $this->input->post('email_address') . '<br>User with this email already exists';
        setErrorFlashMessage($this, $msg);
        $data['already_registered'] = true;
      }
    }
    
    redirect('admin/admin_dashboard');
  }
  
  /* Added to SPW v.3 for User Management System */
  public function change_invitation( $role = '' )
  {
	  $this->load->model( 'spw_user_model' );
	  
	  $role = $this->input->post( 'role' );
	  
	  if( $role === 'PROFESSOR' )
	  	$this->spw_user_model->update_email_template( $role, $this->input->post( 'professor_invitation_text' ) );
	  else if( $role === 'STUDENT' )
	  	$this->spw_user_model->update_email_template( $role, $this->input->post( 'student_invitation_text' ) );
		
	  $msg = 'Successfully updated the email tempalte for: ' . $role . 'S.';
	  setFlashMessage($this, $msg);
	  
	  redirect( 'admin/admin_dashboard' );
  }

  /* Added to SPW v.3 for User Management System */
  public function edit_user( )
  {
	  $this->load->library( 'form_validation' );
	  $this->form_validation->set_rules( 'email_address', 'Email Address', 'required|valid_email' );
	  $data = array( );
	  
	  if( $this->form_validation->run( ) !== false )
	  {
		  $this->load->model( 'spw_user_model' );
		  
		  $res = $this->spw_user_model->is_spw_registered( $_POST[ 'email_address' ] );
		  if( $res == true )
		  {
			  $this->spw_user_model->edit_user( $_POST[ 'email_address' ],
                                         $_POST[ 'role' ],
                                         $_POST[ 'first_name' ],
                                         $_POST[ 'last_name' ],
                                         $_POST[ 'id' ] );
        
			  $msg = 'Successfully edited a user with the email: ' . $_POST[ 'email_address' ];
			  setFlashMessage( $this, $msg );
		  }
		  else
		  {
			  $msg = 'Cannot edit. An error was encountered for : ' . $_POST[ 'email_address' ];
        setErrorFlashMessage($this, $msg);
		  }
	  }
	  
	  redirect( 'admin/filters' );
  }

  /* Added to SPW v.3 for User Management System */
  public function delete_user( )
  {
	  $this->load->library( 'form_validation' );
	  $this->form_validation->set_rules( 'email_address', 'Email Address', 'required|valid_email' );
	  
	  if( $this->form_validation->run( ) !== false )
	  {
		  $this->load->model( 'spw_user_model' );
		  
		  $res = $this->spw_user_model->is_spw_registered( $this->input->post( 'email_address' ) );
		  if( $res == true )
		  {
			  if( $this->spw_user_model->delete_user( $this->input->post( 'userID' ) ) )
			  {
				  $msg = 'Successfully deleted a user with the email: ' . $this->input->post( 'email_address' );
          setFlashMessage( $this, $msg );
			  }
		  }
		  else
		  {
			  $msg = 'Cannot delete. An error was encountered for : ' . $this->input->post( 'email_address' );
        setErrorFlashMessage($this, $msg);
		  }
	  }
	  
	  redirect( 'admin/filters' );
  }

  /* Added to SPW v.3 for User Management System */
  public function change_status( )
  {
	  $this->load->library( 'form_validation' );
	  $this->form_validation->set_rules( 'email_address', 'Email Address', 'required|valid_email' );
	  
	  if( $this->form_validation->run( ) != false )
	  {
		  $this->load->model( 'spw_user_model' );
		  
		  $res = $this->spw_user_model->is_spw_registered( $this->input->post( 'email_address' ) );
		  if( $res )
		  {
			  $this->spw_user_model->change_status( $this->input->post( 'id' ),
                                             $this->input->post( 'status' ) );
			  $msg = 'Successfully updated status for user with the email: ' . $this->input->post( 'email_address' );
			  setFlashMessage( $this, $msg );
		  }
		  else
		  {
			  $msg = 'Cannot change status. An error was encountered for : ' . $this->input->post( 'email_address' );
        setErrorFlashMessage($this, $msg);
		  }
	  }
	  
	  redirect( 'admin/filters' );
  }

  /* Added to SPW v.3 for User Management System */
  public function bypass_activation( )
  {
	  $this->load->library( 'form_validation' );
	  $this->form_validation->set_rules( 'email_address', 'Email Address', 'required|valid_email' );
	  
	  if( $this->form_validation->run( ) != false )
	  {
		  $this->load->model( 'spw_user_model' );
		  $res = $this->spw_user_model->is_spw_registered( $this->input->post( 'email_address' ) );
		  
		  if( $res )
		  {
			  $password = $this->spw_user_model->generate_password( );
			  $hash_password = sha1( $password );
			  $this->spw_user_model->bypass_activation( $this->input->post( 'userID' ), $hash_password );
			  
			  $message ='<html>
        <head><title>Senior Project Website Account Password</title></head>
        <body>
        <h2>Welcome to the Senior Project Website !!</h2>
        
        <p>We have created an account for you to access it.</p>
          <p> Please log in with your email address and this temporary password: ' .  $password . '</p>
          <p>Once you login, update your profile and refer to the User Guide on the "About" page for help.</p>
            <p><a href="http://srprog-spr13-01.aul.fiu.edu/senior-projects">Senior Project Website</a></p>
            </body>
            </html>';
            
            send_email( $this, $this->input->post( 'email_address' ), 'Senior Project Website Account', $message );
			  
			  
			  $msg = 'Successfully bypassed user PENDING status for user with the email: ' . $this->input->post( 'email_address' );
			  setFlashMessage( $this, $msg );
		  }
		  else
		  {
			  $msg = 'Cannot bypass user PENDING status. An error was encountered for: ' . $this->input->post( 'email_address' );
			  setErrorFlashMessage( $this, $msg );
		  }
	  }
	  
	  redirect( 'admin/filters' );
  }
  
  /* Added to SPW v. 3 */
  public function activation( $hash_id = '' )
  {
	  if( $hash_id !== '' )
	  {
		  $user_id = $this->decryption( $hash_id );
		  $data = array( );
		  
		  $this->load->model( 'spw_user_model' );
		  $res = $this->spw_user_model->is_spw_registered_by_id( $user_id );
		  
		  if( $res )
		  {
			  $query = $this->db->query( 'SELECT first_name, last_name, email, picture, role
								  			FROM spw_user
								  			WHERE role != "HEAD" && id="' . $user_id . '"' );
											
			  foreach( $query->result_array( ) as $row )
			  {
				  $data[ 'first_name' ] = $row[ 'first_name' ];
				  $data[ 'last_name' ] = $row[ 'last_name' ];
				  $data[ 'email_address' ] = $row[ 'email' ];
				  $data[ 'role' ] = $row[ 'role' ];
				  $data[ 'id' ] = $user_id;
				  
				  if( $row['picture'] == NULL )
				  	  $data[ 'picture' ] = base_url( '/img/no-photo.jpeg' );
				  else
					  $data[ 'picture' ] = $row['picture'];
			  }
			  
			  $this->load->view( 'user_activation', $data );
		  }
		  else
		  {
			  $msg = 'There was no user found for the information provided: ' . $user_id;
			  setErrorFlashMessage( $this, $msg );
			  
			  redirect( 'login' );
		  }
	  }
	  else
	  {
		  $msg = 'The link you are trying to reach is not valid';
		  setErrorFlashMessage( $this, $msg );
		  
		  redirect( '404_override' );
	  }
  }
  
  /* Added to SPW v. 3 */
  public function activate_pending( )
  {
	  $this->load->library( 'form_validation' );
	  $this->form_validation->set_rules( 'email_address', 'Email Address', 'required|valid_email' );
	  
	  if( $this->form_validation->run( ) != false )
	  {
		  $this->load->model( 'spw_user_model' );
		  
		  $res = $this->spw_user_model->is_spw_registered( $this->input->post( 'email_address' ) );
		  if( $res )
		  {
			  $this->spw_user_model->set_pwd( $this->input->post( 'id' ),
			  											$this->input->post( 'password_1' ) );
			  $msg = 'Successfully updated status for user with the email: ' . $this->input->post( 'email_address' );
			  setFlashMessage( $this, $msg );
		  }
		  else
		  {
			  $msg = 'Cannot change status. An error was encountered for : ' . $this->input->post( 'email_address' );
             setErrorFlashMessage($this, $msg);
		  }
	  }
	  
	  redirect( 'login' );
  }
  
  /* Added to SPW v. 3 */
  public function reversible_encryption( $data )
  {
	  /*$key = '123456';
	  $string = $data;
	  
	  $encrypted = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, sha1( $key ), $string, MCRYPT_MODE_CBC, md5( md5( $key ) ) ) );
	  
	  return $encrypted;*/
	  
	  return $data * 238917652;
  }
  
  /* Added to SPW v. 3 */
  public function decryption( $encrypted )
  {
	  /*$key = '123456';
	  
	  $decrypted = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $key ), 
	  											base64_decode( $encrypted ), MCRYPT_MODE_CBC, md5( md5( $key ) ) ), "\0" );
	
	  return $decrypted;*/
	  
	  return $encrypted / 238917652;
  }

    public function register_professor() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email_address', 'Email Address', 'required|valid_email');
        $this->form_validation->set_rules('password_1', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('password_2', 'Password', 'required|min_length[6]');
        $data = array();

        if ($this->form_validation->run() !== false) {
            $this->load->model('spw_user_model');

            $res = $this->spw_user_model->is_spw_registered($this->input->post('email_address'));
            if ($res == false) {
                $this->spw_user_model->create_new_professor_user($this->input->post('email_address'), $this->input->post('password_1'), $this->input->post('first_name'), $this->input->post('last_name'));
                $message = ' <html >  <head><title></title></head>
                            <body>  <he <h2>Welcome to the Senior Project Website !! </h2>
                                <p>We have created an account for you to access it.</p>
                                <p> Please log in with your email address and this temporary password:' . $this->input->post('password_1') . ' </p>
                                    <p>Once you login, please update your profile and refer to the User Guide on the About page for help.</p>
                                <p><a href="http://srprog-spr13-01.aul.fiu.edu/senior-projects">SeniorProjectWebsite</a></p>
                            </body>
                            </html>';

                send_email($this, $this->input->post('email_address'), 'Senior Project Website Account', $message);

                $msg = 'Successfully created a new professor user with the email: ' . $this->input->post('email_address');
                setFlashMessage($this, $msg);
            } else {
                $msg = 'Cannot create a professor with the email: ' . $this->input->post('email_address') . '
<br>User with this email already exists';
                setErrorFlashMessage($this, $msg);
                $data['already_registered'] = true;
            }
        }
        redirect('admin/admin_dashboard');
    }

    //need a fucntion that will retrieve all the users that are currently in the system
    public function activate_deactive_users() {
        $updates = 0;
        if ($this->input->post('action') === 'Deactivate') {
            if (is_array($this->input->post('users'))) {
                //retrieve all the ids from the array
                foreach ($this->input->post('users') as $key => $value) {
                    $this->spw_user_model->change_status_to_inactive($value);
                    $updates++;
                }

                $msg = 'Successfully deactivated ' . $updates . ' user(s)';
                setFlashMessage($this, $msg);
            }
        } else if ($this->input->post('action') === 'Activate') {
            if (is_array($this->input->post('users'))) {
                //retrieve all the ids from the array
                foreach ($this->input->post('users') as $key => $value) {
                    $this->spw_user_model->change_status_to_active($value);
                    $updates++;
                }

                $msg = 'Successfully activated ' . $updates . ' user(s)';
                setFlashMessage($this, $msg);
            }
        }

        redirect('admin/filters');
    }

    public function set_deadline() {
        //This will return the epoch date: 1970-01-01
        $epochDate = date("Y-m-d", strtotime("//"));

        //convert the text fields into date objects
        $startDeadline = date("Y-m-d", strtotime($this->input->post('from-pick')));
        $endDeadline = date("Y-m-d", strtotime($this->input->post('to-pick')));

        //check that both dates can actually be parsed into a date not equal to epoch
        if ($startDeadline == $epochDate) {
            $msg = 'The input: ' . $this->input->post('from-pick') . ' cannot be parsed as a valid date.
                <br>Use the calendar to make your selections.';
            setErrorFlashMessage($this, $msg);

            redirect('admin/admin_dashboard');
            return;
        } else if ($endDeadline == $epochDate) {
            $msg = 'The input: ' . $this->input->post('to-pick') . ' cannot be parsed as a valid date.
                <br>Use the calendar to make your selections.';
            setErrorFlashMessage($this, $msg);

            redirect('admin/admin_dashboard');
            return;
        }
        //if the end date is less than or equal to the start date then this isn't a valid time window
        else if ($endDeadline <= $startDeadline) {
            $msg = 'The end date must be greater than the start date to appropiately define a realistic time window.';
            setErrorFlashMessage($this, $msg);

            redirect('admin/admin_dashboard');
            return;
        }
        //both dates are valid, so proceed to insert them into the deadline
        else {
            //retrieve the information for the ongoing semester
            //$currentTerm = $this->spw_term_model->getCurrentTermInfo();

            $term = SPW_Term_Model::getInstance();
            $term->setDeadline($startDeadline, $endDeadline);
            //$this->spw_term_model->setDeadline($startDeadline, $endDeadline);

            $msg = 'Successfully updated the join/leave project time period';
            setFlashMessage($this, $msg);

            redirect('admin/admin_dashboard');
        }
    }

    public function refresh_api() {
        $s_url = $this->config->item('fiu_api_refresh');
        $jason_return = file_get_contents($s_url);
        if ($jason_return == 'OK') {
            setFlashMessage($this, "Succesfully update from API");
        } else {
            setErrorFlashMessage($this, "There was an error on the API. Please verify the server.");
        }
        redirect('admin/admin_dashboard');
    }

}
