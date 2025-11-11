<table class="table table-bordered">
    <thead>
        <tr>
            <th scope="col" style="text-align: center;" rowspan="2">NO.</th>
            <th scope="col" rowspan="2" style="text-align: center;">Email</th>
            <th scope="col" rowspan="2" style="text-align: center;">Username</th>
            <th scope="col" rowspan="2" style="text-align: center;">Telp</th>
            <th scope="col" rowspan="2" style="text-align: center;">Verifikasi</th>
            <th scope="col" rowspan="2" style="text-align: center;">Role</th>
            <th scope="col" rowspan="2" style="text-align: center;">Status</th>
            <th scope="col" style="width: 10%;text-align: center">Action</th>
        </tr>
    </thead>
    <tbody id="customerTable">
        @php $no = $users->firstItem(); @endphp
        @forelse ($users as $item)
        <tr>
            <th scope="row" style="text-align: center">{{ $no++ }}</th>
            <td style="display: flex; align-items: center; gap: 10px;">
                <img src="{{ $item->gambar ? asset('assets/img/profil/' . $item->gambar) : asset('assets/img/profil/no-image.jpg') }}"
                    alt="Foto Profil" style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
                <span>{{ $item->email }}</span>
            </td>
            <td style="text-align: center;">{{ $item->username }}</td>
            <td style="text-align: center;">{{ $item->telp }}</td>
            <td style="text-align: center;">
                @if ($item->email_verified_at)
                <span class="badge bg-success" style="padding: 6px 12px; border-radius: 6px;">
                    Sudah Diverifikasi
                </span>
                @else
                <span class="badge bg-danger" style="padding: 6px 12px; border-radius: 6px;">
                    Belum Diverifikasi
                </span>
                @endif
            </td>
            <td style="text-align: center;">{{ $item->level }}</td>
            <td style="text-align: center;">
                @if ($item->status == 'active')
                <span class="badge bg-success" style="padding: 6px 12px; border-radius: 6px;">
                    Active
                </span>
                @else
                <span class=" badge bg-danger" style="padding: 6px 12px; border-radius: 6px;">
                    Non Active
                </span>
                @endif
            </td>
            <td class=" text-center align-middle">
                <div class="d-flex justify-content-center align-items-center"
                    style="gap: 6px; flex-wrap: nowrap; min-height: 32px;">

                    <!-- Tombol Edit -->
                    <a href="{{ route('account.pengguna.edit', $item->id) }}"
                        class="btn btn-warning d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 28px; height: 28px; padding: 0; border-radius: 6px; display: inline-flex;">
                        <i class="fa fa-pencil-alt" style="font-size: 13px; line-height: 1;"></i>
                    </a>

                    <!-- Tombol Delete -->
                    <button onclick="Delete('{{ $item->id }}')"
                        class="btn btn-danger d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 28px; height: 28px; padding: 0; border-radius: 6px; display: inline-flex;">
                        <i class="fa fa-trash" style="font-size: 13px; line-height: 1;"></i>
                    </button>

                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">Tidak ada data</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- Pagination --}}
<div class="d-flex justify-content-center mt-2">
    {{ $users->appends(request()->input())->links('vendor.pagination.bootstrap-4') }}
</div>