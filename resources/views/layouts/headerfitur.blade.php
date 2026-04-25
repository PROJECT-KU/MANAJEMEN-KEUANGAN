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

    /* 🔹 Reset Text Decoration */
    a,
    a:hover {
        text-decoration: none !important;
    }

    /* 🔹 Header Modern */
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
        font-weight: 800;
        letter-spacing: -1.5px;
        background: linear-gradient(135deg, #1e293b 0%, #6366f1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        font-size: 28px;
        text-decoration: none !important;
    }

    /* 🔹 Card Neo */
    .card-neo {
        background: var(--card-bg);
        backdrop-filter: blur(15px);
        border-radius: var(--radius-xl);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: var(--shadow-soft);
        overflow: hidden;
    }

    /* 🔹 Search & Action Wrapper */
    .search-action-wrapper {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .form-control-modern {
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        padding: 10px 18px;
        font-weight: 600;
        transition: all 0.3s ease;
        background: rgba(248, 250, 252, 0.8);
    }

    .form-control-modern:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    /* 🔹 Buttons */
    .btn-modern {
        border-radius: 14px;
        padding: 10px 20px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        text-decoration: none !important;
        /* Hapus garis bawah */
    }

    .btn-gradient {
        background: var(--accent-gradient);
        color: white !important;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }

    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(99, 102, 241, 0.3);
        color: white !important;
    }

    .btn-outline-modern {
        background: white;
        border: 1.5px solid #e2e8f0;
        color: #64748b !important;
        position: relative;
        overflow: hidden;
        z-index: 1;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        /* Animasi smooth */
    }

    .btn-outline-modern:hover {
        background: #f8fafc;
        border-color: var(--accent);
        color: var(--accent) !important;
        transform: translateY(-3px);
        /* Efek melayang */
        box-shadow: 0 8px 15px rgba(99, 102, 241, 0.1);
        /* Shadow halus */
    }

    /* Efek Ripple/Pantulan saat diklik */
    .btn-outline-modern:active {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(99, 102, 241, 0.1);
        background: #f1f5f9;
    }

    /* Opsional: Tambahkan garis bawah kecil yang muncul saat hover */
    .btn-outline-modern::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: var(--accent-gradient);
        transition: all 0.3s ease;
        transform: translateX(-50%);
        z-index: -1;
    }

    .btn-outline-modern:hover::after {
        width: 100%;
    }

    .btn-outline-modern:hover {
        background: #f8fafc;
        border-color: var(--accent);
        color: var(--accent) !important;
    }

    /* 🔹 Table Styling */
    .table-modern thead th {
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1px;
        color: #64748b;
        border: none;
        padding: 20px;
    }

    .table-modern tbody td {
        padding: 20px;
        vertical-align: middle;
        font-weight: 600;
        color: var(--text-dark);
        border-bottom: 1px solid #f1f5f9;
    }

    /* 🔹 Badge */
    .badge-modern {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-block;

        text-align: center;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    td.text-center {
        text-align: center;
    }

    /* 🔹 Search Container */
    .search-container {
        position: relative;
        min-width: 250px;
    }

    .search-container i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-container input {
        padding-left: 45px !important;
    }

    #clearSearch {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #cbd5e1;
        transition: color 0.2s;
    }

    @media (max-width: 768px) {
        .section-header-modern {
            flex-direction: column;
            align-items: stretch;
            gap: 20px;
        }

        .search-action-wrapper {
            width: 100%;
            flex-direction: column;
        }

        .search-container {
            width: 100%;
        }

        .btn-modern {
            width: 100%;
            justify-content: center;
        }
    }

    /* clinik scopus promo */

    .bg-status-active {
        background-color: #10b981;
        color: white;
    }

    .bg-status-nonactive {
        background-color: #ef4444;
        color: white;
    }

    .bg-status {
        background-color: #5778e5;
        color: white;
    }

    .btn-create-animate {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        position: relative;
        overflow: hidden;
    }

    .btn-create-animate:hover {
        transform: scale(1.05) translateY(-3px);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4) !important;
        filter: brightness(1.1);
    }

    .btn-create-animate:active {
        transform: scale(0.95);
    }

    /* Efek kilauan (shimmer) saat hover */
    .btn-create-animate::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(120deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent);
        transition: all 0.6s;
    }

    .btn-create-animate:hover::before {
        left: 100%;
    }
</style>