{{-- Daftar bank untuk isian rekening. Dipisah supaya halaman profil tetap
     terbaca; isinya dipindah apa adanya dari halaman itu. --}}
<option value="" disabled selected>-- PILIH NAMA BANK --</option>
<option value="002" {{ $user->bank == '002' ? 'selected' : '' }}>BRI</option>
<option value="008" {{ $user->bank == '008' ? 'selected' : '' }}>BANK MANDIRI</option>
<option value="009" {{ $user->bank == '009' ? 'selected' : '' }}>BNI</option>
<option value="200" {{ $user->bank == '200' ? 'selected' : '' }}>BANK TABUNGAN NEGARA</option>
<option value="011" {{ $user->bank == '011' ? 'selected' : '' }}>BANK DANAMON</option>
<option value="013" {{ $user->bank == '013' ? 'selected' : '' }}>BANK PERMATA</option>
<option value="014" {{ $user->bank == '014' ? 'selected' : '' }}>BCA</option>
<option value="016" {{ $user->bank == '016' ? 'selected' : '' }}>MAYBANK</option>
<option value="019" {{ $user->bank == '019' ? 'selected' : '' }}>PANINBANK</option>
<option value="022" {{ $user->bank == '022' ? 'selected' : '' }}>CIMB NIAGA</option>
<option value="023" {{ $user->bank == '023' ? 'selected' : '' }}>BANK UOB INDONESIA</option>
<option value="028" {{ $user->bank == '028' ? 'selected' : '' }}>BANK OCBC NISP</option>
<option value="087" {{ $user->bank == '087' ? 'selected' : '' }}>BANK HSBC INDONESIA</option>
<option value="147" {{ $user->bank == '147' ? 'selected' : '' }}>BANK MUAMALAT</option>
<option value="153" {{ $user->bank == '153' ? 'selected' : '' }}>BANK SINARMAS</option>
<option value="426" {{ $user->bank == '426' ? 'selected' : '' }}>BANK MEGA</option>
<option value="441" {{ $user->bank == '441' ? 'selected' : '' }}>BANK BUKOPIN</option>
<option value="451" {{ $user->bank == '451' ? 'selected' : '' }}>BSI</option>
<option value="484" {{ $user->bank == '484' ? 'selected' : '' }}>BANK KEB HANA INDONESIA</option>
<option value="494" {{ $user->bank == '494' ? 'selected' : '' }}>BANK RAYA INDONESIA</option>
<option value="506" {{ $user->bank == '506' ? 'selected' : '' }}>BANK MEGA SYARIAH</option>
<option value="046" {{ $user->bank == '046' ? 'selected' : '' }}>BANK DBS INDONESIA</option>
<option value="947" {{ $user->bank == '947' ? 'selected' : '' }}>BANK ALADIN SYARIAH</option>
<option value="950" {{ $user->bank == '950' ? 'selected' : '' }}>BANK COMMONWEALTH</option>
<option value="213" {{ $user->bank == '213' ? 'selected' : '' }}>BANK BTPN</option>
<option value="490" {{ $user->bank == '490' ? 'selected' : '' }}>BANK NEO COMMERCE</option>
<option value="501" {{ $user->bank == '501' ? 'selected' : '' }}>BANK DIGITAL BCA</option>
<option value="521" {{ $user->bank == '521' ? 'selected' : '' }}>BANK BUKOPIN SYARIAH </option>
<option value="535" {{ $user->bank == '535' ? 'selected' : '' }}>SEABANK INDONESIA</option>
<option value="542" {{ $user->bank == '542' ? 'selected' : '' }}>BANK JAGO</option>
<option value="567" {{ $user->bank == '567' ? 'selected' : '' }}>ALLO BANK</option>
<option value="110" {{ $user->bank == '110' ? 'selected' : '' }}>BPD JAWA BARAT</option>
<option value="111" {{ $user->bank == '111' ? 'selected' : '' }}>BPD DKI</option>
<option value="112" {{ $user->bank == '112' ? 'selected' : '' }}>BPD DAERAH ISTIMEWA YOGYAKARTA</option>
<option value="113" {{ $user->bank == '113' ? 'selected' : '' }}>BPD JAWA TENGAH</option>
<option value="114" {{ $user->bank == '114' ? 'selected' : '' }}>BPD JAWA TIMUR</option>
<option value="115" {{ $user->bank == '115' ? 'selected' : '' }}>BPD JAMBI</option>
<option value="116" {{ $user->bank == '116' ? 'selected' : '' }}>BANK ACEH SYARIAH</option>
<option value="117" {{ $user->bank == '117' ? 'selected' : '' }}>BPD SUMATERA UTARA</option>
<option value="118" {{ $user->bank == '118' ? 'selected' : '' }}>BANK NAGARI</option>
<option value="119" {{ $user->bank == '119' ? 'selected' : '' }}>BPD RIAU KEPRI SYARIAH</option>
<option value="120" {{ $user->bank == '120' ? 'selected' : '' }}>BPD SUMATERA SELATAN DAN BANGKA BELITUNG</option>
<option value="121" {{ $user->bank == '121' ? 'selected' : '' }}>BPD LAMPUNG</option>
<option value="122" {{ $user->bank == '122' ? 'selected' : '' }}>BPD KALIMANTAN SELATAN</option>
<option value="123" {{ $user->bank == '123' ? 'selected' : '' }}>BPD KALIMANTAN BARAT</option>
<option value="124" {{ $user->bank == '124' ? 'selected' : '' }}>BPD KALIMANTAN TIMUR DAN KALIMANTAN UTARA</option>
<option value="125" {{ $user->bank == '125' ? 'selected' : '' }}>BPD KALIMANTAN TENGAH</option>
<option value="126" {{ $user->bank == '126' ? 'selected' : '' }}>BPD SULAWESI SELATAN DAN SULAWESI BARAT</option>
<option value="127" {{ $user->bank == '127' ? 'selected' : '' }}>BPD SULAWESI UTARA DAN GORONTALO</option>
<option value="128" {{ $user->bank == '128' ? 'selected' : '' }}>BANK NTB SYARIAH</option>
<option value="129" {{ $user->bank == '129' ? 'selected' : '' }}>BPD BALI</option>
<option value="130" {{ $user->bank == '130' ? 'selected' : '' }}>BPD NUSA TENGGARA TIMUR</option>
<option value="131" {{ $user->bank == '131' ? 'selected' : '' }}>BPD MALUKU DAN MALUKU UTARA</option>
<option value="132" {{ $user->bank == '132' ? 'selected' : '' }}>BPD PAPUA</option>
<option value="133" {{ $user->bank == '133' ? 'selected' : '' }}>BPD BENGKULU</option>
<option value="134" {{ $user->bank == '134' ? 'selected' : '' }}>BPD SULAWESI TENGAH</option>
<option value="135" {{ $user->bank == '135' ? 'selected' : '' }}>BPD SULAWESI TENGGARA</option>
<option value="137" {{ $user->bank == '137' ? 'selected' : '' }}>BPD BANTEN</option>
