<?php

class User_Authentication extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('login_database');
    }

// Show login page
    public function index()
    {
        $this->load->view('login_form');
    }

// Show registration page
    public function user_registration_show()
    {
        $this->load->view('registration_form');
    }

    // Validate and store registration data in database
    public function new_user_registration()
    {

        // Check validation for user input in SignUp form
        $this->form_validation->set_rules('name', 'Username', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('registration_form');
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'password' => $this->input->post('password'),
            );
            $result = $this->login_database->registration_insert($data);
            if ($result == true) {
                $data['message_display'] = 'Registration Successfully Completed!';
                $this->load->view('login_form', $data);
            } else {
                $data['message_display'] = 'Username already exist!';
                $this->load->view('registration_form', $data);
            }
        }
    }

// Check for user login process
    public function user_login_process()
    {

        $this->form_validation->set_rules('name', 'Username', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            if (isset($this->session->userdata['logged_in'])) {
                $this->load->view('admin_page');
            } else {
                $this->load->view('login_form');
            }
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'password' => $this->input->post('password'),
            );
            $result = $this->login_database->login($data);
            if ($result == true) {

                $username = $this->input->post('name');
                $result = $this->login_database->read_user_information($username);
                if ($result != false) {
                    $session_data = array(
                        'name' => $result[0]->name,
                        'email' => $result[0]->email,
                    );
                    // Add user data in session
                    $this->session->set_userdata('logged_in', $session_data);
                    $this->load->view('admin_page');
                }
            } else {
                $data = array(
                    'error_message' => 'Invalid Username or Password',
                );
                $this->load->view('login_form', $data);
            }
        }
    }

    // Logout from admin page
    public function logout()
    {

        // Removing session data
        $sess_array = array(
            'name' => '',
        );
        $this->session->unset_userdata('logged_in', $sess_array);
        $data['message_display'] = 'Successfully Logout';
        $this->load->view('login_form', $data);
    }

    public function do_upload()
    {
            $image_path                     = realpath(APPPATH . '../uploads');
            $config['upload_path']          = $image_path;
            $config['allowed_types']        = 'gif|jpg|png';
            $config['max_size']             = 10000;
            $config['max_width']            = 10240;
            $config['max_height']           = 7680;

/*             var_dump($config['upload_path']);
            die(); */
            $this->load->library('upload', $config);
            var_dump($_FILES['userfile']['name']);
            die();
            if ( ! $this->upload->do_upload('userfile'))
            {
                    $error = array('error' => $this->upload->display_errors());
                    $this->load->view('admin_page',$error);
                    /* return redirect("user_authentication/user_login_process","location"); */
            }
            else
            {
                    $data = array('upload_data' => $this->upload->data());

                    $this->load->view('admin_page',$data);
                    /* $this->load->view('upload_success', $data); */
            }
    }

}
