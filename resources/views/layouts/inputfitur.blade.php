<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');

    :root {
        --accent: #6366f1;
        --accent-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --bg-main: #f4f7ff;
        --card-bg: rgba(255, 255, 255, 0.9);
        --radius-xl: 24px;
        --shadow-soft: 0 20px 40px rgba(0, 0, 0, 0.04);
        --text-dark: #1e293b;
    }

    .main-content {
        padding-top: 110px !important;
        background-color: var(--bg-main);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }

    /* 🔹 Header Glassmorphism */
    .section-header-modern {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border-radius: var(--radius-xl);
        padding: 25px 30px;
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: var(--shadow-soft);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header-modern h1 {
        font-size: 28px !important;
        font-weight: 800 !important;
        letter-spacing: -1.5px;
        background: linear-gradient(to right, #1e293b, #6366f1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        display: inline-block;
    }

    /* 🔹 Card Neo Design */
    .card-neo {
        background: var(--card-bg);
        backdrop-filter: blur(15px);
        border-radius: var(--radius-xl);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: var(--shadow-soft);
        overflow: hidden;
    }

    .form-group label {
        font-weight: 700;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
        display: block;
    }

    .form-control-modern {
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        padding: 12px 18px;
        height: auto;
        font-weight: 600;
        transition: all 0.3s ease;
        background: rgba(248, 250, 252, 0.8);
        width: 100%;
    }

    .form-control-modern:focus {
        border-color: var(--accent);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    /* 🔹 PPN Input Wrapper */
    .input-group-modern {
        position: relative;
        display: flex;
        align-items: center;
        border-radius: 14px;
        transition: all 0.3s ease;
    }

    .form-control-ppn {
        padding-right: 45px !important;
    }

    .input-suffix {
        position: absolute;
        right: 18px;
        font-weight: 800;
        color: #94a3b8;
        pointer-events: none;
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .modern-prefix {
        position: absolute;
        left: 18px;
        font-weight: 800;
        color: #94a3b8;
        pointer-events: none;
        font-size: 14px;
        transition: color 0.3s ease;
        z-index: 5;
        top: 50%;
        transform: translateY(-50%);
    }

    .modern-prefix+.form-control-modern {
        padding-left: 45px !important;
    }

    .form-control-modern:focus+.input-suffix.modern-prefix {
        color: var(--accent);
    }

    /* 🔹 Buttons */
    .btn-modern {
        border-radius: 16px;
        padding: 14px 28px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        text-decoration: none !important;
    }

    .btn-save {
        background: var(--accent-gradient);
        color: white;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }

    .btn-save:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
        color: white;
    }

    .btn-update {
        background: var(--accent-gradient);
        color: white;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }

    .btn-update:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
        color: white;
    }

    .btn-back {
        background: #fff;
        color: #64748b;
        border: 1.5px solid #e2e8f0;
    }

    .btn-back:hover {
        background: #f8fafc;
        transform: translateY(-3px);
        color: #1e293b;
    }

    .badge-required {
        color: #f43f5e;
        margin-left: 4px;
    }

    /* 🔹 Memaksa Select2 mengikuti gaya Modern */
    .select2-container--default .select2-selection--single {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        /* Warna border halus */
        border-radius: 12px !important;
        /* Radius modern */
        height: 46px !important;
        /* Sesuaikan tinggi */
        display: flex !important;
        align-items: center !important;
        transition: all 0.3s ease !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02) !important;
    }

    /* Efek saat Focus/Diklik */
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #6366f1 !important;
        /* Warna accent */
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
        background: #fff !important;
    }

    /* Mengatur teks di dalam Select2 */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        padding-left: 15px !important;
    }

    /* Mengatur Arrow (Panah) Select2 */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px !important;
        right: 10px !important;
    }

    /* Style Dropdown yang terbuka */
    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
        z-index: 9999;
    }

    .form-control-modern[readonly] {
        background-color: #f1f5f9 !important;
        color: #0b0b0b !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }

    .input-group-modern:has(input[readonly]) {
        background-color: #f1f5f9 !important;
        cursor: not-allowed !important;
        border-color: #e2e8f0 !important;
    }

    .input-group-modern:has(input[readonly]):focus-within {
        border-color: #e2e8f0 !important;
        box-shadow: none !important;
        background-color: #f1f5f9 !important;
    }

    /* Input biasa yang disabled */
    .form-control-modern:disabled,
    .form-control-modern[disabled] {
        background-color: #f1f5f9 !important;
        color: #64748b !important;
        cursor: not-allowed !important;
    }

    /* Khusus untuk Select2 yang disabled */
    .select2-container--disabled .select2-selection--single {
        background-color: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    /* Jika ada input group pembungkus */
    .input-group-modern:has(input[disabled]),
    .input-group-modern:has(select[disabled]) {
        background-color: #f1f5f9 !important;
    }

    /* Gaya untuk Select2 yang disabled */
    .select2-container--disabled .select2-selection--single {
        background-color: #f1f5f9 !important;
        /* Warna abu-abu yang sama */
        border: 1px solid #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    /* Mengubah warna teks di dalam Select2 yang disabled agar terlihat "mati" */
    .select2-container--disabled .select2-selection__rendered {
        color: #64748b !important;
    }
</style>