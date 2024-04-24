 <?php
  $version = "1.24.2";
  ?>

 <footer class="main-footer" style="border-top: 3px solid #6777ef;background-color: #ffffff;margin-bottom: -20px" id="WebsiteFooter">
   <div class="footer-left">
     © <strong>Rumah Scopus Foundation</strong> <?php echo date("Y"); ?>. Hak Cipta Dilindungi.
   </div>
   <div class="footer-right">
     Version {{$version }}
   </div>
 </footer>
 <footer class="main-footer" style="border-top: 3px solid #6777ef;background-color: #ffffff;margin-bottom: -20px" id="PwaFooter">
   <div class="footer-left">
     © <strong>Rumah Scopus Foundation</strong> <?php echo date("Y"); ?>
   </div>
   <div class="footer-right">
     Version {{$version }}
   </div>
 </footer>

 <!--================== CEK APAKAH PWA ATAU WEBSITE ==================-->
 <script>
   // Fungsi untuk mendeteksi apakah perangkat adalah ponsel atau browser
   function isMobileDevice() {
     return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
   }

   // Fungsi untuk menyembunyikan atau menampilkan elemen berdasarkan tipe perangkat
   function toggleElementBasedOnDevice() {
     var WebsiteFooter = document.getElementById('WebsiteFooter');
     var PwaFooter = document.getElementById('PwaFooter');

     if (isMobileDevice()) {
       // Jika aplikasi berjalan di perangkat seluler (PWA)
       WebsiteFooter.style.display = 'none';
       PwaFooter.style.display = 'block';
     } else {
       // Jika aplikasi berjalan di browser
       WebsiteFooter.style.display = 'block';
       PwaFooter.style.display = 'none';
     }
   }

   // Panggil fungsi ketika halaman dimuat
   window.addEventListener('load', toggleElementBasedOnDevice);
 </script>
 <!--================== END ==================-->