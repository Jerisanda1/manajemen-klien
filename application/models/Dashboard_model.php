
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    // 1. Ambil Data Statistik
    public function get_stats() {
        $today = date('Y-m-d');
        $next_month = date('Y-m-d', strtotime('+30 days'));

        // Statistik Invoice
        $total_invoice = $this->db
            ->count_all('invoice');

        $invoice_belum_bayar = $this->db
            ->where('status', 'Belum dibayar')
            ->count_all_results('invoice');

        $invoice_jatuh_tempo = $this->db
            ->where('status', 'Belum dibayar')
            ->where('jatuh_tempo <=', $today)
            ->where('jatuh_tempo IS NOT NULL', NULL, FALSE)
            ->count_all_results('invoice');

        $stats = [
            // Data Utama
            'total_klien' => $this->db
                ->count_all('clients'),

            'total_project' => $this->db
                ->count_all('project'),

            'total_dokumen' => $this->db
                ->count_all('documents'),

            'project_berjalan' => $this->db
                ->where('status', 'berjalan')
                ->count_all_results('project'),

            // Statistik Invoice
            'total_invoice' => $total_invoice,
            'invoice_belum_bayar' => $invoice_belum_bayar,
            'invoice_jatuh_tempo' => $invoice_jatuh_tempo,

            // Perlu Perhatian: Dokumen
            'doc_akan_berakhir' => $this->db
                ->where('tanggal_berakhir >=', $today)
                ->where('tanggal_berakhir <=', $next_month)
                ->count_all_results('documents'),

            'doc_expired' => $this->db
                ->where('tanggal_berakhir <', $today)
                ->count_all_results('documents'),
        ];

        return $stats;
    }

    // 2. Ambil Aktivitas Terbaru
    public function get_recent_activities($limit = 5) {
        return $this->db
            ->order_by('id', 'DESC')
            ->limit($limit)
            ->get('activity_logs')
            ->result();
    }

    // 3. Fungsi Helper untuk mencatat log
    public function log_activity($type, $description) {
        $this->db->insert('activity_logs', [
            'type' => $type,
            'description' => $description
        ]);
    }
}
