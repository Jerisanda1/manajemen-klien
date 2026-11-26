
<?php
$config = [];

if (!empty($template->template_config)) {
    $config = json_decode($template->template_config, true) ?: [];
}

$val = function($key, $default = true) use ($config) {
    return isset($config[$key]) ? (bool) $config[$key] : $default;
};

$isEdit = ($mode === 'edit');
$action = $isEdit
    ? site_url('template-dokumen/update')
    : site_url('template-dokumen/simpan');
?>

<div class="container-fluid">

    <div class="mb-4">
        <a href="<?= site_url('template-dokumen') ?>"
           class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left"></i> Kembali ke Template
        </a>

        <h4 class="fw-bold mt-2">
            <?= $isEdit ? 'Edit Template' : 'Tambah Template' ?>
        </h4>

        <p class="text-muted mb-0">
            Atur identitas dan konfigurasi tampilan template dokumen.
        </p>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger">
            <?= $this->session->flashdata('error') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $action ?>">

        <input type="hidden"
               name="<?= $this->security->get_csrf_token_name() ?>"
               value="<?= $this->security->get_csrf_hash() ?>">

        <?php if ($isEdit): ?>
            <input type="hidden" name="id"
                   value="<?= (int) $template->id ?>">
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold py-3">
                Informasi Template
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Nama Template
                    </label>

                    <input type="text"
                           name="template_name"
                           class="form-control"
                           maxlength="150"
                           required
                           value="<?= html_escape($template->template_name ?? '') ?>"
                           placeholder="Contoh: Proforma Invoice Tridatu">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Jenis Dokumen
                    </label>

                    <select name="document_type"
                            class="form-select"
                            required>
                        <option value="Proforma Invoice"
                            <?= (!isset($template->document_type) || $template->document_type === 'Proforma Invoice') ? 'selected' : '' ?>>
                            Proforma Invoice
                        </option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">
                        Penggunaan Template
                    </label>

                    <select name="client_id" class="form-select">
                        <option value="">
                            Template Utama (Semua Klien)
                        </option>

                        <?php foreach ($klien as $k): ?>
                            <option value="<?= (int) $k->id ?>"
                                <?= isset($template->client_id) && (int) $template->client_id === (int) $k->id ? 'selected' : '' ?>>
                                <?= html_escape($k->nama_klien) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <small class="text-muted">
                        Pilih klien jika template hanya digunakan untuk klien tertentu.
                    </small>
                </div>

            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold py-3">
                Pengaturan Tampilan
            </div>

            <div class="card-body">

                <p class="text-muted small">
                    Pilih elemen yang akan ditampilkan pada dokumen.
                </p>

                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_logo"
                                   value="1"
                                   id="show_logo"
                                   <?= $val('show_logo') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_logo">
                                Tampilkan Logo Perusahaan
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_company_info"
                                   value="1"
                                   id="show_company_info"
                                   <?= $val('show_company_info') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_company_info">
                                Informasi Perusahaan
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_client_info"
                                   value="1"
                                   id="show_client_info"
                                   <?= $val('show_client_info') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_client_info">
                                Informasi Klien
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_payment_info"
                                   value="1"
                                   id="show_payment_info"
                                   <?= $val('show_payment_info') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_payment_info">
                                Informasi Pembayaran
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_signature"
                                   value="1"
                                   id="show_signature"
                                   <?= $val('show_signature') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_signature">
                                Tanda Tangan
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_notes"
                                   value="1"
                                   id="show_notes"
                                   <?= $val('show_notes') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_notes">
                                Catatan Dokumen
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">

            <a href="<?= site_url('template-dokumen') ?>"
               class="btn btn-light border">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>
                <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Template' ?>
            </button>

        </div>

    </form>
</div>