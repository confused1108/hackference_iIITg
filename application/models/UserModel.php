<?php
/**
 * Created by PhpStorm.
 * User: Hitesh
 * Date: 28-Sep-18
 * Time: 12:44 AM
 */

Class UserModel extends CI_Model
{
    Public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    public function login_check($data)
    {
        $username = $data['username'];
        $password = $data['password'];
        $result = array();
        $sql = "SELECT * FROM user where name='$username' AND password='$password'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
            $result['user_id'] = $data[0]['user_id'];
            $result['org_id'] = $data[0]['org_id'];
            $result['status'] = true;
        } else {
            $result['status'] = false;
        }
        return $result;
    }
    public function user_profile($user_id){
        $result=array();
        $sql="SELECT * FROM user WHERE user_id=$user_id";
        $query=$this->db->query($sql);
        $result['userdata']=$query->result_array();
        return $result;
    }
    public function update_profile($data){
        $name=$data['name'];
        $address=$data['address'];
        $user_id=$data['user_id'];
        $phone=$data['phone'];
        $email=$data['email'];

        $sql="UPDATE `user` SET `name`='$name',`address`='$address',`phone`='$phone',`email`='$email' WHERE user_id='$user_id'";
        $query=$this->db->query($sql);
        return 1;
    }
    public function change_password($password){
        $admin_id=$_SESSION['user_id'];
        $sql="UPDATE user SET `password`='$password' WHERE user_id='$admin_id'";
        $query=$this->db->query($sql);
        return 1;
    }
    public function user_old(){
        $sql="SELECT based_on,overall_score,talent_status,assessment_id,candidate.name,candidate.gender,candidate.age,categories.name as cname,candidate.candidate_id,assessment_master.timestamp FROM assessment_master,candidate,categories WHERE candidate.candidate_id=assessment_master.candidate_id && categories.cat_id=assessment_master.cat_id ";
        $query=$this->db->query($sql);
        $result=array();
        $result['userdata']=$query->result_array();
        return $result;
    }
    public function user_view_old($candidate_id){
        $sql = "SELECT assessment_details.input_score , assessment_details.output_score , parameter.name FROM assessment_details,assessment_master,parameter WHERE assessment_master.candidate_id='$candidate_id' AND assessment_master.assessment_id = assessment_details.assessment_id AND  assessment_details.param_id=parameter.param_id ";
        $query=$this->db->query($sql);
        $result=array();
        $result['olddata']=$query->result_array();
        $sql="SELECT assessment_id,based_on,overall_score,talent_status,candidate.name,candidate.gender,candidate.age,categories.name as cname,candidate.candidate_id FROM assessment_master,candidate,categories WHERE candidate.candidate_id=assessment_master.candidate_id && categories.cat_id=assessment_master.cat_id AND assessment_master.candidate_id='$candidate_id'";
        $query=$this->db->query($sql);
        $result['userdata']=$query->result_array();
        return $result;
    }
}

