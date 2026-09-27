/**
 * Menyelaraskan isian formulir dengan Livewire tepat sebelum dikirim.
 *
 * Pengelola kata sandi dan pengisi formulir otomatis sering menyetel nilai
 * isian langsung ke DOM tanpa memicu peristiwa 'input', sehingga Livewire
 * tidak pernah tahu isinya. Akibatnya formulir yang terlihat terisi penuh
 * tetap ditolak dengan pesan "wajib diisi" atau "konfirmasi tidak cocok".
 *
 * Direktif ini membaca isi DOM pada fase capture (sebelum wire:submit
 * berjalan) lalu menaruhnya ke properti komponen tanpa permintaan tambahan.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.directive('sinkron-livewire', (el, _meta, { cleanup }) => {
        const selaraskan = () => {
            const isian = el.querySelectorAll('[wire\\:model], [wire\\:model\\.blur], [wire\\:model\\.live], [wire\\:model\\.lazy]');

            isian.forEach((medan) => {
                const nama = medan.getAttribute('wire:model.blur')
                    || medan.getAttribute('wire:model.live')
                    || medan.getAttribute('wire:model.lazy')
                    || medan.getAttribute('wire:model');

                if (!nama) {
                    return;
                }

                const nilai = medan.type === 'checkbox' ? medan.checked : medan.value;

                // Argumen ketiga false: cukup perbarui keadaan, jangan kirim
                // permintaan sendiri -- biar ikut pada permintaan submit.
                window.Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id')).set(nama, nilai, false);
            });
        };

        el.addEventListener('submit', selaraskan, true);
        cleanup(() => el.removeEventListener('submit', selaraskan, true));
    });
});

/**
 * Memeriksa apakah sebuah kata sandi pernah muncul pada kebocoran data
 * publik, memakai layanan Have I Been Pwned.
 *
 * Kata sandinya TIDAK pernah dikirim ke mana pun. Yang dikirim hanya lima
 * karakter pertama dari sidik SHA-1-nya; layanan mengembalikan seluruh sidik
 * yang berawalan sama, lalu pencocokan sisanya dikerjakan di peramban ini.
 * Cara ini sama dengan yang dipakai Laravel di sisi server.
 *
 * Mengembalikan: 'aman' | 'bocor' | 'gagal'
 */
(() => {
    const ingatan = new Map();

    window.periksaSandiBocor = async function (sandi) {
        // crypto.subtle hanya tersedia pada konteks aman (https atau localhost).
        if (!window.crypto || !window.crypto.subtle || !sandi) {
            return 'gagal';
        }

        try {
            const sidikBuf = await crypto.subtle.digest('SHA-1', new TextEncoder().encode(sandi));
            const sidik = Array.from(new Uint8Array(sidikBuf))
                .map((b) => b.toString(16).padStart(2, '0'))
                .join('')
                .toUpperCase();

            if (ingatan.has(sidik)) {
                return ingatan.get(sidik);
            }

            const awalan = sidik.slice(0, 5);
            const sisa = sidik.slice(5);

            const respons = await fetch(`https://api.pwnedpasswords.com/range/${awalan}`, {
                headers: { 'Add-Padding': 'true' },
            });

            if (!respons.ok) {
                return 'gagal';
            }

            const daftar = await respons.text();
            const ketemu = daftar.split('\n').some((baris) => baris.split(':')[0].trim() === sisa);
            const hasil = ketemu ? 'bocor' : 'aman';

            ingatan.set(sidik, hasil);

            return hasil;
        } catch (e) {
            // Jaringan bermasalah: jangan menghalangi pengguna, server tetap
            // memeriksa ulang saat formulir dikirim.
            return 'gagal';
        }
    };
})();
