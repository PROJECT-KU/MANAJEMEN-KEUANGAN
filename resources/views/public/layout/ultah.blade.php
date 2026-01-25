<style>
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
    }

    .birthday-card h1 {
        font-size: 50px;
        font-weight: bold;
    }

    .birthday-card h2 {
        font-size: 32px;
        margin-top: 10px;
    }

    .birthday-card p {
        margin-top: 15px;
        font-size: 18px;
    }

    .birthday-card button {
        margin-top: 25px;
        background: #fff;
        color: #ff3131;
        border: none;
        padding: 10px 25px;
        border-radius: 30px;
        font-weight: bold;
        cursor: pointer;
    }

    @keyframes popUp {
        0% {
            transform: scale(0.5);
            opacity: 0;
        }

        100% {
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

    /* 🎊 Confetti */
    /* 🎈 Balloon */
    .balloon {
        position: fixed;
        bottom: -100px;
        width: 40px;
        height: 55px;
        border-radius: 50% 50% 50% 50%;
        animation: floatUp linear infinite;
        z-index: 10000;
    }

    /* 🎊 Confetti */
    .confetti {
        position: fixed;
        top: -10px;
        width: 8px;
        height: 12px;
        background-color: red;
        opacity: 0.8;
        animation: fall linear infinite;
        z-index: 10000;
        border-radius: 2px;
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

    /* Animasi balon naik */
    @keyframes floatUp {
        from {
            transform: translateY(0) rotate(0deg);
            opacity: 1;
        }

        to {
            transform: translateY(-120vh) rotate(10deg);
            opacity: 0.8;
        }
    }


    @keyframes fall {
        to {
            transform: translateY(110vh) rotate(360deg);
        }
    }
</style>



<!-- 🎉 Birthday Animation -->
<div id="birthday-overlay">
    <div class="birthday-card">
        <h1>🎂 Happy Birthday</h1>
        <h2>Rumah Scopus 🎉</h2>
        <p>Terus Menjadi Rumah Para Peneliti Hebat 🚀</p>
        <button onclick="closeBirthday()">Tutup</button>
    </div>
</div>


<script>
    function closeBirthday() {
        document.getElementById('birthday-overlay')?.remove();
        document.querySelectorAll('.balloon').forEach(e => e.remove());
        document.querySelectorAll('.confetti').forEach(e => e.remove());
    }

    /* 🎈 BALON */
    function createBalloons() {
        const colors = ['#ff3131', '#ff914d', '#ffd700', '#ffffff', '#ff69b4'];

        for (let i = 0; i < 20; i++) {
            const balloon = document.createElement('div');
            balloon.className = 'balloon';

            balloon.style.left = Math.random() * 100 + 'vw';
            balloon.style.background = colors[Math.floor(Math.random() * colors.length)];
            balloon.style.animationDuration = (Math.random() * 5 + 6) + 's';
            balloon.style.opacity = Math.random() * 0.5 + 0.5;

            document.body.appendChild(balloon);
        }
    }

    /* 🎊 CONFETTI */
    function createConfetti() {
        const colors = ['#ff3131', '#ff914d', '#ffd700', '#ffffff'];

        for (let i = 0; i < 60; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';

            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDuration = (Math.random() * 3 + 3) + 's';
            confetti.style.transform = `rotate(${Math.random() * 360}deg)`;

            document.body.appendChild(confetti);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        createBalloons();
        createConfetti();
    });
</script>