@extends('public.layout.header')

@section('title')
Sharing Session | Rumah Scopus
@stop

@section('konten')
{{--
    Daftar sesi yang sedang dibuka.

    Halaman pemasarannya ada di subdomain tersendiri dan memajang SATU sesi;
    yang di sini jaring pengamannya — tempat mendarat orang yang membuka
    tautan sesi yang sudah ditutup, dan satu-satunya tempat yang memperlihatkan
    semua tanggal sekaligus.
--}}
<section class="sel-latar">
    <div class="container sel-wadah">

        @if (session('error'))
            <div class="sel-galat" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="sel-kepala">
            <span class="sel-lencana"><i class="fas fa-microphone" aria-hidden="true"></i> Sharing Session</span>
            <h1>Belajar langsung dari yang sudah menjalani</h1>
            <p>Sesi daring dua jam, materinya terapan, dan Anda bisa bertanya langsung.</p>
        </div>

        @if ($sesi->isEmpty())
            <div class="sel-kosong">
                <i class="fas fa-calendar-times" aria-hidden="true"></i>
                <p class="sel-kosong-judul">Belum ada sesi yang dijadwalkan</p>
                <p class="sel-kosong-sub">
                    Jadwal berikutnya diumumkan lewat Instagram dan grup WhatsApp Rumah Scopus.
                </p>
            </div>
        @else
            <div class="sel-kisi">
                @foreach ($sesi as $s)
                    @php($sisa = $s->total_kuota === null ? null : (int) $s->sisa_kuota)
                    <article class="sel-kartu">
                        @if ($s->alamat_sampul)
                            <img src="{{ $s->alamat_sampul }}" alt="" class="sel-flyer" loading="lazy">
                        @endif

                        <div class="sel-isi">
                            <div class="sel-pil-baris">
                                <span class="sel-pil">{{ $s->platform ?: 'Daring' }}</span>
                                @if ($sisa !== null && $sisa > 0 && $sisa <= 10)
                                    <span class="sel-pil sel-pil-merah">Tinggal {{ $sisa }} kursi</span>
                                @elseif ($sisa !== null && $sisa < 1)
                                    <span class="sel-pil sel-pil-abu">Penuh</span>
                                @endif
                            </div>

                            <h2>{{ $s->nama }}</h2>

                            <p class="sel-waktu">
                                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                {{ \App\Support\RentangTanggal::tulis(
                                    $s->mulai ? \Carbon\Carbon::parse($s->mulai) : null,
                                    $s->selesai ? \Carbon\Carbon::parse($s->selesai) : null
                                ) ?: 'Menyusul' }}
                                @if ($s->jam) &middot; {{ $s->jam }} @endif
                            </p>

                            @if ($s->pemateri)
                                <p class="sel-pemateri">
                                    <i class="fas fa-user" aria-hidden="true"></i> {{ $s->pemateri }}
                                </p>
                            @endif

                            <div class="sel-kaki">
                                <span class="sel-harga">
                                    {{ (int) $s->biaya > 0
                                        ? 'Rp ' . number_format((int) $s->biaya, 0, ',', '.')
                                        : 'Gratis' }}
                                    <small>per peserta</small>
                                </span>

                                @if ($sisa !== null && $sisa < 1)
                                    <span class="sel-tombol sel-tombol-mati">Kuota penuh</span>
                                @else
                                    <a href="{{ route('public.sharingsession.daftar', [$s->id, $s->token]) }}"
                                        class="sel-tombol">
                                        Daftar <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<style>
    .sel-latar {
        --navy: #0f2b5b;
        --tinta: #1e293b;
        --tinta-2: #64748b;
        --garis: #e2e8f0;

        background: #f8fafc;
        background-image:
            radial-gradient(circle at 85% -5%, rgba(255, 106, 0, .12), transparent 45%),
            radial-gradient(circle at -10% 20%, rgba(15, 43, 91, .1), transparent 45%);
        padding: 110px 0 90px;
        min-height: 100vh;
        font-family: 'Poppins', 'Inter', system-ui, sans-serif;
        color: var(--tinta);
    }

    .sel-wadah { max-width: 1080px; }

    .sel-galat {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 24px;
        padding: 14px 18px;
        border-radius: 14px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        font-size: .9rem;
    }

    .sel-kepala { max-width: 620px; margin: 0 auto 36px; text-align: center; }

    .sel-lencana {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 14px;
        padding: 7px 16px;
        border-radius: 999px;
        background: linear-gradient(135deg, #ff8c00, #e65c00);
        color: #fff;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    /* margin: 0 !important — style.css Stisla memaksa margin-left pada .fas
       di dalam tautan dan rentang tertentu dengan bobot (0,4,1). */
    .sel-lencana > .fas { margin: 0 !important; }

    .sel-kepala h1 {
        margin: 0 0 10px;
        font-size: clamp(1.6rem, 4vw, 2.3rem);
        font-weight: 800;
        line-height: 1.25;
        color: var(--navy);
    }

    .sel-kepala p { margin: 0; font-size: 1rem; color: var(--tinta-2); }

    /* auto-fit + minmax: tiga kartu di layar lebar, dua di tablet, satu di
       ponsel — tanpa titik putus yang harus dijaga sendiri. */
    .sel-kisi {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 22px;
    }

    .sel-kartu {
        display: flex;
        flex-direction: column;
        border-radius: 20px;
        border: 1px solid var(--garis);
        background: #fff;
        overflow: hidden;
        box-shadow: 0 20px 50px -34px rgba(15, 43, 91, .5);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .sel-kartu:hover { transform: translateY(-4px); box-shadow: 0 26px 56px -30px rgba(15, 43, 91, .55); }

    .sel-flyer { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; }

    /* flex: 1 supaya kaki kartu rata bawah walau judulnya beda panjang. */
    .sel-isi { flex: 1 1 auto; display: flex; flex-direction: column; padding: 20px; }

    .sel-pil-baris { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }

    .sel-pil {
        padding: 5px 11px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: .72rem;
        font-weight: 700;
    }

    .sel-pil-merah { background: #fee2e2; color: #b91c1c; }
    .sel-pil-abu { background: #f1f5f9; color: #64748b; }

    .sel-isi h2 {
        margin: 0 0 10px;
        font-size: 1.12rem;
        font-weight: 800;
        line-height: 1.4;
        color: var(--navy);
    }

    .sel-waktu,
    .sel-pemateri {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 6px;
        font-size: .86rem;
        color: var(--tinta-2);
    }

    .sel-waktu > .fas,
    .sel-pemateri > .fas { margin: 0 !important; color: #ff6a00; font-size: .85rem; }

    .sel-kaki {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        /* margin-top: auto — kaki menempel ke bawah kartu, jadi tombol semua
           kartu sebaris walau judulnya beda panjang. */
        margin-top: auto;
        padding-top: 16px;
    }

    .sel-harga { font-size: 1.15rem; font-weight: 800; color: var(--navy); }
    .sel-harga small { display: block; font-size: .72rem; font-weight: 500; color: var(--tinta-2); }

    .sel-tombol {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 18px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ff8c00, #e65c00);
        color: #fff;
        font-size: .9rem;
        font-weight: 700;
        text-decoration: none;
    }

    .sel-tombol:hover { color: #fff; text-decoration: none; }
    .sel-tombol > .fas { margin: 0 !important; }

    .sel-tombol-mati { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }

    .sel-kosong {
        max-width: 460px;
        margin: 0 auto;
        padding: 46px 28px;
        border-radius: 20px;
        border: 1px solid var(--garis);
        background: #fff;
        text-align: center;
    }

    .sel-kosong > .fas {
        margin: 0 0 14px !important;
        display: block;
        font-size: 2.4rem;
        /* Abu-abu khusus untuk ketiadaan data; di tempat lain warna aksen. */
        color: #cbd5e1;
    }

    .sel-kosong-judul { margin: 0 0 6px; font-size: 1.05rem; font-weight: 800; color: var(--navy); }
    .sel-kosong-sub { margin: 0; font-size: .9rem; color: var(--tinta-2); }
</style>
@stop
