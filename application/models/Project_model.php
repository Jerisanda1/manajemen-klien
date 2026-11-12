<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
    // Fungsi internal untuk menyaring data berdasarkan pencarian, klien, dan status
    private function filter($q, $klien, $status)
    {
        $this->db->from('project p');
        $this->db->join('clients k', 'k.id = p.klien_id', 'left');

        // Search HANYA di nama project — filter klien ditangani $klien (exact)
        if ($q !== '') {
            $this->db->like('p.nama_project', $q);
        }
        if ($klien !== '') $this->db->where('p.klien_id', (int) $klien);
        if ($status !== '') $this->db->where('p.status', $status);
    }

    // Mengambil daftar project dengan pagination & filter
    public function get_list($q, $klien, $status, $limit, $offset)
    {
        $this->filter($q, $klien, $status);
        $this->db->select('p.*, k.nama_klien');
        $this->db->order_by('p.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    // Menghitung total data setelah difilter (untuk pagination)
    public function count_filtered($q, $klien, $status)
    {
        $this->filter($q, $klien, $status);
        return $this->db->count_all_results();
    }

    // Menghitung seluruh data project
    public function count_all()
    {
        return $this->db->count_all('project');
    }

    // Menghitung project yang sedang berjalan (Untuk Dashboard)
    public function count_berjalan()
    {
        return $this->db->where('status', 'Berjalan')->count_all_results('project');
    }

    // Mengambil list Klien untuk dropdown
    public function get_klien_options()
    {
        return $this->db->select('id, nama_klien')->order_by('nama_klien')->get('clients')->result();
    }

    // Menghapus project
    public function delete($id)
    {
        return $this->db->delete('project', ['id' => (int) $id]);
    }

    // Mengambil detail 1 project beserta data Kliennya
    public function get($id)
    {
        return $this->db->select('p.*, k.nama_klien, k.nama_pic, k.no_hp, k.email, k.alamat')
                        ->from('project p')
                        ->join('clients k', 'k.id = p.klien_id', 'left')
                        ->where('p.id', (int) $id)
                        ->get()->row();
    }

    // Mengubah status project (Berjalan / Selesai / Ditunda)
    public function update_status($id, $status)
    {
        return $this->db->where('id', (int) $id)->update('project', ['status' => $status]);
    }

    // Menyimpan project baru
    public function insert($data)
    {
        return $this->db->insert('project', $data);
    }

    // Mengupdate data project
    public function update($id, $data)
    {
        return $this->db->where('id', (int) $id)->update('project', $data);
    }
}