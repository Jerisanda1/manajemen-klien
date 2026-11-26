
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Template_dokumen extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Dokument_template_model', 'template_model');
        $this->load->model('Dashboard_model');
        $this->load->library('form_validation');
        $this->load->helper(['form', 'url']);
    }

    public function index()
    {
        $data = [
            'title'     => 'Template Dokumen - Tridatu',
            'content'   => 'content/template_dokumen/index',
            'templates' => $this->template_model->get_all()
        ];

        $this->load->view('main', $data);
    }

    public function tambah()
    {
        $data = [
            'title'   => 'Tambah Template - Tridatu',
            'klien'   => $this->template_model->get_clients(),
            'template' => null,
            'mode'    => 'tambah'
        ];

        $this->load->view('content/template_dokumen/form', $data);
    }

    public function simpan()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $this->form_validation->set_rules(
            'template_name',
            'Nama Template',
            'required|trim|max_length[150]'
        );

        $this->form_validation->set_rules(
            'document_type',
            'Jenis Dokumen',
            'required|trim|max_length[100]'
        );

        $this->form_validation->set_rules(
            'client_id',
            'Klien',
            'integer'
        );

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata(
                'error',
                strip_tags(validation_errors())
            );
            redirect('template-dokumen/tambah');
            return;
        }

        $client_id = $this->input->post('client_id');

        $client_id = ($client_id === '' || $client_id === null)
            ? null
            : (int) $client_id;

        $name = trim($this->input->post('template_name', TRUE));

        if ($this->template_model->name_exists($name, $client_id)) {
            $this->session->set_flashdata(
                'error',
                'Nama template sudah digunakan dalam lingkup tersebut.'
            );
            redirect('template-dokumen/tambah');
            return;
        }

        $config = $this->_get_template_config();

        $data = [
            'client_id'      => $client_id,
            'template_name'  => $name,
            'document_type'  => $this->input->post('document_type', TRUE),
            'template_config'=> json_encode($config),
            'is_default'     => 0,
            'is_active'      => 1
        ];

        $id = $this->template_model->insert($data);

        if (!$id) {
            $this->session->set_flashdata(
                'error',
                'Template gagal disimpan.'
            );
            redirect('template-dokumen');
            return;
        }

        // Jika belum ada template default pada lingkup ini,
        // jadikan template baru sebagai default.
        $defaultTemplate = $this->template_model->get_default($client_id);

if (!$defaultTemplate) {
    $this->template_model->set_default($id);
}

        $this->Dashboard_model->log_activity(
            'dokumen',
            'Template dokumen baru dibuat: ' . $name
        );

        $this->session->set_flashdata(
            'success',
            'Template berhasil ditambahkan.'
        );

        redirect('template-dokumen');
    }

    public function edit($id)
    {
        $template = $this->template_model->get_by_id($id);

        if (!$template) {
            show_404();
        }

        $data = [
            'title'    => 'Edit Template - Tridatu',
            'klien'    => $this->template_model->get_clients(),
            'template' => $template,
            'mode'     => 'edit'
        ];

        $this->load->view('content/template_dokumen/form', $data);
    }

    public function update()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $id = (int) $this->input->post('id');
        $template = $this->template_model->get_by_id($id);

        if (!$template) {
            show_404();
        }

        $this->form_validation->set_rules(
            'template_name',
            'Nama Template',
            'required|trim|max_length[150]'
        );

        $this->form_validation->set_rules(
            'document_type',
            'Jenis Dokumen',
            'required|trim|max_length[100]'
        );

        $this->form_validation->set_rules(
            'client_id',
            'Klien',
            'integer'
        );

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata(
                'error',
                strip_tags(validation_errors())
            );
            redirect('template-dokumen/edit/' . $id);
            return;
        }

        $client_id = $this->input->post('client_id');

        $client_id = ($client_id === '' || $client_id === null)
            ? null
            : (int) $client_id;

        $name = trim($this->input->post('template_name', TRUE));

        if ($this->template_model->name_exists(
            $name,
            $client_id,
            $id
        )) {
            $this->session->set_flashdata(
                'error',
                'Nama template sudah digunakan dalam lingkup tersebut.'
            );
            redirect('template-dokumen/edit/' . $id);
            return;
        }

        $config = $this->_get_template_config();

        $data = [
            'client_id'       => $client_id,
            'template_name'   => $name,
            'document_type'   => $this->input->post('document_type', TRUE),
            'template_config' => json_encode($config)
        ];

        $success = $this->template_model->update($id, $data);

        if (!$success) {
            $this->session->set_flashdata(
                'error',
                'Template gagal diperbarui.'
            );
            redirect('template-dokumen/edit/' . $id);
            return;
        }

        $this->Dashboard_model->log_activity(
            'dokumen',
            'Template dokumen diperbarui: ' . $name
        );

        $this->session->set_flashdata(
            'success',
            'Template berhasil diperbarui.'
        );

        redirect('template-dokumen');
    }

    public function status($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $template = $this->template_model->get_by_id($id);

        if (!$template) {
            show_404();
        }

        // Template default tidak boleh dinonaktifkan
        // sebelum default lain ditentukan.
        if ((int) $template->is_default === 1) {
            $this->session->set_flashdata(
                'error',
                'Template default tidak dapat dinonaktifkan. Tentukan default lain terlebih dahulu.'
            );
            redirect('template-dokumen');
            return;
        }

        $newStatus = ((int) $template->is_active === 1) ? 0 : 1;

        $this->template_model->update($id, [
            'is_active' => $newStatus
        ]);

        $this->session->set_flashdata(
            'success',
            $newStatus
                ? 'Template berhasil diaktifkan.'
                : 'Template berhasil dinonaktifkan.'
        );

        redirect('template-dokumen');
    }

    public function default($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $success = $this->template_model->set_default($id);

        $this->session->set_flashdata(
            $success ? 'success' : 'error',
            $success
                ? 'Template default berhasil diperbarui.'
                : 'Gagal mengubah template default.'
        );

        redirect('template-dokumen');
    }

    private function _get_template_config()
    {
        return [
            'layout'             => 'standard',
            'page_size'          => 'A4',
            'orientation'        => 'portrait',
            'show_logo'          => $this->input->post('show_logo') ? true : false,
            'show_company_info'  => $this->input->post('show_company_info') ? true : false,
            'show_client_info'   => $this->input->post('show_client_info') ? true : false,
            'show_payment_info'  => $this->input->post('show_payment_info') ? true : false,
            'show_signature'     => $this->input->post('show_signature') ? true : false,
            'show_notes'         => $this->input->post('show_notes') ? true : false,
            'currency'           => 'IDR'
        ];
    }
}
