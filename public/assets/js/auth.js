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
