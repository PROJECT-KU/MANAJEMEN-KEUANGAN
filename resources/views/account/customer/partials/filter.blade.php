<div class="card border-0 shadow-sm rounded-3 w-100">
    <div class="card-body py-3">
        <!-- Header Filter -->
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2">
            <h6 class="fw-semibold mb-0">
                <i class="bi bi-funnel-fill me-1"></i> Filter Tabel
            </h6>
            <button type="button" id="resetFilter" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-x-circle me-1"></i> Reset
            </button>
        </div>

        <hr class="my-2">

        <!-- Filter Fields -->
        <form class="row gy-3 align-items-end">
            <!-- Status User -->
            <div class="col-6 col-sm-6 col-md-4 col-lg-2 status-col">
                <div class="mb-2">
                    <label for="statusFilter" class="form-label fw-semibold mb-1">Status User</label>
                    <select id="statusFilter" class="form-select form-select-sm rounded-pill">
                        <option value="">Semua</option>
                        <option value="active">Active</option>
                        <option value="non active">Non Active</option>
                    </select>
                </div>
            </div>

            <!-- Verifikasi Email -->
            <div class="col-6 col-sm-6 col-md-4 col-lg-2 px-1 verified-col">
                <div class="mb-2">
                    <label for="verifiedFilter" class="form-label fw-semibold mb-1">Verifikasi Email</label>
                    <select id="verifiedFilter" class="form-select form-select-sm rounded-pill">
                        <option value="">Semua</option>
                        <option value="verified">Sudah Diverifikasi</option>
                        <option value="unverified">Belum Diverifikasi</option>
                    </select>
                </div>
            </div>

            <!-- Email -->
            <div class="col-6 col-sm-6 col-md-4 col-lg-2 email-col">
                <div class="mb-2">
                    <label for="verifiedFilter" class="form-label fw-semibold mb-1">Verifikasi Email</label>
                    <select id="userEmailFilter" class="form-select form-select-sm rounded-pill">
                        <option value="">Semua Email</option>
                        @foreach($users as $user)
                        <option value="{{ $user->email }}">{{ $user->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Jarak Tanggal -->
            <div class="col-12 col-md-6 col-lg-4 date-col">
                <label class="form-label fw-semibold mb-1 label-tanggal">Jarak Tanggal</label>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <input type="date" id="dateStart" class="form-control form-control-sm flex-fill">
                    <span class="mr-2 ml-2 sdtanggal">s/d</span>
                    <input type="date" id="dateEnd" class="form-control form-control-sm flex-fill">
                </div>
            </div>

            <!-- Per Page -->
            <div class="col-6 col-sm-6 col-md-4 col-lg-2 mt-2 display-col">
                <div class="mb-2">
                    <label for="perPage" class="form-label fw-semibold mb-1 d-block">Display Page</label>
                    <select id="perPage" class="form-select form-select-sm rounded-pill">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        </form>

    </div>
</div>

<style>
    /* Margin-left besar hanya untuk laptop ke atas */
    @media (min-width: 992px) {
        .verified-col {
            margin-left: -50px;
        }

        #dateStart {
            margin-left: 50px;
            height: 25px;
        }

        #dateEnd {
            height: 25px;
        }

        .label-tanggal {
            margin-left: 54px;
        }

        .email-col {
            margin-left: -30px;
        }

        .display-col {
            width: 150px;
        }

        .display-col select {
            width: 50%;
            min-width: 0;
            box-sizing: border-box;
        }
    }

    /* Di bawah 992px (tablet & hp), margin normal agar tetap di dalam card */
    @media (max-width: 991.98px) {
        .status-col {
            width: 90%;
            /* Lebar Status User */
        }

        .verified-col {
            margin-left: -8px;
            width: 10%;
        }

        .verified-col select {
            width: 100%;
        }

        .sdtanggal {
            text-align: center;
        }

        .display-col select {
            width: calc(100% + 16px);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filters = ['statusFilter', 'verifiedFilter', 'userEmailFilter', 'dateStart', 'dateEnd', 'perPage'];
        const resetBtn = document.getElementById('resetFilter');
        const tableWrapper = document.getElementById('customerTableWrapper');

        function applyFilter() {
            const params = filters.map(id => {
                const el = document.getElementById(id);
                // sesuaikan param per_page dan email
                if (id === 'perPage') return `per_page=${encodeURIComponent(el.value)}`;
                if (id === 'userEmailFilter') return `email=${encodeURIComponent(el.value)}`;
                return `${id.replace('Filter','').toLowerCase()}=${encodeURIComponent(el.value)}`;
            }).join('&');

            fetch(`{{ route('account.customer.filter') }}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => tableWrapper.innerHTML = html)
                .catch(err => console.error('Filter error:', err));
        }

        filters.forEach(id => document.getElementById(id).addEventListener('change', applyFilter));

        resetBtn.addEventListener('click', function() {
            filters.forEach(id => document.getElementById(id).value = '');
            document.getElementById('perPage').value = '10';
            applyFilter();
        });
    });
</script>