-- Cadangan kolom users.nik sebelum kolomnya dihapus (28 Sep 2026).
-- Kolomnya tidak pernah disimpan dari mana pun: isian NIK di halaman
-- detail pengguna tidak punya penangan penyimpanan, dan tidak satu pun
-- tampilan menampilkannya. Baris ini disimpan kalau-kalau ada yang
-- ternyata masih dibutuhkan.
--
-- Memulihkan: tambahkan lagi kolomnya, lalu jalankan UPDATE di bawah.

UPDATE users SET nik = '545646' WHERE id = 15; -- admin
UPDATE users SET nik = '123456' WHERE id = 22; -- manager
UPDATE users SET nik = '545646' WHERE id = 24; -- staff
UPDATE users SET nik = '123456' WHERE id = 49; -- karyawan
UPDATE users SET nik = '2313221312' WHERE id = 59; -- ludiro
UPDATE users SET nik = '3471130706980001' WHERE id = 62; -- bertojuni
UPDATE users SET nik = '3404151112010001' WHERE id = 63; -- ALVI AL
UPDATE users SET nik = '3404154212970001' WHERE id = 65; -- Desi123
UPDATE users SET nik = '3404156110020001' WHERE id = 66; -- elsamanora
UPDATE users SET nik = '3310260607990001' WHERE id = 67; -- muhhafidh
UPDATE users SET nik = '3404145809880003' WHERE id = 68; -- Isti123
UPDATE users SET nik = '33150915080002' WHERE id = 69; -- wildanp
UPDATE users SET nik = '177901000065535' WHERE id = 70; -- Dinar
UPDATE users SET nik = '027401001754565' WHERE id = 71; -- Rumah Scopus
UPDATE users SET nik = '3314100906990006' WHERE id = 73; -- nofand
UPDATE users SET nik = '545646' WHERE id = 78; -- tesss
UPDATE users SET nik = '3310264109990001' WHERE id = 83; -- haniifajri
UPDATE users SET nik = '3404101704910001' WHERE id = 85; -- Ariyadi N
UPDATE users SET nik = '3471106909960001' WHERE id = 87; -- Biwi Faiza
UPDATE users SET nik = '3401061101980001' WHERE id = 97; -- wiamm
UPDATE users SET nik = '3315091908980001' WHERE id = 98; -- rizal
UPDATE users SET nik = '3310165603020002' WHERE id = 99; -- auliaaarc
UPDATE users SET nik = '3310265207980001' WHERE id = 100; -- kumalasarip
UPDATE users SET nik = '3404151303930001' WHERE id = 103; -- fsofyan77
UPDATE users SET nik = '12345689' WHERE id = 110; -- Staffadmin
UPDATE users SET nik = '12234556' WHERE id = 111; -- desiwahyu
UPDATE users SET nik = '12234556' WHERE id = 112; -- idaervi
UPDATE users SET nik = '3314104607000003' WHERE id = 130; -- Flora
