<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    // 1. Ambil Data Statistik
    public function get_stats() {
        $today = date('Y-m-d');
        $next_month = date('Y-m-d', strtotime('+30 days'));

        $stats = [
            'total_klien'     => $this->db->count_all('clients'),
            'total_project'   => $this->db->count_all('project'),
            'total_dokumen'   => $this->db->count_all('documents'),
            // Asumsi ada kolom 'status' di tabel project. Sesuaikan jika beda
            'project_berjalan'=> $this->db->where('status', 'berjalan')->count_all_results('project'),
            'total_invoice'   => 0, // Placeholder untuk rekan Anda
            
            // Perlu Perhatian
            'doc_akan_berakhir' => $this->db->where('tanggal_berakhir >=', $today)
                                           ->where('tanggal_berakhir <=', $next_month)
                                           ->count_all_results('documents'),
            'doc_expired'       => $this->db->where('tanggal_berakhir <', $today)
                                           ->count_all_results('documents'),
            'invoice_belum_bayar' => 0, // Placeholder
            'invoice_jatuh_tempo' => 0  // Placeholder
        ];

        return $stats;
    }

    // 2. Ambil Aktivitas Terbaru
    public function get_recent_activities($limit = 5) {
        return $this->db->order_by('id', 'DESC')->limit($limit)->get('activity_logs')->result();
    }

    // 3. Fungsi Helper untuk mencatat log (bisa dipanggil dari Controller mana saja)
    public function log_activity($type, $description) {
        $this->db->insert('activity_logs', [
            'type' => $type,
            'description' => $description
        ]);
    }
}