<?php

require APPPATH.'libraries/REST_Controller.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST");

class Student extends REST_Controller{
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/student_model');
        $this->load->helper(array(
            'authorization',
            'jwt'
        ));
    }

    public function register_post()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->name) && isset($data->email) && ($data->branch_id) && isset($data->phone) && isset($data->password) && isset($data->gender))
        {
            if($this->student_model->is_email_exists($data->email))
            {
                $this->response(array(
                    'status' => 0,
                    'message' => "Email address already registered"
                ),parent::HTTP_NOT_FOUND);
            }
            else{
                $student_data = array(
                    "name" => $data->name,
                    "email" => $data->email,
                    "phone" => $data->phone,
                    "branch_id" => $data->branch_id,
                    "password" => password_hash($data->password, PASSWORD_DEFAULT),
                    "gender" => $data->gender,                
                );

                if($this->student_model->create_student($student_data))
                {
                    $this->response(array(
                        'status' => 1,
                        'message' => "Student has been created"
                    ),parent::HTTP_OK);
                }
                else{
                    $this->response(array(
                        'status' => 0,
                        'message' => "Failed to create student"
                    ),parent::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }else{
            $this->response(array(
                'status' => 0,
                'message' => "All fields are needed"
            ),parent::HTTP_NOT_FOUND);
        }
    }

    public function list_get()
    {
        $data = $this->student_model->students_list();

        if(count($data)>0){
            $this->response(array(
                "status" => 1,
                "message" => "Students List",
                'data' => $data
            ), parent::HTTP_OK);
        }
        else{
            $this->response(array(
                'status'=>0,
                'message'=>"No student found"
            ), parent::HTTP_NOT_FOUND);
        }
    }

    public function update_details_put()
    {
       $data = json_decode(file_get_contents("php://input"));
       if(isset($data->id) && isset($data->name) && isset($data->email) && isset($data->phone) && isset($data->gender) && isset($data->branch_id)){
            $student_data = array(
                "name" => $data->name,
                "email" => $data->email,
                "phone" => $data->phone,
                "branch_id" => $data->branch_id,
                "gender" => $data->gender
            );

            if($this->student_model->update_student($data->id,$student_data))
            {
                $this->response(array(
                    'status' => 1,
                    'message' => "Student has been successfully added"
                ), parent::HTTP_OK);
            }
            else{
                $this->response(array(
                    'status' => 0,
                    'message' => 'Failed to update the student data'
                ), parent::HTTP_INTERNAL_SERVER_ERROR);   
            }
       } 
       else{
           $this->response(array(
               'status' => 0,
               'message' => 'All fields are needed'
           ), parent::HTTP_NOT_FOUND);
       }
    }

    public function delete_student_delete()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->id)){

            if($this->student_model->find_by_id($data->id)){
                if($this->student_model->delete_student($data->id)){
                    $this->response(array(
                        'status' => 1,
                        'message' => 'Student has been deleted'
                    ), parent::HTTP_OK);
                }
                else{
                    $this->response(array(
                        'status' => 0,
                        'message' => 'failed to delete student'
                    ), parent::HTTP_NOT_FOUND);
                }
            }
            else{
                $this->response(array(
                    'status' => 0,
                    'message' => 'Student id does not exists'
                ), parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else{
            $this->response(array(
                'status' => 0,
                'message' => 'Student id should be needed'
            ),parent::HTTP_NOT_FOUND);
        }
    }

    public function login_post()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->email) && isset($data->password)){
            $email = $data->email;
            $password = $data->password;

            $student_details = $this->student_model->is_email_exists($email);

            if(!empty($student_details))
            {
                if(password_verify($password, $student_details->password)){
                    $token = authorization::generateToken((array)$student_details);

                    $this->response(array(
                        'status' => 1,
                        'message' => 'login successfully',
                        'token' => $token
                    ));
                }
                else{
                    $this->response(array(
                        'status' => 0,
                        'message' => 'Password didnt match'
                    ), parent::HTTP_NOT_FOUND);
                }
            }else{
                $this->response(array(
                    'status' => 0,
                    'message' => 'Email address not found'
                ), parent::HTTP_NOT_FOUND);
            }
        }
        else{
            $this->response(array(
                'status' => 0,
                'message' => 'Login details needed'
            ),parent::HTTP_NOT_FOUND);
        }
    }

    public function student_details_get()
    {
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'];

        try{
            $student_data = authorization::validateToken($token);
            if($student_data===false){
                $this->response(array(
                    'status' => 0,
                    'message' => 'Unauthorise access'
                ), parent::HTTP_UNAUTHORIZED);
            }
            else{
                $this->response(array(
                    'status' => 1,
                    'message' => 'student data',
                    'data' => $student_data
                ), parent::HTTP_OK);
            }
        }
        catch(Exception $ex){
            $this->response(array(
                'status' => 0,
                'message' => $ex->getMessage()
            ), parent::HTTP_INTERNAL_SERVER_ERROR);
            
        }
    }
}