@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Clinik Scopus Chat Konsultasi | MIS
@stop

<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-level" content="{{ Auth::user()->level }}">
<!-- ================== STYLE COUNT DOWN ================== -->
<style>
    /* ===== COUNTDOWN CARD ===== */
    .countdown-card {
        background: linear-gradient(to right, #ff3131, #ff914d);
        color: #fff;
        padding: 18px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .25);
    }

    /* ===== RING WRAPPER ===== */
    .countdown-wrapper {
        display: flex;
        justify-content: center;
        gap: 18px;
        margin-top: 15px;
    }

    .ring-box {
        position: relative;
        width: 120px;
        height: 120px;
    }

    /* SVG */
    .countdown-ring {
        transform: rotate(-90deg);
        overflow: visible;
    }

    .ring-bg {
        fill: none;
        stroke: rgba(255, 255, 255, .25);
        stroke-width: 10;
    }

    .ring-progress {
        fill: none;
        stroke: url(#grad);
        stroke-width: 10;
        stroke-linecap: round;
        stroke-dasharray: 326;
        stroke-dashoffset: 326;
        transition: stroke-dashoffset 1s linear;
        filter: url(#ringShadow);
    }

    /* NUMBER */
    .ring-number {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }

    .ring-number span {
        font-size: 28px;
        line-height: 1;
    }

    .ring-number small {
        font-size: 12px;
        opacity: .85;
    }

    /* MOBILE */
    @media(max-width:576px) {
        .ring-box {
            width: 95px;
            height: 95px
        }

        .ring-number span {
            font-size: 24px
        }
    }

    /* ===== PULSE EFFECT < 5 MENIT ===== */
    @keyframes pulseRing {
        0% {
            transform: scale(1);
            filter: drop-shadow(0 0 6px rgba(255, 255, 255, .4));
        }

        50% {
            transform: scale(1.05);
            filter: drop-shadow(0 0 14px rgba(255, 255, 255, .9));
        }

        100% {
            transform: scale(1);
            filter: drop-shadow(0 0 6px rgba(255, 255, 255, .4));
        }
    }

    .pulse {
        animation: pulseRing 1.2s infinite ease-in-out;
    }

    /* ===== SHAKE < 10 DETIK ===== */
    @keyframes shakeHard {
        0% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-4px);
        }

        50% {
            transform: translateX(4px);
        }

        75% {
            transform: translateX(-4px);
        }

        100% {
            transform: translateX(0);
        }
    }

    .shake {
        animation: shakeHard .4s infinite;
    }

    /* ===== DISABLE CHAT ===== */
    .chat-disabled {
        opacity: .6;
        pointer-events: none;
    }

    /* disabled upload gambar & inputan */
    #upload-btn:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    #message:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    #send-btn:disabled {
        cursor: not-allowed;
    }

    #image-upload:disabled {
        cursor: not-allowed;
    }

    /* ===== CHAT STYLE =====*/
    .chat-message {
        display: flex;
        margin-bottom: 10px;
    }

    .chat-message.me {
        justify-content: flex-end;
    }

    .chat-message.other {
        justify-content: flex-start;
    }

    .chat-message .bubble {
        max-width: 70%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.4;
        position: relative;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .chat-message.me .bubble {
        background: linear-gradient(135deg, #ff5858, #f09819);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .chat-message.other .bubble {
        background: linear-gradient(135deg, #56CCF2, #2F80ED);
        color: #fff;
        border-bottom-left-radius: 4px;
    }

    /* Hover efek */
    .chat-message .bubble:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    /* Style teks nama */
    .chat-message .bubble strong {
        display: block;
        margin-bottom: 4px;
        font-size: 13px;
        opacity: 0.9;
    }

    /* Style gambar di chat */
    .chat-message .bubble img {
        margin-top: 6px;
        border-radius: 10px;
        max-width: 160px;
        display: block;
        transition: transform 0.2s;
    }

    .chat-message .bubble img:hover {
        transform: scale(1.05);
    }
</style>

<!-- GRADIENT -->
<svg width="0" height="0">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#ffffff" />
            <stop offset="100%" stop-color="#bcb9b9" />
        </linearGradient>
    </defs>
</svg>

<!-- ================== END ================== -->

@section('content')
<audio id="beep-sound" preload="auto">
    <source src="https://assets.mixkit.co/sfx/preview/mixkit-warning-alarm-beep-991.mp3" type="audio/mpeg">
</audio>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>KONSULTASI CLINIK SCOPUS</h1>
        </div>

        <div class="section-body">
            <div class="card" style="border-radius: 16px;">

                <!-- COUNTDOWN -->
                <div class="countdown-card text-center">
                    <div class="countdown-label" id="countdown-label">
                        Sisa Waktu Konsultasi
                    </div>
                    <div class="countdown-wrapper">

                        <!-- JAM -->
                        <div class="ring-box">
                            <svg class="countdown-ring" width="110" height="110">
                                <circle class="ring-bg" cx="55" cy="55" r="52"></circle>
                                <circle class="ring-progress" id="ring-hour" cx="55" cy="55" r="52"></circle>
                            </svg>
                            <div class="ring-number">
                                <span id="countdown-hour">00</span>
                                <small>Jam</small>
                            </div>
                        </div>

                        <!-- MENIT -->
                        <div class="ring-box">
                            <svg class="countdown-ring" width="110" height="110">
                                <circle class="ring-bg" cx="55" cy="55" r="52"></circle>
                                <circle class="ring-progress" id="ring-minute" cx="55" cy="55" r="52"></circle>
                            </svg>
                            <div class="ring-number">
                                <span id="countdown-minute">00</span>
                                <small>Menit</small>
                            </div>
                        </div>

                        <!-- DETIK -->
                        <div class="ring-box">
                            <svg class="countdown-ring" width="110" height="110">
                                <circle class="ring-bg" cx="55" cy="55" r="52"></circle>
                                <circle class="ring-progress" id="ring-second" cx="55" cy="55" r="52"></circle>
                            </svg>
                            <div class="ring-number">
                                <span id="countdown-second">00</span>
                                <small>Detik</small>
                            </div>
                        </div>

                    </div>
                    <div class="countdown-note" id="countdown-note"></div>
                </div>
                <!-- END COUNTDOWN -->

                <!-- MENAMPILKAN NAMA TRAINER DAN CUSTOMER -->
                <div class="trainer-separator p-3 mb-3 d-flex justify-content-between align-items-center"
                    style="background: #fff5f0; border-left: 4px solid #ff914d; box-shadow: 0 4px 12px rgba(0,0,0,0.08);font-weight: 600; color: #333; font-size: 14px; border-top-left-radius: 15px; border-top-right-radius: 15px; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
                    @if(Auth::id() == $pemesanan->trainer_id)
                    <div>
                        <span class="text-muted small me-2">Customer:</span>
                        <span>{{ $pemesanan->customer->full_name ?? '-' }}</span>
                    </div>
                    @else
                    <div>
                        <span class="text-muted small me-2">Trainer:</span>
                        <span>{{ $pemesanan->trainer->full_name ?? '-' }}</span>
                    </div>
                    @endif
                </div>
                <!-- END MENAMPILKAN NAMA TRAINER DAN CUSTOMER -->

                <!-- CHAT BOX-->
                <div class="container mt-3">
                    <!-- BUBLE CHAT -->
                    <div class="card">
                        <div class="card-body" id="chat-box" style="height: 350px; overflow-y: auto;">
                            @foreach($pemesanan->chats ?? [] as $chat)
                            <div class="chat-message {{ $chat->sender_id == Auth::id() ? 'me' : 'other' }}">
                                <div class="bubble">
                                    <strong>{{ $chat->sender->name }}</strong><br>
                                    {{ $chat->message }}
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="card-footer">
                            <div class="input-group">

                                <!-- Upload Gambar -->
                                <input type="file" id="image-upload" accept="image/*" multiple hidden>
                                <div class="input-group-prepend">
                                    <button class="btn btn-secondary" type="button" id="upload-btn">
                                        <i class="fa fa-image"></i>
                                    </button>
                                </div>

                                <!-- Pesan -->
                                <input type="text" id="message" class="form-control" placeholder="Ketik pesan...">

                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="send-btn" onclick="sendMessage()">Kirim</button>

                                    <button class="btn btn-success d-none" id="back-btn"
                                        onclick="goToHistory()">
                                        <i class="fa fa-arrow-left"></i> Kembali ke Riwayat
                                    </button>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div id="image-preview" class="mt-2 d-flex flex-wrap" style="display:none; gap:10px;"></div>
                        </div>
                    </div>
                </div>
                <!-- END CHAT BOX -->

            </div>
        </div>
    </section>
</div>

// <!--================== JAM SESI BERAKHIR MAKA BERUBAH MENJADI TOMBOL KEMBALI DAN DATA DI DB AUTO KEDELETE ==================-->
<script>
    function goToHistory() {
        fetch("{{ route('chat.clear', $pemesanan->id) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // redirect ke riwayat
                    window.location.href =
                        "{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.index') }}";
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan server', 'error');
            });
    }
</script>
// <!--================== END ==================-->

// <!--================== REAL TIME CHAT PUSHER ==================-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.0/echo.iife.js"></script>
<script>
    const AUTH_ID = @json(Auth::id());
    let lastChatId = @json($lastChatId);

    function appendMessage(chat) {
        const box = document.getElementById('chat-box');
        const isMe = chat.sender_id == AUTH_ID;
        const senderName = chat.sender?.name ?? '';

        let imagesHtml = '';

        if (chat.images) {
            const images = JSON.parse(chat.images);

            images.forEach(img => {
                imagesHtml += `
                <img src="/ClinikScopusChat/${img}"
                     style="max-width:160px;border-radius:10px;margin-top:6px;display:block">
            `;
            });
        }

        const div = document.createElement('div');
        div.className = `chat-message ${isMe ? 'me' : 'other'}`;

        div.innerHTML = `
        <div class="bubble">
            ${chat.message ?? ''}
            ${imagesHtml}
        </div>
    `;

        box.appendChild(div);
        box.scrollTop = box.scrollHeight;


        // zoom gambar pakai SweetAlert
        div.querySelectorAll('img').forEach(img => {
            img.addEventListener('click', () => {
                Swal.fire({
                    imageUrl: img.src,
                    imageAlt: 'Zoom Image',
                    showCloseButton: true,
                    showConfirmButton: false,
                    background: '#000',
                    padding: '0',
                    width: 'auto',
                    customClass: {
                        popup: 'zoom-image-popup'
                    }
                });
            });
        });
    }

    function pollChats() {
        fetch(`{{ route('chat.load', $pemesanan->id) }}?last_id=${lastChatId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.chats.length > 0) {
                    data.chats.forEach(chat => {
                        appendMessage(chat);
                        lastChatId = chat.id;
                    });
                }
            })
            .catch(err => console.error(err));
    }

    setInterval(pollChats, 2000);
</script>
// <!--================== END ==================-->

// <!--================== VALIDASI CHAT ==================-->
<script>
    function showAlert(icon, title, text) {
        Swal.fire({
            icon: icon, // error | warning | info | success
            title: title,
            text: text,
            confirmButtonText: 'OK',
            confirmButtonColor: '#ff3131'
        });
    }

    const ringHour = document.getElementById('ring-hour');
    const ringMinute = document.getElementById('ring-minute');
    const ringSecond = document.getElementById('ring-second');

    const radius = 52;
    const circumference = 2 * Math.PI * radius;

    [ringHour, ringMinute, ringSecond].forEach(r => {
        r.style.strokeDasharray = circumference;
        r.style.strokeDashoffset = circumference;
    });

    function parseTime(time) {
        const [h, m] = time.split('.').map(Number);
        return {
            h,
            m
        };
    }

    const beep = document.getElementById('beep-sound');
    let audioUnlocked = false;

    /* iOS FIX: unlock audio */
    document.addEventListener('click', () => {
        if (!audioUnlocked) {
            beep.play().then(() => {
                beep.pause();
                beep.currentTime = 0;
                audioUnlocked = true;
            }).catch(() => {});
        }
    }, {
        once: true
    });

    // kirim gambar
    const uploadBtn = document.getElementById('upload-btn');
    const imageUpload = document.getElementById('image-upload');
    const imagePreview = document.getElementById('image-preview');

    let selectedFiles = [];
    const MAX_FILES = 5;
    const MAX_SIZE = 2 * 1024 * 1024; // 2MB

    uploadBtn.addEventListener('click', () => {
        imageUpload.click();
    });

    imageUpload.addEventListener('change', () => {
        const files = Array.from(imageUpload.files);

        for (let file of files) {

            // ❌ validasi jumlah
            if (selectedFiles.length >= MAX_FILES) {
                showAlert(
                    'warning',
                    'Batas Upload Tercapai',
                    'Maksimal upload adalah 5 gambar.'
                );
                break;
            }

            // ❌ validasi tipe
            if (!file.type.startsWith('image/')) {
                showAlert(
                    'error',
                    'Format Tidak Didukung',
                    'File yang diunggah harus berupa gambar (JPG, PNG, JPEG).'
                );
                continue;
            }

            // ❌ validasi ukuran
            if (file.size > MAX_SIZE) {
                showAlert(
                    'error',
                    'Ukuran File Terlalu Besar',
                    `Ukuran "${file.name}" melebihi 2 MB.`
                );
                continue;
            }

            // ✅ lolos validasi
            selectedFiles.push(file);

            const reader = new FileReader();
            reader.onload = e => {
                const wrapper = document.createElement('div');
                wrapper.style.position = 'relative';

                wrapper.innerHTML = `
                <img src="${e.target.result}" style="max-width:120px;border-radius:8px;">
                <button class="btn btn-sm btn-danger"
                    style="position:absolute;top:-8px;right:-8px"
                    onclick="removeImage(this)">×</button>
            `;

                imagePreview.appendChild(wrapper);
                imagePreview.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        }

        imageUpload.value = ''; // reset input
    });

    function removeImage(btn) {
        const wrapper = btn.parentElement;
        const index = Array.from(imagePreview.children).indexOf(wrapper);

        selectedFiles.splice(index, 1);
        wrapper.remove();

        if (selectedFiles.length === 0) {
            imagePreview.style.display = 'none';
        }
    }
    // end

    // disabled inputan & button sebelum jadwal di mulai
    function setChatDisabled(disabled = true) {
        const messageInput = document.getElementById('message');
        const sendBtn = document.getElementById('send-btn');
        const uploadBtn = document.getElementById('upload-btn');
        const imageUpload = document.getElementById('image-upload');

        messageInput.disabled = disabled;
        sendBtn.disabled = disabled;
        uploadBtn.disabled = disabled;
        imageUpload.disabled = disabled;

        if (disabled) {
            document.getElementById('chat-box').classList.add('chat-disabled');
        } else {
            document.getElementById('chat-box').classList.remove('chat-disabled');
        }
    }
    // end

    // kirim form
    function sendMessage() {
        const messageInput = document.getElementById('message');

        if (!messageInput.value.trim() && selectedFiles.length === 0) {
            showAlert('warning', 'Pesan Kosong', 'Pesan atau gambar harus diisi');
            return;
        }

        const formData = new FormData();
        formData.append('message', messageInput.value);
        formData.append('pemesanan_id', "{{ $pemesanan->id }}");

        selectedFiles.forEach(file => {
            formData.append('images[]', file);
        });

        fetch('{{ route("chat.send") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // appendMessage(res.chat);

                    messageInput.value = '';
                    selectedFiles = [];
                    imagePreview.innerHTML = '';
                    imagePreview.style.display = 'none';

                    lastChatId = res.chat.id;
                } else {
                    showAlert('error', 'Gagal mengirim', res.message);
                }
            })

            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal mengirim', 'Terjadi kesalahan server, silakan coba lagi.');
            });
    }
    // end

    function startCountdown() {
        const startT = parseTime("{{ $start }}");
        const endT = parseTime("{{ $end }}");

        const bookingDate = new Date("{{ $pemesanan->tanggal_booking }}");

        const start = new Date(bookingDate.getFullYear(), bookingDate.getMonth(), bookingDate.getDate(), startT.h, startT.m, 0);

        const end = new Date(bookingDate.getFullYear(), bookingDate.getMonth(), bookingDate.getDate(), endT.h, endT.m, 0);

        const totalSessionSeconds = Math.floor((end - start) / 1000);
        const label = document.getElementById('countdown-label');
        const note = document.getElementById('countdown-note');

        setInterval(() => {
            const now = new Date();

            /* BELUM MULAI */
            if (now < start) {
                setChatDisabled(true);

                const diff = start - now;
                const h = Math.floor(diff / 3600000);
                const m = Math.floor(diff % 3600000 / 60000);
                const s = Math.floor(diff % 60000 / 1000);

                label.innerText = 'Sesi konsultasi akan dimulai dalam (WIB)';
                note.innerText = 'Silakan menunggu sesuai jadwal';

                updateRing(ringHour, h, 24);
                updateRing(ringMinute, m, 60);
                updateRing(ringSecond, s, 60);

                updateText(h, m, s);
                return;
            }

            /* SELESAI */
            if (now >= end) {
                setChatDisabled(true);

                document.getElementById('message').placeholder =
                    'Waktu konsultasi telah berakhir';

                label.innerText = 'Status Konsultasi';
                note.innerText = 'WAKTU KONSULTASI TELAH BERAKHIR';

                // 🔥 GANTI TOMBOL
                document.getElementById('send-btn').classList.add('d-none');
                document.getElementById('back-btn').classList.remove('d-none');

                showTestimoniPopup();
                updateRing(ringHour, 0, 1);
                updateRing(ringMinute, 0, 1);
                updateRing(ringSecond, 0, 1);
                return;
            }

            /* ======================
             DALAM SESI
            ====================== */
            setChatDisabled(false);
            const remainMs = end - now;
            const remainSeconds = Math.max(0, Math.floor(remainMs / 1000));

            const h = Math.floor(remainSeconds / 3600);
            const m = Math.floor((remainSeconds % 3600) / 60);
            const s = remainSeconds % 60;

            /* ======================
               PROGRESS BASED ON SESSION
            ====================== */
            const progressHour =
                totalSessionSeconds >= 3600 ?
                remainSeconds / totalSessionSeconds * 24 :
                0;

            const progressMinute =
                remainSeconds / totalSessionSeconds * 60;

            /* ======================
               RESET EFFECT
            ====================== */
            [ringHour, ringMinute, ringSecond].forEach(r => {
                r.classList.remove('pulse');
                r.classList.remove('shake');
            });

            /* ======================
               COLOR STATE
            ====================== */
            ringHour.style.stroke = '#2ecc71'; // Hijau
            ringMinute.style.stroke = '#ec8b24'; // Oranye
            ringSecond.style.stroke = '#e74c3c'; // Merah

            /* ======================
               WARNING STATE
            ====================== */
            if (remainSeconds <= 300) { // < 5 menit
                ringSecond.classList.add('pulse');
            }

            if (remainSeconds <= 30 && remainSeconds > 0) {
                beep.currentTime = 0;
                beep.play().catch(() => {});
            }

            if (remainSeconds <= 10 && remainSeconds > 0) {
                document.querySelector('.countdown-card').classList.add('shake');
            } else {
                document.querySelector('.countdown-card').classList.remove('shake');
            }

            /* ======================
               UPDATE RING
            ====================== */
            updateRing(ringHour, progressHour, 24);
            updateRing(ringMinute, progressMinute, 60);
            updateRing(ringSecond, s, 60);

            /* ======================
               UPDATE TEXT
            ====================== */
            updateText(h, m, s);

        }, 1000);
    }

    function updateRing(el, value, max) {
        const offset = circumference - (value / max) * circumference;
        el.style.strokeDashoffset = offset;
    }

    function updateText(h, m, s) {
        document.getElementById('countdown-hour').innerText = String(h).padStart(2, '0');
        document.getElementById('countdown-minute').innerText = String(m).padStart(2, '0');
        document.getElementById('countdown-second').innerText = String(s).padStart(2, '0');
    }

    startCountdown();
</script>
// <!--================== END ==================-->

// <!--================== POP UP TESTIMONI ==================-->
<script>
    let testimonialShown = false;

    function showTestimoniPopup() {
        // Ambil level user dari meta
        const userLevel = document.querySelector('meta[name="user-level"]').content;

        // Hanya tampilkan untuk level 'user'
        if (userLevel !== 'user') return;

        if (testimonialShown) return;
        testimonialShown = true;

        Swal.fire({
            title: 'Terima Kasih 🙏',
            html: `
            <form id="form-testimoni">
                <h6 class="text-left mt-2">⭐ Testimoni Trainer</h6>
                <select id="rating_trainer" class="form-control mb-2" required>
                    <option value="">Pilih Rating</option>
                    <option value="5">⭐⭐⭐⭐⭐ Sangat Puas</option>
                    <option value="4">⭐⭐⭐⭐ Puas</option>
                    <option value="3">⭐⭐⭐ Cukup</option>
                    <option value="2">⭐⭐ Kurang</option>
                    <option value="1">⭐ Tidak Puas</option>
                </select>
                <textarea id="komentar_trainer" class="form-control mb-3"
                    placeholder="Komentar untuk trainer (opsional)"></textarea>

                <hr>

                <h6 class="text-left mt-2">⭐ Testimoni Aplikasi (Web)</h6>
                <select id="rating_aplikasi" class="form-control mb-2" required>
                    <option value="">Pilih Rating</option>
                    <option value="5">⭐⭐⭐⭐⭐ Sangat Baik</option>
                    <option value="4">⭐⭐⭐⭐ Baik</option>
                    <option value="3">⭐⭐⭐ Cukup</option>
                    <option value="2">⭐⭐ Kurang</option>
                    <option value="1">⭐ Buruk</option>
                </select>
                <textarea id="komentar_aplikasi" class="form-control"
                    placeholder="Komentar untuk aplikasi (opsional)"></textarea>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="anonymous">
                    <label class="form-check-label">
                        Kirim sebagai anonim
                    </label>
                </div>
            </form>
        `,
            confirmButtonText: 'Kirim Testimoni',
            confirmButtonColor: '#28a745',
            allowOutsideClick: false,
            preConfirm: () => {
                const ratingTrainer = document.getElementById('rating_trainer').value;
                const ratingApp = document.getElementById('rating_aplikasi').value;

                if (!ratingTrainer || !ratingApp) {
                    Swal.showValidationMessage('Rating trainer dan aplikasi wajib diisi');
                    return false;
                }

                return {
                    rating: ratingTrainer,
                    komentar: document.getElementById('komentar_trainer').value,
                    rating_aplikasi: ratingApp,
                    komentar_aplikasi: document.getElementById('komentar_aplikasi').value,
                    is_anonymous: document.getElementById('anonymous').checked
                };
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            const data = result.value;

            fetch("{{ route('account.Clinik-Scopus-Testimoni.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pemesanan_id: "{{ $pemesanan->id }}",
                        rating: data.rating,
                        komentar: data.komentar,
                        rating_aplikasi: data.rating_aplikasi,
                        komentar_aplikasi: data.komentar_aplikasi,
                        is_anonymous: data.is_anonymous
                    })
                })
                .then(response => response.json())
                .then(resp => {
                    if (resp.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terima kasih!',
                            text: resp.message,
                            confirmButtonColor: '#ff3131'
                        }).then(() => {
                            window.location.href =
                                "{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.index') }}";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: resp.message,
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi kesalahan',
                        text: 'Coba lagi nanti'
                    });
                });
        });
    }
</script>
// <!--================== END ==================-->
@stop