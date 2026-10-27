<?php
require_once __DIR__ . "/koneksi.php";

/* ====== PARAMETER (search, filter, sort, paging) ====== */
$q        = isset($_GET['q']) ? trim($_GET['q']) : '';
$predikat = isset($_GET['predikat']) ? strtolower(trim($_GET['predikat'])) : 'all';

// whitelist kolom sorting (supaya aman dari SQL injection)
$sort = (isset($_GET['sort']) && in_array($_GET['sort'], ['namalengkap', 'nilaiakhir'])) ? $_GET['sort'] : 'nilaiakhir';
$dir  = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc') ? 'desc' : 'asc';

// paging: pilihan kelipatan 5
$limit = (isset($_GET['limit']) && in_array((int)$_GET['limit'], [5, 10, 15])) ? (int)$_GET['limit'] : 5;
$page  = (isset($_GET['page']) && (int)$_GET['page'] > 0) ? (int)$_GET['page'] : 1;

/* ====== WHERE (searching nama + filter predikat) ====== */
$where  = [];
$params = [];
$types  = '';
if ($q !== '') {
    $where[]  = "namalengkap LIKE ?";
    $params[] = "%" . $q . "%";
    $types   .= 's';
}
if ($predikat !== 'all' && $predikat !== '') {
    $where[]  = "LOWER(predikat) = ?";
    $params[] = $predikat;
    $types   .= 's';
}
$whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

/* ====== ORDER BY ====== */
// kalau nama sama, tetap dibedakan oleh nilai akhir
if ($sort === 'namalengkap') {
    $orderSql = "ORDER BY namalengkap $dir, nilaiakhir $dir";
} else {
    $orderSql = "ORDER BY nilaiakhir $dir, namalengkap asc";
}

/* ====== HITUNG TOTAL DATA ====== */
$stmtCount = mysqli_prepare($conn, "SELECT COUNT(*) FROM nilaimahasiswa $whereSql");
if ($types !== '') { mysqli_stmt_bind_param($stmtCount, $types, ...$params); }
mysqli_stmt_execute($stmtCount);
mysqli_stmt_bind_result($stmtCount, $totalData);
mysqli_stmt_fetch($stmtCount);
mysqli_stmt_close($stmtCount);

$totalPages = max(1, (int)ceil($totalData / $limit));
if ($page > $totalPages) { $page = $totalPages; }
$offset = ($page - 1) * $limit;

/* ====== AMBIL DATA (LIMIT + OFFSET) ====== */
$stmt = mysqli_prepare($conn, "SELECT * FROM nilaimahasiswa $whereSql $orderSql LIMIT ? OFFSET ?");
$types2  = $types . 'ii';
$params2 = array_merge($params, [$limit, $offset]);
mysqli_stmt_bind_param($stmt, $types2, ...$params2);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

/* ====== HELPER URL (menjaga search/filter/sort saat pindah halaman) ====== */
function url_with($override = []) {
    $base = [
        'q'        => $_GET['q']        ?? '',
        'predikat' => $_GET['predikat'] ?? 'all',
        'sort'     => $_GET['sort']     ?? 'nilaiakhir',
        'dir'      => $_GET['dir']      ?? 'asc',
        'limit'    => $_GET['limit']    ?? 5,
        'page'     => $_GET['page']     ?? 1,
    ];
    return '?' . http_build_query(array_merge($base, $override));
}
// link sorting: klik pertama = asc, klik lagi = desc
function sort_link($col, $sort, $dir) {
    $newDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
    return url_with(['sort' => $col, 'dir' => $newDir, 'page' => 1]);
}
function sort_icon($col, $sort, $dir) {
    if ($sort !== $col) return 'bi-arrow-down-up text-body-secondary';
    return $dir === 'asc' ? 'bi-sort-up' : 'bi-sort-down';
}
$from = $totalData > 0 ? $offset + 1 : 0;
$to   = min($offset + $limit, $totalData);
?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | Users</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;

        // Applications with their own theming opt out of AdminLTE's color mode
        // entirely, here as well as in the bundle.
        if (root.getAttribute('data-lte-color-mode') === 'off') {
          return;
        }

        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
          stored = localStorage.getItem(STORAGE_KEY);
        } catch {
          // localStorage may be unavailable (private mode, sandboxed iframe).
        }
        // Mirror the precedence in color-mode.ts: the visitor's stored choice
        // wins, then a theme this page declared itself, then the OS preference.
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
          resolved = stored;
        } else if (authored === 'dark' || authored === 'light') {
          resolved = authored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
          resolved = 'dark';
        }
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
        // Flag values computed here, so the bundle does not mistake them for a
        // theme the page declared and stop following the OS preference.
        if (resolved !== authored) {
          root.setAttribute('data-lte-theme-resolved', '');
        }
      })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE 4 | Users" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel"
    />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
  
      <!--end::Header-->
      <!--begin::Sidebar-->
      
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Rekap Nilai Mahasiswa</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nilai</li>
                  </ol>
                </nav>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-12">
                <!--begin::Card-->
                <div class="card mb-4">
                  <!--begin::Card Header-->
                  <div class="card-header">
                    <div class="row g-2 align-items-center">
                      <div class="col-12 col-md-4">
                        <h3 class="card-title">Direktori Nilai</h3>
                      </div>
                      <div class="col-12 col-md-8">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                          <form method="get" id="filter-form" class="d-flex flex-wrap gap-2 m-0">
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>" />
                            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($dir); ?>" />
                            <div class="input-group input-group-sm w-auto">
                              <span class="input-group-text">
                                <i class="bi bi-search" aria-hidden="true"></i>
                              </span>
                              <input
                                type="search"
                                id="user-search"
                                name="q"
                                value="<?php echo htmlspecialchars($q); ?>"
                                class="form-control"
                                placeholder="Search Berdasarkan Nama"
                                aria-label="Cari Data"
                                style="width: 190px"
                              />
                              <button type="submit" class="btn btn-outline-secondary">Cari</button>
                            </div>
                            <select
                              id="user-role-filter"
                              name="predikat"
                              class="form-select form-select-sm w-auto"
                              aria-label="Filter by predikat"
                              onchange="this.form.submit()"
                            >
                              <?php
                                $opsiPredikat = [
                                  'all' => 'Semua Predikat',
                                  'sangat memuaskan' => 'Sangat Memuaskan',
                                  'memuaskan' => 'Memuaskan',
                                  'cukup memuaskan' => 'Cukup Memuaskan',
                                  'kurang memuaskan' => 'Kurang Memuaskan',
                                  'tidak lulus' => 'Tidak Lulus',
                                ];
                                foreach ($opsiPredikat as $val => $label) {
                                  $sel = ($predikat === $val) ? 'selected' : '';
                                  echo "<option value=\"$val\" $sel>$label</option>";
                                }
                              ?>
                            </select>
                            <select
                              id="user-limit"
                              name="limit"
                              class="form-select form-select-sm w-auto"
                              aria-label="Jumlah data per halaman"
                              onchange="this.form.submit()"
                            >
                              <?php foreach ([5, 10, 15] as $n) { ?>
                                <option value="<?php echo $n; ?>" <?php echo $limit === $n ? 'selected' : ''; ?>>
                                  <?php echo $n; ?> data
                                </option>
                              <?php } ?>
                            </select>
                          </form>
                          <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-add-user"
                          >
                            <i class="bi bi-person-plus-fill me-1" aria-hidden="true"> </i>
                            Tambah Data Nilai
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--end::Card Header-->
                  <!--begin::Card Body-->
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle m-0">
                        <thead>
                          <tr>
                            <th>NIM</th>
                            <th>
                              <a href="<?php echo sort_link('namalengkap', $sort, $dir); ?>" class="text-decoration-none text-reset">
                                Nama Lengkap <i class="bi <?php echo sort_icon('namalengkap', $sort, $dir); ?>"></i>
                              </a>
                            </th>
                            <th>
                              <a href="<?php echo sort_link('nilaiakhir', $sort, $dir); ?>" class="text-decoration-none text-reset">
                                Nilai Akhir <i class="bi <?php echo sort_icon('nilaiakhir', $sort, $dir); ?>"></i>
                              </a>
                            </th>
                            <th>Nilai Huruf</th>
                            <th>Predikat</th>
                            <th>Tanggal Dibuat</th>
                            <th>Tanggal Diubah</th>
                            <th class="text-end">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($result) > 0) { ?>
                          <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                          <tr>
                            <td>
                              <span class="fw-medium"><?php echo htmlspecialchars($row['nim']); ?></span>
                            </td>
                            <td>
                              <div class="d-flex align-items-center">
                                <?php if (!empty($row['foto']) && file_exists(__DIR__ . '/uploads/' . $row['foto'])) { ?>
                                  <img
                                    src="uploads/<?php echo htmlspecialchars($row['foto']); ?>"
                                    alt="Foto <?php echo htmlspecialchars($row['namalengkap']); ?>"
                                    class="rounded-circle me-2"
                                    style="width: 40px; height: 40px; object-fit: cover"
                                  />
                                <?php } else { ?>
                                  <span
                                    class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center me-2"
                                    style="width: 40px; height: 40px"
                                  >
                                    <i class="bi bi-person-fill" aria-hidden="true"></i>
                                  </span>
                                <?php } ?>
                                <span class="fw-medium"><?php echo htmlspecialchars($row['namalengkap']); ?></span>
                              </div>
                            </td>
                            <td>
                              <span class="fw-medium"><?php echo $row['nilaiakhir']; ?></span>
                            </td>
                            <td>
                              <span class="fw-medium"><?php echo htmlspecialchars($row['nilaihuruf']); ?></span>
                            </td>
                            <td>
                              <span class="fw-medium"><?php echo htmlspecialchars($row['predikat']); ?></span>
                            </td>
                            <td>
                              <span class="fw-medium"><?php echo $row['entry_date']; ?></span>
                            </td>
                            <td>
                              <span class="fw-medium"><?php echo $row['modified_at']; ?></span>
                            </td>
                            <td class="text-end">
                              <div class="btn-group btn-group-sm">
                                <a
                                  href="editnilai.php?id=<?php echo $row['id']; ?>"
                                  class="btn btn-outline-secondary"
                                  aria-label="Edit <?php echo htmlspecialchars($row['namalengkap']); ?>"
                                >
                                  <i class="bi bi-pencil" aria-hidden="true"></i>
                                </a>
                                <button
                                  type="button"
                                  class="btn btn-outline-danger"
                                  data-bs-toggle="modal"
                                  data-bs-target="#modal-delete-user"
                                  data-id="<?php echo $row['id']; ?>"
                                  data-nama="<?php echo htmlspecialchars($row['namalengkap']); ?>"
                                  aria-label="Delete <?php echo htmlspecialchars($row['namalengkap']); ?>"
                                >
                                  <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                              </div>
                            </td>
                          </tr>
                          <?php } ?>
                        <?php } else { ?>
                          <tr>
                            <td colspan="8" class="text-center text-body-secondary py-4">
                              0 results - Data Not Found!
                            </td>
                          </tr>
                        <?php } ?>
                        </tbody>
                      </table>
                    </div>
                    <!-- /.table-responsive -->
                  </div>
                  <!--end::Card Body-->
                  <!--begin::Card Footer-->
                  <div class="card-footer clearfix">
                    <div class="float-start pt-1 fs-7 text-body-secondary">
                      Showing <?php echo $from; ?> to <?php echo $to; ?> of <?php echo $totalData; ?> data
                    </div>
                    <?php if ($totalPages > 1) { ?>
                    <ul class="pagination pagination-sm m-0 float-end">
                      <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url_with(['page' => $page - 1]); ?>" aria-label="Previous"> &laquo; </a>
                      </li>
                      <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                          <a class="page-link" href="<?php echo url_with(['page' => $i]); ?>"><?php echo $i; ?></a>
                        </li>
                      <?php } ?>
                      <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url_with(['page' => $page + 1]); ?>" aria-label="Next"> &raquo; </a>
                      </li>
                    </ul>
                    <?php } ?>
                  </div>
                  <!--end::Card Footer-->
                </div>
                <!--end::Card-->
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->

            <!--begin::Add User Modal-->
            <div
              class="modal fade"
              id="modal-add-user"
              tabindex="-1"
              aria-labelledby="modal-add-user-label"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action='inputnilai.php' method='post' enctype='multipart/form-data'>
                    <div class="modal-header">
                      <h5 class="modal-title" id="modal-add-user-label">Tambah nama Mahasiswa</h5>
                      <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                      ></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label for="new-user-name" class="form-label"> NIM </label>
                        <input
                          type="text" minlength=10 maxlength=10
                          class="form-control"
                          id="new-nim"
                          name="nim"
                          placeholder="e.g. 3202416143"
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-namalengkap" class="form-label"> Nama Lengkap </label>
                        <input
                          type="text"
                          class="form-control"
                          id="new-namalengkap"
                          name="namalengkap"
                          placeholder="Masukkan Nama Lengkap"
                          required
                        />
                        <div class="form-text">Masukkan Nama Lengkap Sesuai KTP</div>
                      </div>
                      <div class="mb-3">
                        <label for="new-nilaiakhir" class="form-label"> Nilai Akhir </label>
                        <input
                          type="number"
                          class="form-control"
                          id="new-nilaiakhir"
                          name="nilaiakhir"
                          placeholder="Masukkan Nilai Akhir" min="0" max="100" step="0.01" 
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-foto" class="form-label"> Foto </label>
                        <input
                          type="file"
                          class="form-control"
                          id="new-foto"
                          name="foto"
                          accept="image/png, image/jpeg"
                        />
                        <div class="form-text">Format JPG/PNG, maksimal 2 MB</div>
                      </div>
                     
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                      </button>
                      <button type="submit" class="btn btn-primary">Tambah Data</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Add User Modal-->

            <!--begin::Delete User Modal-->
            <div
              class="modal fade"
              id="modal-delete-user"
              tabindex="-1"
              aria-labelledby="modal-delete-user-label"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modal-delete-user-label">Delete user</h5>
                    <button
                      type="button"
                      class="btn-close"
                      data-bs-dismiss="modal"
                      aria-label="Close"
                    ></button>
                  </div>
                  <div class="modal-body">
                    <p class="mb-0">
                      Yakin ingin menghapus data <strong id="delete-nama"></strong>?
                      Tindakan ini tidak bisa dibatalkan.
                    </p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                      Cancel
                    </button>
                      <a href="#" id="btn-confirm-delete" class="btn btn-danger">Hapus</a>
                  </div>
                </div>
              </div>
            </div>
            <!--end::Delete User Modal-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Web Programming Semester 5</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2014-2026&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="./js/adminlte.js"></script>
    <script>
      const modalHapus = document.getElementById('modal-delete-user');
      modalHapus.addEventListener('show.bs.modal', function (event) {
        const tombol = event.relatedTarget;
        document.getElementById('btn-confirm-delete').href = 'hapusnilai.php?id=' + tombol.dataset.id;
        document.getElementById('delete-nama').textContent = tombol.dataset.nama;
  });
</script>
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!--begin::Color Mode Toggle-->
    <!-- The light/dark/auto switcher ships in adminlte.js as the ColorMode
     module (since 4.1) — no page script needed. Only the no-flash snippet
     in <head> stays inline, because it must run before first paint. -->
    <!--end::Color Mode Toggle-->

    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
<?php mysqli_stmt_close($stmt); mysqli_close($conn); ?>