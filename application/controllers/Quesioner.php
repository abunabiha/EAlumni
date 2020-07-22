<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Quesioner extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('quesioner_model' => 'quesioner'));
    }

    public function index()
    {
        $id_user = $this->ion_auth->user()->row()->id;
        $num_rows = $this->quesioner->get_num_rows($id_user)->num_rows();
        //echo $this->quesioner->get_num_rows($id_user)->row()->id_quesioner;
        if ($num_rows == 1) {
            $id_quesioner = $this->quesioner->get_num_rows($id_user)->row()->id_quesioner;
            redirect('quesioner/update/'.$id_quesioner.'', 'refresh');
        } else {
            redirect('quesioner/create', 'refresh');
        }
    }

    public function list_admin()
    {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('auth/login', 'refresh');
        } elseif (!$this->ion_auth->is_admin()) { // remove this elseif if you want to enable this for non-admins
            // redirect them to the home page because they must be an administrator to view this
            return show_error('Anda tidak punya akses di halaman ini');
        } else {
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            //partial datatable
            $this->data['_partial_css'] = '<!-- JQuery DataTable Css -->
            <link href="'.base_url('assets/backend').'/plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">';
            $this->data['_partial_js'] = '<!-- Jquery DataTable Plugin Js -->
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/jquery.dataTables.js"></script>
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js"></script>
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js"></script>
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/extensions/export/buttons.flash.min.js"></script>
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/extensions/export/jszip.min.js"></script>
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/extensions/export/pdfmake.min.js"></script>
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/extensions/export/vfs_fonts.js"></script>
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/extensions/export/buttons.html5.min.js"></script>
            <script src="'.base_url('assets/backend').'/plugins/jquery-datatable/extensions/export/buttons.print.min.js"></script>
            <!-- Custom Js -->
            <script src="'.base_url('assets/backend').'/js/pages/tables/jquery-datatable.js"></script>
            ';
            //end partial

            $this->data['is_user'] = $this->ion_auth->user()->row();
            $this->data['_get_quesioner'] = $this->quesioner->get_join();
            $this->data['_view'] = 'quesioner/quesioner_list';
            $this->template->_render_page('layouts/backend', $this->data);
        }
    }

    public function read_admin($id)
    {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('auth/login', 'refresh');
        } elseif (!$this->ion_auth->is_admin()) { // remove this elseif if you want to enable this for non-admins
            // redirect them to the home page because they must be an administrator to view this
            return show_error('Anda tidak punya akses di halaman ini');
        } else {
            $this->data['is_user'] = $this->ion_auth->user()->row();

            $row = $this->quesioner->get_by_id($id);
            if ($row) {
                $this->data['id_quesioner'] = $this->form_validation->set_value('id_quesioner', $row->id_quesioner);
                $this->data['first_name'] = $this->form_validation->set_value('first_name', $row->first_name);
                $this->data['last_name'] = $this->form_validation->set_value('last_name', $row->last_name);
                $this->data['quesioner'] = $this->form_validation->set_value('quesioner', $row->quesioner);
                $this->data['is_tampil'] = $this->form_validation->set_value('is_tampil', $row->is_tampil);

                $this->data['_view'] = 'quesioner/quesioner_read';
                $this->template->_render_page('layouts/backend', $this->data);
            } else {
                $this->session->set_flashdata('message','Data tidak ditemukan');
                redirect(site_url('quesioner/list_admin'));
            }
        }
    }

    public function create()
    {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('auth/login', 'refresh');
        } elseif (!$this->ion_auth->in_group(2)) { // remove this elseif if you want to enable this for non-admins
            // redirect them to the home page because they must be an administrator to view this
            return show_error('Anda tidak punya akses di halaman ini');
        } else {
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $this->data['is_user'] = $this->ion_auth->user()->row();
            $id = $this->ion_auth->user()->row()->id;
            $this->data['button'] = 'Tambah';
            $this->data['action'] = site_url('quesioner/create_action');
            $this->data['id_quesioner'] = array(
                'name' => 'id_quesioner',
                'type' => 'hidden',
                'value' => $this->form_validation->set_value('id_quesioner'),
                'class' => 'form-control',
            );
            $this->data['id_user'] = array(
                'name' => 'id_user',
                'type' => 'hidden',
                'value' => $this->form_validation->set_value('id_user', $id),
            );
            $this->data['quesioner'] = array(
                'name' => 'quesioner',
                'type' => 'text',
                'value' => $this->form_validation->set_value('quesioner'),
                'class' => 'form-control',
                'required' => 'required',
            );
            $this->data['is_tampil'] = array(
                'name' => 'is_tampil',
                'type' => 'hidden',
                'value' => $this->form_validation->set_value('is_tampil', 'Tidak'),
                'class' => 'form-control',
            );

            $this->data['_view'] = 'quesioner/quesioner_form';
            $this->template->_render_page('layouts/backend', $this->data);
        }
    }

    public function create_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->create();
        } else {
            $data = array(
                'id_user' => $this->input->post('id_user', true),
                'quesioner' => $this->input->post('quesioner', true),
                'is_tampil' => $this->input->post('is_tampil', true),
                );

            $this->quesioner->insert($data);
            $this->session->set_flashdata('message','Data berhasil ditambahkan');
            redirect(site_url('quesioner'));
        }
    }

    public function update($id)
    {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('auth/login', 'refresh');
        } elseif (!$this->ion_auth->in_group(2)) { // remove this elseif if you want to enable this for non-admins
            // redirect them to the home page because they must be an administrator to view this
            return show_error('Anda tidak punya akses di halaman ini');
        } else {
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $this->data['is_user'] = $this->ion_auth->user()->row();

            $row = $this->quesioner->get_by_id($id);
            $id_user = $this->ion_auth->user()->row()->id;
            if ($row) {
                $this->data['button'] = 'Ubah';
                $this->data['action'] = site_url('quesioner/update_action');
                $this->data['id_quesioner'] = array(
                    'name' => 'id_quesioner',
                    'type' => 'hidden',
                    'value' => $this->form_validation->set_value('id_quesioner', $row->id_quesioner),
                );
                $this->data['id_user'] = array(
                    'name' => 'id_user',
                    'type' => 'hidden',
                    'value' => $this->form_validation->set_value('id_user', $id_user),
                );
                $this->data['username'] = array(
                    'name' => 'username',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('username', $row->username),
                    'class' => 'form-control',
                    'readonly' => 'readonly',
                );
                $this->data['quesioner'] = array(
                    'name' => 'quesioner',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('quesioner', $row->quesioner),
                    'class' => 'form-control',
                    'required' => 'required',
                );
                $this->data['is_tampil'] = array(
                    'name' => 'is_tampil',
                    'type' => 'hidden',
                    'value' => $this->form_validation->set_value('is_tampil', $row->is_tampil),
                );
                $this->data['_partial_css'] = '<!-- Bootstrap Select Css -->
                <link href="'.base_url('assets/backend').'/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />';
                $this->data['_partial_js'] = '<!-- Select Plugin Js -->
                <script src="'.base_url('assets/backend').'/plugins/bootstrap-select/js/bootstrap-select.js"></script>';

                $this->data['_view'] = 'quesioner/quesioner_form';
                $this->template->_render_page('layouts/backend', $this->data);
            } else {
                $this->session->set_flashdata('message','Data Tidak Ditemukan');
                redirect(site_url('quesioner/create'));
            }
        }
    }

    public function update_admin($id)
    {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('auth/login', 'refresh');
        } elseif (!$this->ion_auth->is_admin()) { // remove this elseif if you want to enable this for non-admins
            // redirect them to the home page because they must be an administrator to view this
            return show_error('Anda tidak punya akses di halaman ini');
        } else {
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $this->data['is_user'] = $this->ion_auth->user()->row();

            $row = $this->quesioner->get_by_id($id);

            if ($row) {
                $this->data['button'] = 'Ubah';
                $this->data['action'] = site_url('quesioner/update_action_admin');
                $this->data['id_quesioner'] = array(
                    'name' => 'id_quesioner',
                    'type' => 'hidden',
                    'value' => $this->form_validation->set_value('id_quesioner', $row->id_quesioner),
                );
                $this->data['id_user'] = array(
                    'name' => 'id_user',
                    'type' => 'hidden',
                    'value' => $this->form_validation->set_value('id_user', $row->id_user),
                );
                $this->data['username'] = array(
                    'name' => 'username',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('username', $row->username),
                    'class' => 'form-control',
                    'readonly' => 'readonly',
                );
                $this->data['quesioner'] = array(
                    'name' => 'quesioner',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('quesioner', $row->quesioner),
                    'class' => 'form-control',
                    'readonly' => 'readonly',
                );
                $this->data['is_tampil'] = array(
                    'name' => 'is_tampil',
                    'value' => $this->form_validation->set_value('is_tampil', $row->is_tampil),
                    'class' => 'form-control show-tick',
                );
                $this->data['_partial_css'] = '<!-- Bootstrap Select Css -->
                <link href="'.base_url('assets/backend').'/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />';
                $this->data['_partial_js'] = '<!-- Select Plugin Js -->
                <script src="'.base_url('assets/backend').'/plugins/bootstrap-select/js/bootstrap-select.js"></script>';

                $this->data['_view'] = 'quesioner/tquesioner_form_tampil';
                $this->template->_render_page('layouts/backend', $this->data);
            } else {
                $this->session->set_flashdata('message','Data Tidak Ditemukan');
                redirect(site_url('quesioner/list_admin'));
            }
        }
    }

    public function update_action_admin()
    {
        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->update($this->input->post('id_quesioner', true));
        } else {
            $data = array(
            'id_user' => $this->input->post('id_user', true),
            'quesioner' => $this->input->post('quesioner', true),
            'is_tampil' => $this->input->post('is_tampil', true),
        );

            $this->quesioner->update($this->input->post('id_quesioner', true), $data);
            $this->session->set_flashdata('message','Data berhasil di ubah');
            redirect(site_url('quesioner/list_admin'));
        }
    }

    public function update_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->update($this->input->post('id_quesioner', true));
        } else {
            $data = array(
            'id_user' => $this->input->post('id_user', true),
            'quesioner' => $this->input->post('quesioner', true),
            'is_tampil' => $this->input->post('is_tampil', true),
        );

            $this->quesioner->update($this->input->post('id_quesioner', true), $data);
            $this->session->set_flashdata('message','Data berhasil di ubah');
            redirect(site_url('quesioner'));
        }
    }

    public function _rules()
    {
        $this->form_validation->set_rules('id_user', 'id user', 'trim|required');
        $this->form_validation->set_rules('quesioner', 'quesioner', 'trim|required');
        $this->form_validation->set_rules('is_tampil', 'is tampil', 'trim');

        $this->form_validation->set_rules('id_quesioner', 'id_quesioner', 'trim');
        //$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }
}

/* End of file Quesioner.php */
/* Location: ./application/controllers/Quesioner.php */
