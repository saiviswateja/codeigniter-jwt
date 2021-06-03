<?php

require APPPATH.'libraries/REST_Controller.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST");

class Branch extends REST_Controller{
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/branch_model');
    }

    public function create_post()
    {
        $data = json_decode(file_get_contents("php://input"));

        if(isset($data->name)){
            $branch_data = array(
                "name" => $data->name
            );
            if($this->branch_model->create($branch_data))
            {
                $this->response(array(
                    'status' => 1,
                    'message' => "Branch has been created"
                ),parent::HTTP_OK);
            }
            else{
                $this->response(array(
                    'status' => 0,
                    'message' => "Failed to create branch"
                ),parent::HTTP_NOT_FOUND);
            }
        }
        else{
            $this->response(array(
                'status' => 0,
                'message' => "Branch name should be needed"
            ),parent::HTTP_NOT_FOUND);
        }
    }

    public function list_get()
    {
        $branch_list = $this->branch_model->get_all_branch();
        if(count($branch_list)>0){
            $this->response(array(
                'status' => 1,
                'message' => "Branch list",
                'data' => $branch_list
            ),parent::HTTP_OK);
        }
        else{
            $this->response(array(
                'status' => 0,
                'message' => "No branches found"
            ),parent::HTTP_NOT_FOUND);
        }
    }

    public function delete_branch_delete()
    {
        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->branch_id)){
            if($this->branch_model->delete_branch($data->branch_id)){
                $this->response(array(
                    'status' => 1,
                    'message' => "branch deleted successfully"
                ),parent::HTTP_OK);
            }
            else{
                $this->response(array(
                    'status' => 0,
                    'message' => "failed to delete branch"
                ),parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else{
            $this->response(array(
                'status' => 0,
                'message' => "branch id needed"
            ),parent::HTTP_NOT_FOUND);
        }
    }
}