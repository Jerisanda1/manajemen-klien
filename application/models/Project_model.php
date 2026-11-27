<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
    // Fungsi internal untuk menyaring data berdasarkan pencarian, klien, dan status
    private function filter($q, $klien, $status)
    {
        $this->db->from('project p');
        $this->db->join('clients k', 'k.id = p.klien_id', 'left');

        // Search hanya di nama project
        if ($q !== '') {
            $this->db->like('p.nama_project', $q);
        }

        if ($klien !== '') {
            $this->db->where('p.klien_id', (int) $klien);
        }

        if ($status !== '') {
            $this->db->where('p.status', $status);
        }
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

    // Menghitung total data setelah difilter
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

    // Menghitung project yang sedang berjalan
    public function count_berjalan()
    {
        return $this->db
            ->where('status', 'Berjalan')
            ->count_all_results('project');
    }

    // Mengambil list klien untuk dropdown
    public function get_klien_options()
    {
        return $this->db
            ->select('id, nama_klien')
            ->order_by('nama_klien')
            ->get('clients')
            ->result();
    }

    // Menghapus project
    public function delete($id)
    {
        return $this->db->delete('project', ['id' => (int) $id]);
    }

    // Mengambil detail 1 project beserta data kliennya
    public function get($id)
    {
        return $this->db
            ->select('p.*, k.nama_klien, k.nama_pic, k.no_hp, k.email, k.alamat')
            ->from('project p')
            ->join('clients k', 'k.id = p.klien_id', 'left')
            ->where('p.id', (int) $id)
            ->get()
            ->row();
    }

    // Mengubah status project
    public function update_status($id, $status)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update('project', ['status' => $status]);
    }

    // Menyimpan project baru
    public function insert($data)
    {
        return $this->db->insert('project', $data);
    }

    // Mengupdate data project
    public function update($id, $data)
    {
        $current = $this->db->select('tanggal_selesai')
            ->get_where('project', ['id' => (int) $id])
            ->row();

        if (
            $current &&
            array_key_exists('tanggal_selesai', $data) &&
            $current->tanggal_selesai !== $data['tanggal_selesai']
        ) {
            $data['last_reminder_sent_at'] = NULL;
            $data['wa_reminder_sent_at'] = NULL;
            $data['expired_notified_at'] = NULL;
            $data['wa_expired_notified_at'] = NULL;
        }

        return $this->db
            ->where('id', (int) $id)
            ->update('project', $data);
    }

    public function get_projects_for_reminder($email_enabled = TRUE, $wa_enabled = TRUE)
    {
        if (!$email_enabled && !$wa_enabled) {
            return [];
        }

        $this->db
            ->select('p.id, p.nama_project, p.tanggal_selesai, p.last_reminder_sent_at, p.wa_reminder_sent_at, p.expired_notified_at, p.wa_expired_notified_at, c.nama_klien, c.nama_pic, c.reminder_days_before')
            ->from('project p')
            ->join('clients c', 'c.id = p.klien_id', 'inner')
            ->where('p.status', 'Berjalan')
            ->where('p.tanggal_selesai IS NOT NULL', null, false);

        $this->db->group_start();
        if ($email_enabled) {
            $this->db->group_start()
                ->group_start()
                    ->where('DATEDIFF(p.tanggal_selesai, CURDATE()) BETWEEN 0 AND c.reminder_days_before', null, false)
                    ->where('p.last_reminder_sent_at IS NULL', null, false)
                ->group_end()
                ->or_group_start()
                    ->where('DATEDIFF(p.tanggal_selesai, CURDATE()) < 0', null, false)
                    ->where('p.expired_notified_at IS NULL', null, false)
                ->group_end()
            ->group_end();
        }
        if ($wa_enabled) {
            if ($email_enabled) {
                $this->db->or_group_start();
            } else {
                $this->db->group_start();
            }
            $this->db->group_start()
                ->group_start()
                    ->where('DATEDIFF(p.tanggal_selesai, CURDATE()) BETWEEN 0 AND c.reminder_days_before', null, false)
                    ->where('p.wa_reminder_sent_at IS NULL', null, false)
                ->group_end()
                ->or_group_start()
                    ->where('DATEDIFF(p.tanggal_selesai, CURDATE()) < 0', null, false)
                    ->where('p.wa_expired_notified_at IS NULL', null, false)
                ->group_end()
            ->group_end()
            ->group_end();
        }
        $this->db->group_end();

        return $this->db->order_by('p.tanggal_selesai', 'ASC')->get()->result();
    }

    public function mark_reminder_sent($id)
    {
        return $this->db->where('id', (int) $id)
            ->update('project', ['last_reminder_sent_at' => date('Y-m-d H:i:s')]);
    }

    public function mark_reminder_wa_sent($id)
    {
        return $this->db->where('id', (int) $id)
            ->update('project', ['wa_reminder_sent_at' => date('Y-m-d H:i:s')]);
    }

    public function mark_expired_notified($id)
    {
        return $this->db->where('id', (int) $id)
            ->update('project', ['expired_notified_at' => date('Y-m-d H:i:s')]);
    }

    public function mark_expired_wa_notified($id)
    {
        return $this->db->where('id', (int) $id)
            ->update('project', ['wa_expired_notified_at' => date('Y-m-d H:i:s')]);
    }

    // =========================================
    // DOKUMEN BERDASARKAN PROJECT
    // Selain data dasar, ikut diambil info penyimpanan
    // (storage_type, device_id, file_name, file_hash) untuk ikon lokasi,
    // pratinjau lokal, sinkron ke server, dan hapus file lokal.
    // =========================================
    public function get_dokumen_by_project($project_id)
    {
        return $this->db
            ->select('id, nama_dokumen AS nama, jenis_dokumen AS jenis, tanggal_berakhir AS sampai, storage_type, device_id, file_name, file_hash')
            ->where('project_id', (int) $project_id)
            ->order_by('id', 'DESC')
            ->get('documents')
            ->result_array();
    }

    // =========================================
    // PENAWARAN BERDASARKAN PROJECT
    // =========================================
    public function get_penawaran_by_project($project_id)
    {
        return $this->db
            ->select('id, no_penawaran AS no, tanggal_penawaran AS tanggal, status')
            ->where('project_id', (int) $project_id)
            ->order_by('id', 'DESC')
            ->get('penawaran')
            ->result_array();
    }

    // =========================================
    // INVOICE BERDASARKAN PROJECT
    // =========================================
    public function get_invoice_by_project($project_id)
    {
        return $this->db
            ->select('id, no_invoice AS no, jatuh_tempo AS tempo, status')
            ->where('project_id', (int) $project_id)
            ->order_by('id', 'DESC')
            ->get('invoice')
            ->result_array();
    }
}