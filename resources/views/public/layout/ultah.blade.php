<!-- ================== STYLE ================== -->
<style>
    /* ===== OVERLAY ===== */
    #birthday-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 1s ease-in-out;
    }

    .birthday-card {
        background: linear-gradient(to right, #ff3131, #ff914d);
        padding: 40px;
        border-radius: 22px;
        text-align: center;
        color: #fff;
        box-shadow: 0 20px 40px rgba(0, 0, 0, .3);
        animation: popUp 1s ease;
        max-width: 420px;
        width: 90%;
    }

    .birthday-card h1 {
        font-size: 42px;
        font-weight: 800;
        margin: 0;
    }

    .birthday-card h2 {
        font-size: 26px;
        margin-top: 10px;
    }

    .birthday-card p {
        margin-top: 15px;
        font-size: 16px;
    }

    .birthday-card button {
        margin-top: 18px;
        background: #fff;
        color: #ff3131;
        border: none;
        padding: 12px 30px;
        border-radius: 30px;
        font-weight: bold;
        cursor: pointer;
        transition: .3s;
    }

    .birthday-card button:hover {
        transform: scale(1.05);
    }

    /* ===== AGE ===== */
    .age-wrapper {
        margin-top: 10px;
        display: flex;
        justify-content: center;
        gap: 10px;
        align-items: baseline;
    }

    #age-number {
        font-size: 70px;
        font-weight: 900;
        animation: glow 1.5s infinite alternate;
    }

    .age-text {
        font-size: 26px;
        font-weight: 700;
    }

    /* ===== COUNTDOWN ELEGANT ===== */
    .countdown-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 25px auto 10px;
    }

    .countdown-ring {
        transform: rotate(-90deg);
    }

    .ring-bg {
        fill: none;
        stroke: rgba(255, 255, 255, 0.25);
        stroke-width: 10;
    }

    .ring-progress {
        fill: none;
        stroke: url(#grad);
        stroke-width: 10;
        stroke-linecap: round;
        stroke-dasharray: 326;
        stroke-dashoffset: 0;
        transition: stroke-dashoffset 1s linear;
    }

    .countdown-number {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }

    .countdown-number span {
        font-size: 34px;
        line-height: 1;
    }

    .countdown-number small {
        font-size: 13px;
        opacity: .85;
    }

    /* ===== ANIMATION ===== */
    @keyframes popUp {
        from {
            transform: scale(.6);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    @keyframes glow {
        from {
            text-shadow: 0 0 10px rgba(255, 255, 255, .5);
        }

        to {
            text-shadow: 0 0 25px rgba(255, 255, 255, 1);
        }
    }

    /* ===== BALLOON ===== */
    .balloon {
        position: fixed;
        bottom: -100px;
        width: 40px;
        height: 55px;
        border-radius: 50%;
        animation: floatUp linear infinite;
        z-index: 10000;
    }

    .balloon::after {
        content: '';
        position: absolute;
        bottom: -12px;
        left: 50%;
        width: 2px;
        height: 15px;
        background: #555;
        transform: translateX(-50%);
    }

    @keyframes floatUp {
        to {
            transform: translateY(-120vh);
            opacity: .85;
        }
    }

    /* ===== CONFETTI ===== */
    .confetti {
        position: fixed;
        top: -10px;
        width: 8px;
        height: 12px;
        opacity: .85;
        animation: fall linear infinite;
        z-index: 10000;
        border-radius: 2px;
    }

    @keyframes fall {
        to {
            transform: translateY(110vh) rotate(360deg);
        }
    }
</style>

<!-- ===== SVG GRADIENT ===== -->
<svg width="0" height="0">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#ff3131" />
            <stop offset="100%" stop-color="#ff914d" />
        </linearGradient>
    </defs>
</svg>

<!-- ================== AUDIO ================== -->
<audio id="birthday-music" preload="auto">
    <source src="/assets/public/JamrudSelamatUlangTahun.mp3" type="audio/mpeg">
</audio>

<!-- ================== HTML ================== -->
<div id="birthday-overlay">
    <div class="birthday-card">
        <h1>🎂</h1>
        <h1>Happy Birthday</h1>

        <div class="age-wrapper">
            <span id="age-number">0</span>
            <span class="age-text">Tahun</span>
        </div>

        <h2>Rumah Scopus</h2>
        <p>🎉 Terus Menjadi Rumah Para Peneliti Hebat 🚀</p>

        <!-- COUNTDOWN -->
        <div class="countdown-wrapper">
            <svg class="countdown-ring" width="120" height="120">
                <circle class="ring-bg" cx="60" cy="60" r="52"></circle>
                <circle class="ring-progress" cx="60" cy="60" r="52"></circle>
            </svg>
            <div class="countdown-number">
                <span id="countdown">15</span>
                <small>detik</small>
            </div>
        </div>

        <button id="play-btn" onclick="playMusic()">▶ Play Music</button>
        <button id="close-btn" onclick="closeBirthday()" style="display:none;">Tutup</button>
    </div>
</div>

<!-- ================== SCRIPT ================== -->
<script>
    let musicPlayed = false;
    let countdownInterval;

    function playMusic() {
        if (musicPlayed) return;

        const music = document.getElementById("birthday-music");
        const countdownEl = document.getElementById("countdown");
        const circle = document.querySelector('.ring-progress');

        const total = 15;
        let timeLeft = total;
        const radius = 52;
        const circumference = 2 * Math.PI * radius;

        circle.style.strokeDasharray = circumference;

        music.volume = 0;
        music.play().then(() => {
            musicPlayed = true;
            document.getElementById('play-btn').style.display = 'none';

            // Fade in volume
            let vol = 0;
            const fade = setInterval(() => {
                vol += 0.05;
                music.volume = Math.min(vol, 0.8);
                if (vol >= 0.8) clearInterval(fade);
            }, 100);

            countdownEl.textContent = timeLeft;

            countdownInterval = setInterval(() => {
                timeLeft--;
                countdownEl.textContent = timeLeft;

                const offset = circumference - (timeLeft / total) * circumference;
                circle.style.strokeDashoffset = offset;

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('close-btn').style.display = 'inline-block';
                }
            }, 1000);
        });
    }

    function closeBirthday() {
        document.getElementById('birthday-overlay').remove();
        document.querySelectorAll('.balloon,.confetti').forEach(e => e.remove());

        const music = document.getElementById("birthday-music");
        music.pause();
        music.currentTime = 0;
    }

    function createBalloons() {
        const colors = ['#ff3131', '#ff914d', '#ffd700', '#fff'];
        for (let i = 0; i < 18; i++) {
            const b = document.createElement('div');
            b.className = 'balloon';
            b.style.left = Math.random() * 100 + 'vw';
            b.style.background = colors[Math.floor(Math.random() * colors.length)];
            b.style.animationDuration = (Math.random() * 5 + 6) + 's';
            document.body.appendChild(b);
        }
    }

    function createConfetti() {
        const colors = ['#ff3131', '#ff914d', '#ffd700', '#fff'];
        for (let i = 0; i < 60; i++) {
            const c = document.createElement('div');
            c.className = 'confetti';
            c.style.left = Math.random() * 100 + 'vw';
            c.style.background = colors[Math.floor(Math.random() * colors.length)];
            c.style.animationDuration = (Math.random() * 3 + 3) + 's';
            document.body.appendChild(c);
        }
    }

    function animateAge(target = 7) {
        let n = 0;
        const el = document.getElementById('age-number');
        const i = setInterval(() => {
            n++;
            el.textContent = n;
            if (n >= target) clearInterval(i);
        }, 200);
    }

    document.addEventListener("DOMContentLoaded", () => {
        createBalloons();
        createConfetti();
        setTimeout(() => animateAge(7), 600);
    });
</script>
<!-- ================== END ================== -->