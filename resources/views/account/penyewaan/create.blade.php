@extends('layouts.account')

@section('title')
Tambah Kategori Uang Masuk - UANGKU
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>TAMBAH PEMINJAMAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-dice-d6"></i> TAMBAH PEMINJAMAN</h4>
        </div>

        <div class="card-body">

          <form action="{{ route('account.penyewaan.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>KENDARAAN</label>
                  <select class="form-control select2" name="tambah_barang_id" id="tambahBarangSelect" style="width: 100%" required>
                    <option value="">-- PILIH KENDARAAN --</option>
                    @foreach ($tambahBarang as $hasil)
                    <option value="{{ $hasil->id }}" data-price="{{ $hasil->harga_barang }}" data-stock="{{ $hasil->stok }}" data-per="{{ $hasil->perhari }}" data-jenis="{{ $hasil->jenis }}" style="text-transform:uppercase;"> {{ strtoupper($hasil->nama_barang) }}</option>
                    @endforeach
                  </select>

                  @error('category_id')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>NAMA PEMINJAM</label>
                  <input type="text" name="nama" value="{{ old('nama') }}" placeholder="Masukkan Nama Peminjam" class="form-control" style="text-transform:uppercase" required>
                  @error('nama')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>HARGA SEWA</label>
                  <input type="text" class="form-control" id="hargaBarang" disabled>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>STOK KENDARAAN</label>
                  <input type="text" class="form-control" id="stokBarang" disabled>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>HARGA PER</label>
                  <input type="text" class="form-control" id="hargaPer" disabled>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>JENIS</label>
                  <input type="text" class="form-control" id="jenisBarang" disabled>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>EMAIL PEMINJAM</label>
                  <input type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan Email" class="form-control" required>
                  @error('email')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>NO TELP</label>
                  <input type="text" name="telp" value="{{ old('telp') }}" placeholder="Masukkan Telp" class="form-control" required>
                  @error('telp')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>ALAMAT</label>
                  <textarea type="text" name="alamat" value="{{ old('alamat') }}" placeholder="Masukkan Alamat" class="form-control" required></textarea>
                  @error('alamat')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>NO IDENTITAS (KTP)</label>
                  <input type="text" name="identitas" value="{{ old('identitas') }}" placeholder="Masukkan No Identitas" class="form-control" required>

                  @error('identitas')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>JUMLAH UNIT</label>
                  <input type="text" name="jumlah" value="{{ old('jumlah') }}" placeholder="Masukkan Jumlah Unit Peminjaman" class="form-control" required>
                  @error('jumlah')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Lama Peminjaman (Hari)</label>
                  <input type="text" name="lama_peminjaman" value="{{ old('lama_peminjaman') }}" placeholder="Masukkan Lama Peminjaman" class="form-control" required>
                  @error('kama_peminjaman')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>TANGGAL PEMINJAMAN</label>
                  <input type="date" name="tanggal" value="{{ old('tanggal') }}" class="form-control" required>

                  @error('tanggal')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="col-md-6" hidden>
              <div class="form-group">
                <label>SUBTOTAL</label>
                <input type="text" name="subtotal" value="{{ old('subtotal') }}" class="form-control" readonly></input>
                @error('subtotal')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>


            <div class="col-md-12">
              <div class="form-group">
                <label>DISKON</label>
                <input type="text" name="diskon" value="{{ old('diskon') }}" class="form-control currency">
                @error('diskon')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-12" hidden>
              <div class="form-group">
                <label>TOTAL</label>
                <input type="text" name="total" value="{{ old('total') }}" class="form-control">
                @error('total')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>


            <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
            <button class="btn btn-warning btn-reset" type="reset"><i class="fa fa-redo"></i> RESET</button>

          </form>

        </div>
      </div>
    </div>
  </section>
</div>
<script>
  $(document).ready(function() {
    // Function to format the number to rupiah
    function formatToRupiah(number) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
      }).format(number);
    }

    // Function to update the price, stock, per, and jenis based on selected kendaraan
    function updateFields() {
      var selectedKendaraanId = $('#tambahBarangSelect').val();
      var selectedKendaraanOption = $('#tambahBarangSelect option:selected');

      if (selectedKendaraanId) {
        var price = selectedKendaraanOption.data('price');
        var stock = selectedKendaraanOption.data('stock');
        var per = selectedKendaraanOption.data('per');
        var jenis = selectedKendaraanOption.data('jenis');

        $('#hargaBarang').val(formatToRupiah(price));
        $('#stokBarang').val(stock);

        // Change 'per' to display 12 JAM or 24 JAM
        if (per === 'setengah') {
          $('#hargaPer').val('12 JAM');
        } else if (per === 'sehari') {
          $('#hargaPer').val('24 JAM');
        } else {
          $('#hargaPer').val('');
        }

        $('#jenisBarang').val(jenis);
      } else {
        $('#hargaBarang').val('');
        $('#stokBarang').val('');
        $('#hargaPer').val('');
        $('#jenisBarang').val('');
      }
    }

    // Call the function when the page loads to initialize the values
    updateFields();

    // Call the function whenever the user selects a kendaraan
    $('#tambahBarangSelect').on('change', function() {
      updateFields();
    });
  });
</script>



<script>
  var cleaveC = new Cleave('.currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var timeoutHandler = null;
</script>
<script>
  /**
   * Calculate subtotal
   */
  function calculateSubtotal() {
    const tambahBarangSelect = document.getElementById('tambah_barang_id');
    const selectedOption = tambahBarangSelect.options[tambahBarangSelect.selectedIndex];
    const hargaBarang = parseFloat(selectedOption.getAttribute('data-harga'));
    const jumlah = parseFloat(document.getElementById('jumlah').value);
    const subtotal = hargaBarang * jumlah;
    document.getElementById('subtotal').value = isNaN(subtotal) ? '' : subtotal.toFixed(2);
    calculateTotal();
  }

  /**
   * Calculate total
   */
  function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('subtotal').value);
    const diskon = parseFloat(document.getElementById('diskon').value);
    const total = subtotal - diskon;
    document.getElementById('total').value = isNaN(total) ? '' : total.toFixed(2);
  }

  // Attach event listeners
  document.getElementById('tambah_barang_id').addEventListener('change', calculateSubtotal);
  document.getElementById('jumlah').addEventListener('input', calculateSubtotal);
  document.getElementById('diskon').addEventListener('input', calculateTotal);
</script>
<script>
  /**
   * btn submit loader
   */
  $(".btn-submit").click(function() {
    $(".btn-submit").addClass('btn-progress');
    if (timeoutHandler) clearTimeout(timeoutHandler);

    timeoutHandler = setTimeout(function() {
      $(".btn-submit").removeClass('btn-progress');

    }, 1000);
  });

  /**
   * btn reset loader
   */
  $(".btn-reset").click(function() {
    $(".btn-reset").addClass('btn-progress');
    if (timeoutHandler) clearTimeout(timeoutHandler);

    timeoutHandler = setTimeout(function() {
      $(".btn-reset").removeClass('btn-progress');

    }, 500);
  })
</script>

@stop