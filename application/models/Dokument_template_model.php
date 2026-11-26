
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dokument_template_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Mengambil semua template beserta nama klien.
     */
    public function get_all()
    {
        return $this->db
            ->select('dt.*, c.nama_klien')
            ->from('document_templates dt')
            ->join('clients c', 'c.id = dt.client_id', 'left')
            ->order_by('dt.client_id IS NOT NULL', 'ASC', false)
            ->order_by('dt.is_default', 'DESC')
            ->order_by('dt.template_name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Mengambil template berdasarkan ID.
     */
    public function get_by_id($id)
    {
        return $this->db
            ->get_where('document_templates', [
                'id' => (int) $id
            ])
            ->row();
    }

    /**
     * Mengambil template aktif.
     * Template global dan template khusus klien.
     */
    public function get_active_templates($client_id = null)
    {
        $this->db
            ->where('is_active', 1)
            ->group_start()
                ->where('client_id IS NULL', null, false);

        if ($client_id !== null) {
            $this->db->or_where('client_id', (int) $client_id);
        }

        $this->db
            ->group_end()
            ->order_by('is_default', 'DESC')
            ->order_by('template_name', 'ASC');

        return $this->db
            ->get('document_templates')
            ->result();
    }

    /**
     * Mengambil template default.
     * Prioritas: template khusus klien, kemudian template global.
     */
   
public function get_default($client_id = null)
{
    if ($client_id !== null) {
        $client_template = $this->db
            ->where('client_id', (int) $client_id)
            ->where('is_default', 1)
            ->where('is_active', 1)
            ->get('document_templates')
            ->row();

        if ($client_template) {
            return $client_template;
        }
    }

    return $this->db
        ->where('client_id IS NULL', null, false)
        ->where('is_default', 1)
        ->where('is_active', 1)
        ->get('document_templates')
        ->row();
    }

    /**
     * Menyimpan template baru.
     */
    public function insert($data)
    {
        $this->db->insert('document_templates', $data);

        return $this->db->insert_id();
    }

    /**
     * Memperbarui template.
     */
    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update('document_templates', $data);
    }

    /**
     * Mengatur template default dalam lingkup yang sama.
     * Template global tidak memengaruhi template klien.
     */
    public function set_default($id)
    {
        $template = $this->get_by_id($id);

        if (!$template) {
            return false;
        }

        $this->db->trans_begin();

        if ($template->client_id === null) {
            $this->db->where('client_id IS NULL', null, false);
        } else {
            $this->db->where(
                'client_id',
                (int) $template->client_id
            );
        }

        $this->db->update('document_templates', [
            'is_default' => 0
        ]);

        $this->db
            ->where('id', (int) $id)
            ->update('document_templates', [
                'is_default' => 1,
                'is_active' => 1
            ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();

        return true;
    }

    /**
     * Mengambil daftar klien untuk dropdown.
     */
    public function get_clients()
    {
        return $this->db
            ->select('id, nama_klien')
            ->order_by('nama_klien', 'ASC')
            ->get('clients')
            ->result();
    }

    /**
     * Memeriksa nama template agar tidak duplikat
     * dalam lingkup klien yang sama.
     */
    public function name_exists($name, $client_id = null, $exclude_id = null)
    {
        $this->db
            ->where('template_name', trim($name));

        if ($client_id === null) {
            $this->db->where('client_id IS NULL', null, false);
        } else {
            $this->db->where('client_id', (int) $client_id);
        }

        if ($exclude_id !== null) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db
            ->count_all_results('document_templates') > 0;
    }
}
