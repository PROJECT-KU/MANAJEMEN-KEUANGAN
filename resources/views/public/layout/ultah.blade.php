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
        border-radius: 20px;
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
        margin-top: 25px;
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
        margin-top: 15px;
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

    /* ===== ANIMATION ===== */
    @keyframes popUp {
        from {
            transform: scale(.5);
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
        from {
            transform: translateY(0);
            opacity: 1;
        }

        to {
            transform: translateY(-120vh);
            opacity: .8;
        }
    }

    /* ===== CONFETTI ===== */
    .confetti {
        position: fixed;
        top: -10px;
        width: 8px;
        height: 12px;
        opacity: .8;
        animation: fall linear infinite;
        z-index: 10000;
        border-radius: 2px;
    }

    @keyframes fall {
        to {
            transform: translateY(110vh) rotate(360deg);
        }
    }

    /* ===== COMING SOON ===== */
    #comingsoon-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .85);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10001;
    }

    .comingsoon-card {
        background: #fff;
        padding: 35px;
        border-radius: 22px;
        max-width: 440px;
        width: 90%;
        text-align: center;
        box-shadow: 0 30px 60px rgba(0, 0, 0, .35);
        animation: popUp .6s ease;
    }

    .comingsoon-card h2 {
        color: #ff3131;
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .comingsoon-highlight {
        background: linear-gradient(to right, #ff3131, #ff914d);
        color: #fff;
        padding: 14px;
        border-radius: 14px;
        font-weight: 600;
        margin: 15px 0;
    }

    .comingsoon-card p {
        font-size: 15px;
        color: #333;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .comingsoon-card button {
        margin-top: 10px;
        background: linear-gradient(to right, #ff3131, #ff914d);
        color: #fff;
        border: none;
        padding: 12px 28px;
        border-radius: 30px;
        font-weight: bold;
        cursor: pointer;
    }
</style>

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

        <button id="play-btn" onclick="playMusic()">▶ Play Music</button>
        <button id="close-btn" onclick="closeBirthday()" style="display:none;">Tutup</button>
    </div>
</div>

<div id="comingsoon-overlay">
    <div class="comingsoon-card">
        <h2>🚀 Coming Soon</h2>

        <div class="comingsoon-highlight">
            Klinik Scopus<br>
            <strong>Pendampingan Private via Chat</strong>
        </div>

        <p>
            Bukan kelas. Bukan webinar.<br>
            Ini <strong>pendampingan personal</strong> langsung dengan mentor
            berpengalaman publikasi jurnal Scopus.
        </p>

        <p>
            ✔ Diskusi naskah via chat<br>
            ✔ Review artikel real-time<br>
            ✔ Arahan jurnal yang realistis
        </p>

        <p><strong>🔒 Kuota terbatas. Untuk peneliti serius.</strong></p>

        <button onclick="closeComingSoon()">Saya Tertarik</button>
    </div>
</div>


<!-- ================== SCRIPT ================== -->
<script>
    let musicPlayed = false;

    function playMusic() {
        if (musicPlayed) return;
        const music = document.getElementById("birthday-music");

        music.volume = 0;
        music.play().then(() => {
            musicPlayed = true;
            document.getElementById('play-btn').style.display = 'none';

            let vol = 0;
            const fade = setInterval(() => {
                vol += 0.05;
                music.volume = Math.min(vol, 0.8);
                if (vol >= 0.8) clearInterval(fade);
            }, 100);

            setTimeout(() => {
                document.getElementById('close-btn').style.display = 'inline-block';
            }, 30000); // 1 menit
        });
    }

    function closeBirthday() {
        document.getElementById('birthday-overlay').remove();
        document.querySelectorAll('.balloon,.confetti').forEach(e => e.remove());

        const music = document.getElementById("birthday-music");
        music.pause();
        music.currentTime = 0;

        setTimeout(() => {
            document.getElementById('comingsoon-overlay').style.display = 'flex';
        }, 400);
    }

    function closeComingSoon() {
        document.getElementById('comingsoon-overlay').remove();
    }

    function createBalloons() {
        const colors = ['#ff3131','#ff914d','#ffd700','#fff'];
        for (let i=0;i<18;i++) {
            const b=document.createElement('div');
            b.className='balloon';
            b.style.left=Math.random()*100+'vw';
            b.style.background=colors[Math.floor(Math.random()*colors.length)];
            b.style.animationDuration=(Math.random()*5+6)+'s';
            document.body.appendChild(b);
        }
    }

    function createConfetti() {
        const colors=['#ff3131','#ff914d','#ffd700','#fff'];
        for (let i=0;i<60;i++) {
            const c=document.createElement('div');
            c.className='confetti';
            c.style.left=Math.random()*100+'vw';
            c.style.background=colors[Math.floor(Math.random()*colors.length)];
            c.style.animationDuration=(Math.random()*3+3)+'s';
            document.body.appendChild(c);
        }
    }

    function animateAge(target=7) {
        let n=0;
        const el=document.getElementById('age-number');
        const i=setInterval(()=>{
            n++;
            el.textContent=n;
            if(n>=target) clearInterval(i);
        },250);
    }

    document.addEventListener("DOMContentLoaded",()=>{
        createBalloons();
        createConfetti();
        setTimeout(()=>animateAge(7),800);
    });
</script>
