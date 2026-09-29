{{--
  Penomoran halaman bersama MIS.

  Dipakai 20-an layar lewat ->links('vendor.pagination.bootstrap-4'), jadi
  perbaikan di sini berlaku serentak — itu sebabnya ia diubah di templat ini,
  bukan digayakan ulang per layar.

  Dua hal yang ditambahkan dibanding bawaan Bootstrap:

  1. Kalimat "Menampilkan 1-12 dari 102". Deretan angka saja tidak menjawab
     pertanyaan yang paling sering muncul — ini baris ke berapa, dan semuanya
     ada berapa. Tanpa itu orang harus mengalikan sendiri nomor halaman dengan
     isi per halaman.
  2. Tombol sebelum/sesudah berlabel, bukan cuma tanda kurung sudut tunggal
     yang lebarnya 6px dan sulit disentuh di ponsel.

  Kelas .page-item/.page-link dipertahankan: dua puluh layar dan gaya bawaan
  Bootstrap masih bersandar padanya, dan mengganti namanya berarti menyisir
  semuanya sekaligus.
--}}
@if ($paginator->hasPages())
    <nav class="mis-halaman" role="navigation" aria-label="Penomoran halaman">
        <p class="mis-halaman-ket">
            Menampilkan <strong>{{ $paginator->firstItem() }}</strong>&ndash;<strong>{{ $paginator->lastItem() }}</strong>
            dari <strong>{{ number_format($paginator->total()) }}</strong>
        </p>

        <ul class="pagination mis-halaman-daftar">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link mis-halaman-arah" aria-hidden="true">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link mis-halaman-arah" href="{{ $paginator->previousPageUrl() }}"
                        rel="prev" aria-label="Halaman sebelumnya">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                {{-- Pemisah titik tiga --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link mis-halaman-jeda">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            {{-- aria-current menyebut halaman yang sedang dibuka;
                                 kelas .active hanya rupa dan tidak terbaca sama
                                 sekali oleh pembaca layar. --}}
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}"
                                    aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Sesudahnya --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link mis-halaman-arah" href="{{ $paginator->nextPageUrl() }}"
                        rel="next" aria-label="Halaman berikutnya">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link mis-halaman-arah" aria-hidden="true">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
