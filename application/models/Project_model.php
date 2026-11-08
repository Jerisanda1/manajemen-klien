<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
    private function filter($q, $klien, $status)
    {
        $this->db->from('project p');
        $this->db->join('clients k', 'k.id = p.klien_id', 'left');

        if ($q !== '') {
            $this->db->group_start()
                     ->like('p.nama_project', $q)
                     ->or_like('k.nama_klien', $q)
                     ->group_end();
        }
        if ($klien !== '') $this->db->where('p.klien_id', (int) $klien);
        if ($status !== '') $this->db->where('p.status', $status);
    }

    public function get_list($q, $klien, $status, $limit, $offset)
    {
        $this->filter($q, $klien, $status);
        $this->db->select('p.*, k.nama_klien');
        $this->db->order_by('p.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_filtered($q, $klien, $status)
    {
        $this->filter($q, $klien, $status);
        return $this->db->count_all_results();
    }

    public function count_all()
    {
        return $this->db->count_all('project');
    }

    public function count_berjalan()
    {
        return $this->db->where('status', 'Berjalan')->count_all_results('project');
    }

    public function get_klien_options()
    {
        return $this->db->select('id, nama_klien')->order_by('nama_klien')->get('clients')->result();
    }

    public function delete($id)
    {
        return $this->db->delete('project', ['id' => (int) $id]);
    }

    public function get($id)
{
    return $this->db->select('p.*, k.nama_klien, k.nama_pic, k.no_hp, k.alamat')
                    ->from('project p')
                    ->join('clients k', 'k.id = p.klien_id', 'left')
                    ->where('p.id', (int) $id)
                    ->get()->row();
}

public function update_status($id, $status)
{
    return $this->db->where('id', (int) $id)->update('project', ['status' => $status]);
}

public function insert($data)
{
    return $this->db->insert('project', $data);
}

public function update($id, $data)
{
    return $this->db->where('id', (int) $id)->update('project', $data);
}
}