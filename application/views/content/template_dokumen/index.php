
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Template Dokumen</h4>
            <p class="text-muted mb-0">
                Kelola template Proforma Invoice Tridatu Solution.
            </p>
        </div>

        <a href="<?= site_url('template-dokumen/tambah') ?>"
           class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Template
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No.</th>
                            <th>Nama Template</th>
                            <th>Jenis Dokumen</th>
                            <th>Penggunaan</th>
                            <th>Status</th>
                            <th width="100">Default</th>
                            <th width="190">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!empty($templates)): ?>
                        <?php $no = 1; foreach ($templates as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <div class="fw-semibold">
                                        <?= html_escape($row->template_name) ?>
                                    </div>
                                    <small class="text-muted">
                                        ID: <?= (int) $row->id ?>
                                    </small>
                                </td>

                                <td>
                                    <?= html_escape($row->document_type) ?>
                                </td>

                                <td>
                                    <?php if ($row->client_id === null): ?>
                                        <span class="badge bg-primary-subtle text-primary">
                                            Semua Klien
                                        </span>
                                    <?php else: ?>
                                        <?= html_escape($row->nama_klien ?: 'Klien') ?>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ((int) $row->is_active === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ((int) $row->is_default === 1): ?>
                                        <span class="badge bg-warning text-dark">
                                            Default
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-1 flex-wrap">

                                        <a href="<?= site_url('template-dokumen/edit/'.$row->id) ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <?php if ((int) $row->is_default !== 1 && (int) $row->is_active === 1): ?>
                                            <form method="post"
                                                  action="<?= site_url('template-dokumen/default/'.$row->id) ?>">
                                                <input type="hidden"
                                                       name="<?= $this->security->get_csrf_token_name() ?>"
                                                       value="<?= $this->security->get_csrf_hash() ?>">

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Jadikan Default">
                                                    <i class="bi bi-star"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ((int) $row->is_default !== 1): ?>
                                            <form method="post"
                                                  action="<?= site_url('template-dokumen/status/'.$row->id) ?>"
                                                  onsubmit="return confirm('Ubah status template ini?')">

                                                <input type="hidden"
                                                       name="<?= $this->security->get_csrf_token_name() ?>"
                                                       value="<?= $this->security->get_csrf_hash() ?>">

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        title="Aktif/Nonaktif">
                                                    <i class="bi bi-power"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Belum ada template dokumen.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>