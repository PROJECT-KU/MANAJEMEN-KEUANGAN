 <style>
   /* Footer tetap di bawah dan berada di atas konten */
   .main-footer {
     border-top: 3px solid #ff914d;
     background-color: rgba(255, 255, 255, 0.95);
     /* Transparan sedikit untuk kesan modern */
     position: fixed;
     bottom: 0;
     left: 0;
     width: 100%;
     height: 50px;
     /* Sesuaikan tinggi footer */
     display: flex;
     justify-content: space-between;
     align-items: center;
     z-index: 500;
   }

   /* Tambahkan padding bawah agar konten tidak tertutup footer */
   .main-content {
     padding-bottom: 80px;
     /* Tambah ruang supaya konten tidak tertutup footer */
     overflow-x: hidden;
   }

   /* Sidebar tetap normal saat scroll */
   .sidebar {
     position: relative;
     /* Pastikan sidebar tidak terpengaruh footer */
     z-index: 1;
   }
 </style>

 <?php
  $version = "3.0.2";
  ?>

 <footer class="main-footer" id="PwaFooter">
   <div class="footer-left">
     © <strong>Rumah Scopus Foundation</strong> <?php echo date("Y"); ?>
   </div>
   <div class="footer-right">
     Version <?php echo $version; ?>
   </div>
 </footer>