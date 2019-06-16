<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Content extends NS_Controller {
    public function __construct(){
        parent::__construct();
        $this->is_login_admin();

        $this->menu_model        = new master_model('menu');
        $this->user_model        = new master_model('user');
        $this->posts_model        = new master_model('posts');
        $this->galery_model        = new master_model('galery');
        $this->posts_meta_model        = new master_model('posts_meta');
        $this->content_model        = new master_model('content');

        $this->data = array();

        $this->controller_name = "nh_admin/content";
        $this->title = "Content";

        $temp_data = $this->temporary_data;
        $this->data["msg"] = isset($temp_data['msg']) ? $temp_data['msg'] : "";
        $this->data["msg_status"] = isset($temp_data['msg_status']) ? $temp_data['msg_status'] : "";
        $this->data["menu_parent_selector"] = $this->menu_model->all(" AND parent = 1 ");
        $this->data["menu_selector"] = array();
        $this->data["user_selector"] = $this->user_model->all("  ");
        $this->data["boolean_selector"] = array("NO","YES");
        $this->data["status_selector"] = array("publish","pending",'later');
        //crypt()sha1(str) 
    }

    /**
     * index page, for showing list of data
     */
    public function index(){
        $data = $this->data; //take any data possibly set in construct function
        $data['title'] = $this->title; //title to show up in last part of breadcrumb and title of a page
        $data['listener'] = base_url($this->controller_name).'/listener/';
        $data['delete_route'] = base_url($this->controller_name).'/destroy/';
        $data['add_new_link'] = $this->controller_name."/create"; // anchor link for add new data for this module
        $data["breadcrumb"] = array(array("title"=>$this->title,"link"=>"","active"=>"active"));

        $this->template->loadView($this->controller_name.'/index',$data,'admin');
    }

    /**
     * rendering create page
     */
    public function create(){
        $data = $this->data; //take any data possibly set in construct function
        $data['title'] = $this->title." Create"; //title to show up in last part of breadcrumb and title of a page
        $data['type'] = 'create';
        $data['back_link'] = $this->controller_name; // anchor link for add new data for this module
        $data["breadcrumb"] = array(
                                        array("title"=>$this->title,"link"=>base_url($this->controller_name),"active"=>""),
                                        array("title"=>$this->title." Create","link"=>'#',"active"=>"active")
                                    );
        $data['action']= base_url($this->controller_name).'/store';
        $this->template->loadView($this->controller_name.'/form',$data,'admin');
    }

    /**
     * store input data to db
     */
    public function store(){
        if($this->storeValidation()) {
            $crud_data = array();
            $crud_data = $this->input_data;

            $input_posts = array();
            $input_posts["menu_id"] = isset($crud_data["menu_id"]) && $crud_data["menu_id"]!=""?$crud_data["menu_id"]:$crud_data["menu_parent_id"];
            $input_posts["title"] = $crud_data["title"];
            $input_posts["content"] = $crud_data["content"];
            $input_posts["type"] = "article";
            $input_posts["status"] = isset($crud_data["status"]) && $crud_data["status"] != ''?$crud_data['status']:"publish";

            $posts_id = $this->posts_model->add($input_posts);
            
            $input_content = array();
            $input_content["posts_id"] = $posts_id;
            $input_content["writer"] = $crud_data["writer"];
            $input_content["galery_id"] = $crud_data["galery_id"];
            $input_content["photographer"] = $crud_data["photographer"];
            $input_content["lead"] = $crud_data["lead"];
            $input_content["terkait"] = isset($crud_data["terkait"])?$crud_data["terkait"]:NULL;
            $input_content["sumber"] = isset($crud_data["sumber"])?$crud_data["sumber"]:NULL;
            $input_content["photo_caption"] = isset($crud_data["photo_caption"])?$crud_data["photo_caption"]:NULL;
            $input_content["publish_date"] = isset($input_posts["status"]) && $crud_data["status"]=='publish'?date('Y-m-d H:i:s'):NULL;
            if($input_posts['status']=='later'){
                $input_content['publish_date'] = $crud_data['later'];
            }
            $this->content_model->add($input_content);

            $input_meta = array();
            $input_meta["posts_id"] = $posts_id;
            $input_meta["tags"] = $crud_data["tags"];
            $input_meta["key_word"] = $crud_data["key_word"];
            $input_meta["description"] = $crud_data["description"];

            $this->posts_meta_model->add($input_meta);

            $passData['msg'] = "success add new content ".$crud_data["title"];
            $passData['msg_status'] = "success";
            $this->session->set_userdata("temporary_data",$passData);


            redirect($this->controller_name);
        }else{
            $this->create();
        }
    }

    /**
     * rendering read page
     * @param int $id data id
     */
   

    /**
     * rendering update page
     * @param int $id data id
     */
    public function edit($id){


        $data = $this->data; //take any data possibly set in construct function
        $data['title'] = $this->title." Edit"; //title to show up in last part of breadcrumb and title of a page
        $data['type'] = 'Edit';
        $data['back_link'] = $this->controller_name; // anchor link for add new data for this module
        $data["breadcrumb"] = array(
                                        array("title"=>$this->title,"link"=>base_url($this->controller_name),"active"=>""),
                                        array("title"=>$this->title." Edit","link"=>'#',"active"=>"active")
                                    );
        $data['action']= base_url($this->controller_name).'/update/'.$id;
        
        $db_data1 = $this->posts_model->find($id);
        $db_data2 = $this->posts_meta_model->find($id,"posts_id");
        $db_data3 = $this->content_model->find($id,"posts_id");

        $kategori = $this->menu_model->find($db_data1["menu_id"]);
        $galery = $this->galery_model->find($db_data3["galery_id"]);

        $terkait = $db_data3["terkait"];
        $terkait_title="";
        if($terkait!=""){
            $terkait = $this->posts_model->all(" AND id IN ($terkait) ");
            foreach ($terkait as $key => $value) {
                $terkait_title .= $value["title"]."<br>";
            }
        }

        $menu_selector = $this->menu_model->all(" AND parent = ".$kategori['parent']);
        $data["menu_selector"] = $menu_selector;
        $data["db_data"]  = $db_data1+$db_data2+$db_data3;
        
        if($kategori["parent"]!="" && $kategori["parent"]!="1"){
            $data['db_data']["menu_parent_id"] = $kategori['parent'];
        }else{
            $data["db_data"]["menu_parent_id"] = $kategori["id"];
            $menu_selector = $this->menu_model->all(" AND parent = ".$kategori['id']);
            $data["menu_selector"] = $menu_selector;
            unset($data["db_data"]["menu_id"]);
        }

        $data["db_data"]["terkait_title"] = $terkait_title;
        $data['db_data']["image_path"] = isset($galery['image_path'])?base_url($galery['image_path']):"";
        
        $this->template->loadView($this->controller_name.'/form',$data,'admin');
    }

    /**
     * update data in db with id
     * @param int $id data id
     */
    public function update($id){
        if($this->updateValidation()){

            $crud_data = array();
            $crud_data = $this->input_data;
            $meta = $this->posts_meta_model->find($id,"posts_id");
            $content = $this->content_model->find($id,"posts_id");
          

            $input_posts = array();
            $input_posts["menu_id"] = isset($crud_data["menu_id"]) && $crud_data["menu_id"]!=""?$crud_data["menu_id"]:$crud_data["menu_parent_id"];
            $input_posts["title"] = $crud_data["title"];
            $input_posts["content"] = $crud_data["content"];
            $input_posts["status"] = isset($crud_data["status"]) && $crud_data["status"] != ''?$crud_data['status']:"publish";
            $posts_id = $this->posts_model->update($id,$input_posts);

            $input_content = array();
            $input_content["writer"] = $crud_data["writer"];
            $input_content["galery_id"] = $crud_data["galery_id"];
            $input_content["photographer"] = $crud_data["photographer"];
            $input_content["lead"] = $crud_data["lead"];
            $input_content["terkait"] = isset($crud_data["terkait"])?$crud_data["terkait"]:NULL;
            $input_content["sumber"] = isset($crud_data["sumber"])?$crud_data["sumber"]:NULL;
            $input_content["photo_caption"] = isset($crud_data["photo_caption"])?$crud_data["photo_caption"]:NULL;

            $input_content["publish_date"] = isset($input_posts["status"]) && $crud_data["status"]=='publish'?date('Y-m-d H:i:s'):NULL;
            if($input_posts['status']=='later'){
                $input_content['publish_date'] = $crud_data['later'];
            }

            $this->content_model->update($content["id"],$input_content);

            $input_meta = array();
            $input_meta["tags"] = $crud_data["tags"];
            $input_meta["key_word"] = $crud_data["key_word"];
            $input_meta["description"] = $crud_data["description"];

            $this->posts_meta_model->update($meta["id"],$input_meta);


            $passData['msg'] = "success edit  content ".$crud_data["title"];
            $passData['msg_status'] = "success";
            $this->session->set_userdata("temporary_data",$passData);
            redirect($this->controller_name);
        }else{
            $this->edit($id);
        }
    }

    /**
     * delete data in db with id
     * @param $id
     */
    public function destroy($id){
        try {
            $this->posts_model->delete($id);
            return 0;
        }catch (Exception $e){
            return 1;
        }
    }

    /**
     * create form validation
     */
    public function storeValidation(){
        $crud_data = $this->input_data;
        if(!isset($crud_data['title'])){
            $passData['msg'] = "gagal simpan data, data tidak di temukan ";
            $passData['msg_status'] = "error";
            $this->session->set_userdata("temporary_data",$passData); 
            redirect($this->controller_name.'/create');
            return false;           
        }

        return true;
    }

    /**
     * update form validation
     */
    public function updateValidation(){
        $crud_data = $this->input_data;
        if(!isset($crud_data['title'])){
            $passData['msg'] = "gagal simpan data, data tidak di temukan ";
            $passData['msg_status'] = "error";
            $this->session->set_userdata("temporary_data",$passData); 
            redirect($this->controller_name.'/create');
            return false;           
        }

        return true;
    }

    /**
     * datatable listener
     */
    public function listener(){
        $datatables = $this->datatables;

        $menu_id[] = 1;
        $menu_data = $this->menu_model->all(" AND parent = 1 ");
        foreach ($menu_data as $key => $value) {
            $menu_id[]=$value["id"];
            $second = $this->menu_model->all(" AND parent =  ".$value["id"]);
            foreach ($second as $key_1 => $value_1) {
                $menu_id[]=$value_1["id"];
            }
        }
        $menu_id = implode(",", $menu_id);

        $data = $datatables
            ->select(" a.id, a.title, writer.full_name as writer, editor.full_name as editor, c.full_name as created_by, kategori.name as kategori,
                section.name as section, b.publish_date as publish_date, a.status ",false)
            ->from('posts a')
            ->join('content b', 'b.posts_id = a.id',"left")
            ->join('user c', 'c.id = a.created_by',"left")
            ->join('user writer', 'writer.id = b.writer',"left")
            ->join('user editor', 'editor.id = b.editor',"left")
            ->join('menu kategori', 'kategori.id = a.menu_id',"left")
            ->join('menu section', 'section.id = kategori.parent',"left")
            ->where(' a.menu_id IN ('.$menu_id.') ',NULL,false)
            ->where('a.type','article')
            ->where('a.deleted_at',NULL)
            ->edit_column("id", "$1", "select_helper(id)")
            ->add_column("action", "$1", "action_helper('nh_admin/content',id)")
            ->generate();
        echo $data;
    }

    public function get_menu($id)
    {
        $menu_data = $this->menu_model->all(" AND parent = $id ");
        $data = "";

        $data .= '<option value=""></option>';
        foreach ($menu_data as $key => $value) {
            $data .= '<option value="'.$value["id"].'" >'.$value["name"].'</option>';
        }

        $result["data"] = $data;
        $result["msg"] = "success";
        $result["error"] = 0;

        echo json_encode($result);

    }
    public function popup(){
        $data = $this->data; //take any data possibly set in construct function
        $data['title'] = $this->title; //title to show up in last part of breadcrumb and title of a page
        $data['listener'] = base_url($this->controller_name).'/listener_popup/';
        $data["breadcrumb"] = array(array("title"=>$this->title,"link"=>"","active"=>"active"));

        $data["foot"]=$this->load->view("template/includes/admin-foot", "", TRUE);
        $data["head"] = $this->load->view("template/includes/admin-head","", TRUE);
        $this->template->loadView($this->controller_name.'/popup',$data,'');
    }

    /**
     * datatable listener
     */
    public function listener_popup(){
        $datatables = $this->datatables;

        $menu_id[] = 1;
        $menu_data = $this->menu_model->all(" AND parent = 1 ");
        foreach ($menu_data as $key => $value) {
            $menu_id[]=$value["id"];
            $second = $this->menu_model->all(" AND parent =  ".$value["id"]);
            foreach ($second as $key_1 => $value_1) {
                $menu_id[]=$value_1["id"];
            }
        }
        $menu_id = implode(",", $menu_id);

        $data = $datatables
            ->select(" a.id, a.title, writer.full_name as writer, editor.full_name as editor, c.full_name as created_by, kategori.name as kategori,
                section.name as section, b.publish_date as publish_date, a.status ",false)
            ->from('posts a')
            ->join('content b', 'b.posts_id = a.id',"left")
            ->join('user c', 'c.id = a.created_by',"left")
            ->join('user writer', 'writer.id = b.writer',"left")
            ->join('user editor', 'editor.id = b.editor',"left")
            ->join('menu kategori', 'kategori.id = a.menu_id',"left")
            ->join('menu section', 'section.id = kategori.parent',"left")
            ->where(' a.menu_id IN ('.$menu_id.') ',NULL,false)
            ->where('a.deleted_at',NULL)
            ->edit_column("id", "$1", "select_content(id)")
            ->add_column("action", "$1", "action_helper('nh_admin/content',id)")
            ->generate();
        
        echo $data;
    }
    

    public function publish($id)
    {
            $crud_data = array();
            $crud_data = $this->input_data;
            $meta = $this->posts_meta_model->find($id,"posts_id");
            $content = $this->content_model->find($id,"posts_id");
          
            $input_posts = array();
            $input_posts["status"] = "publish";

            $posts_id = $this->posts_model->update($id,$input_posts);
            
            $input_content = array();
            $input_content["publish_date"] = date('Y-m-d H:i:s');

            $this->content_model->update($content["id"],$input_content);

            $content_data= $this->content_model->find($content["id"]);
            
            $passData['msg'] = "success publish  content ".$content_data["title"];
            $passData['msg_status'] = "success";
            $this->session->set_userdata("temporary_data",$passData);
            redirect($this->controller_name);

    }

    public function popup_headline($headline_id=""){
        $data = $this->data; //take any data possibly set in construct function
        $data['title'] = $this->title; //title to show up in last part of breadcrumb and title of a page
        $data['listener'] = base_url($this->controller_name).'/listener_popup_headline/'.$headline_id;
        $data["breadcrumb"] = array(array("title"=>$this->title,"link"=>"","active"=>"active"));
        $data["headline_id"] = $headline_id;
        $data["foot"]=$this->load->view("template/includes/admin-foot", "", TRUE);
        $data["head"] = $this->load->view("template/includes/admin-head","", TRUE);
        $this->template->loadView($this->controller_name.'/popup_headline',$data,'');
    }

    /**
     * datatable listener
     */
    public function listener_popup_headline($headline_id){
        $datatables = $this->datatables;

        $menu_id[] = 1;
        $menu_data = $this->menu_model->all(" AND parent = 1 ");
        foreach ($menu_data as $key => $value) {
            $menu_id[]=$value["id"];
            $second = $this->menu_model->all(" AND parent =  ".$value["id"]);
            foreach ($second as $key_1 => $value_1) {
                $menu_id[]=$value_1["id"];
            }
        }
        $menu_id = implode(",", $menu_id);

        $data = $datatables
            ->select(" a.id, a.title, writer.full_name as writer, editor.full_name as editor, c.full_name as created_by, kategori.name as kategori,
                section.name as section, b.publish_date as publish_date, a.status ",false)
            ->from('posts a')
            ->join('content b', 'b.posts_id = a.id',"left")
            ->join('user c', 'c.id = a.created_by',"left")
            ->join('user writer', 'writer.id = b.writer',"left")
            ->join('user editor', 'editor.id = b.editor',"left")
            ->join('menu kategori', 'kategori.id = a.menu_id',"left")
            ->join('menu section', 'section.id = kategori.parent',"left")
            ->where(' a.menu_id IN ('.$menu_id.') ',NULL,false)
            ->where('a.deleted_at',NULL)
            ->add_column("action", "$1", "action_helper('nh_admin/galery',id,".$headline_id.",'headline')")
            ->generate();
        echo $data;
    }



}