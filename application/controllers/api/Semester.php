<?php

require APPPATH.'libraries/REST_Controller.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST");

class Semester extends REST_Controller{
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/semester_model');
        $this->load->helper(array(
            'authorization',
            'jwt'
        ));
    }

    public function create_project_post()
    {
        $data = json_decode(file_get_contents("php://input"));

        $headers = $this->input->request_headers();
        $token = $headers['Authorization'];

        try{
            $student_data = authorization::validateToken($token);

            if($student_data===false)
            {
                $this->response(array(
                    'status' => 0,
                    'message' => 'Unauthorise token'
                ), parent::HTTP_UNAUTHORIZED);
            }
            else{
                $student_id = $student_data->data->id;

                if(isset($data->title) && isset($data->level)  && isset($data->complete_days) && isset($data->semester))
                {
                    $project_arr_data = array(
                        'student_id' => $student_id,
                        'title' => $data->title,
                        'level' => $data->level,
                        'description' => isset($data->description) ? $data->description : "",
                        'complete_days' => $data->complete_days,
                        'semester' => $data->semester
                    );

                    if($this->semester_model->create_project($project_arr_data)){
                        $this->response(array(
                            'status' => 1,
                            'message' => "Project has been created successfully"
                        ), parent::HTTP_OK);
                    }
                    else{
                        $this->response(array(
                            'status' => 0,
                            'message' => 'Failed to create project'
                        ), parent::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
                else{
                    $this->response(array(
                        'status' => 0,
                        'message' => 'All fields are required'
                    ));
                }
            }
        }
        catch(Exception $ex){
            $this->response(array(
                'status' => 0,
                'message' => $ex->getMessage()
            ), parent::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function projects_list_get()
    {
        $projects = $this->semester_model->get_all_projects();

        if(count($projects)>0){
            $this->response(array(
                'status' => 1,
                'message' => 'Projects found',
                'projects' => $projects
            ),parent::HTTP_OK);
        }
        else{
            $this->response(array(
                'status' => 0,
                'message' => 'No Projects Found'
            ), parent::HTTP_NOT_FOUND);
        }
    }

    public function get_student_projects_get()
    {
        $headers = $this->input->request_headers();

        $token = $headers['Authorization'];

        try{
            $student_data = authorization::validateToken($token);

            if($student_data==false){
                $this->response(array(
                    'status' => 0,
                    'message' => 'Unauthorise Access'
                ), parent::HTTP_UNAUTHORIZED);
            }
            else{
                $student_id = $student_data->data->id;
                $projects = $this->semester_model->get_student_projects($student_id);

                $this->response(array(
                    'status' => 1,
                    'message' => 'Projects Found',
                    'projects' => $projects
                ), parent::HTTP_UNAUTHORIZED);
            }
        }
        catch(Exception $ex){
            $this->response(array(
                'status'=>0,
                'message'=>$ex->getMessage()
            ), parent::HTTP_NOT_FOUND);
        }
    }

    public function delete_projects_delete()
    {
        $headers = $this->input->request_headers();

        $token = $headers['Authorization'];

        try{
            $student_data = authorization::validateToken($token);

            if($student_data===false){
                $this->response(array(
                    'status' => 0,
                    'message' => 'Unauthorise Access'
                ), parent::HTTP_UNAUTHORIZED);
            }
            else{
                $student_id = $student_data->data->id;

                if($this->semester_model->delete_projects($student_id)){
                    $this->response(array(
                        'status' => 1,
                        'message' => 'Projects deleted successfully'
                    ), parent::HTTP_OK);
                }
                else{
                    $this->response(array(
                        'status'=>0,
                        'message'=>"Failed to delete projects"
                    ), parent::HTTP_INTERNAL_SERVER_ERROR);
                }

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