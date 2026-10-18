<?php
return [

   /*
   |--------------------------------------------------------------------------
   | Bahasa
   |--------------------------------------------------------------------------
   |
   | Bahasa yang dipakai mengeja angka jadi kata.
   |
   | WAJIB disebut di sini. Terbilang membacanya begini:
   |
   |     config('terbilang.locale') ?: config('app.locale')
   |
   | dan berkas ini dulu tidak menyebutnya sama sekali — jadi yang terbaca
   | nilai bawaan dari config paketnya sendiri, yaitu 'en'. Ia MENANG atas
   | app.locale yang sudah 'id', sebab ?: hanya mundur saat nilainya kosong,
   | dan 'en' bukan nilai kosong.
   |
   | Akibatnya terukur pada dokumen yang keluar ke pelanggan: faktur Scopus
   | Camp, faktur Analisis Bibliometrik, slip gaji, dan slip camp semuanya
   | mencetak "two hundred thirty-two thousand nine hundred thirty-one
   | rupiah" di bawah kata "Terbilang:".
   |
   | ICU di mesin ini memang bisa bahasa Indonesia — diperiksa langsung,
   | NumberFormatter('id', SPELLOUT) memulangkan "dua ratus tiga puluh dua
   | ribu sembilan ratus tiga puluh satu". Yang salah cuma bahasanya yang
   | tidak pernah diminta.
   */
   'locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Date Output Format
    |--------------------------------------------------------------------------
    |
    | This value is output format for function Terbilang::date()
    | default date format is '{DAY} {MONTH} {YEAR}'
    | default time format is '{HOUR} {SEPARATOR} {MINUTE} {MINUTE_LABEL} {SECOND} {SECOND_LABEL}'
    | you can mondify for example 'day {DAY} month {MONTH} year {YEAR}'
    */
   'output' => [
       'date' => '{DAY} {MONTH} {YEAR}',
       'time' => '{HOUR} {SEPARATOR} {MINUTE} {MINUTE_LABEL} {SECOND} {SECOND_LABEL}'
   ],

   /*
   |--------------------------------------------------------------------------
   | Short Output
   |--------------------------------------------------------------------------
   |
   | This value is short config
   | default value is million
   | available value 'kilo, million, billion, trillion'
   */
   'short' => 'million',


   /*
   |--------------------------------------------------------------------------
   | Period Output Format
   |--------------------------------------------------------------------------
   |
   | This value is output format for function Terbilang::period()
   | default format FULL, available format FULL, YEAR, MONTH, DAY, HOUR, MINUTE, SECOND
   */
   'period' => [
       'type' => 'DAY',
       'format' => '{YEAR} {MONTH} {DAY} {HOUR} {MINUTE} {SECOND}',
       'hide_zero_value' => true,
       'separator' => ' ',
       'terbilang' => false,
       'show' => [
           'year' => true,
           'month' => true,
           'day' => true,
           'hour' => true,
           'minute' => true,
           'second' => true,
       ]
   ],
];
