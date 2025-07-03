<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-6">
        <!-- Form Input -->
        <form action="<?= base_url('buy') ?>" method="post" class="row g-3">
            <input type="hidden" name="username" value="<?= session()->get('username') ?>">
            <input type="hidden" name="total_harga" id="total_harga">
            <input type="hidden" name="ppn" id="ppn">
            <input type="hidden" name="biaya_admin" id="biaya_admin">
            <input type="hidden" name="grand_total" id="grand_total">

            <div class="col-12">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" value="<?= session()->get('username') ?>" readonly>
            </div>
            <div class="col-12">
                <label for="alamat" class="form-label">Alamat</label>
                <input type="text" class="form-control" id="alamat" name="alamat" required>
            </div>
            <div class="col-12">
                <label for="kelurahan" class="form-label">Kelurahan</label>
                <select class="form-control" id="kelurahan" name="kelurahan" required></select>
            </div>
            <div class="col-12">
                <label for="layanan" class="form-label">Layanan</label>
                <select class="form-control" id="layanan" name="layanan" required></select>
            </div>
            <div class="col-12">
                <label for="ongkir" class="form-label">Ongkir</label>
                <input type="text" class="form-control" id="ongkir" name="ongkir" readonly>
            </div>
    </div>

    <div class="col-lg-6">
        <!-- Tabel Checkout -->
        <div class="col-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Sub Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['name'] ?></td>
                        <td><?= number_to_currency($item['price'], 'IDR') ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td><?= number_to_currency($item['price'] * $item['qty'], 'IDR') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2"></td>
                        <td>Subtotal</td>
                        <td><?= number_to_currency($total, 'IDR') ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>PPN (11%)</td>
                        <td><span id="ppn_display"><?= number_to_currency(0, 'IDR') ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>Biaya Admin</td>
                        <td><span id="admin_display"><?= number_to_currency(0, 'IDR') ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>Ongkir</td>
                        <td><span id="ongkir_display"><?= number_to_currency(0, 'IDR') ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><strong>Grand Total</strong></td>
                        <td><strong><span id="grand_total_display"><?= number_to_currency(0, 'IDR') ?></span></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary">Buat Pesanan</button>
        </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
$(document).ready(function() {
    var ongkir = 0;
    var subtotal = <?= $total ?>;

    hitungTotal();

    $('#kelurahan').select2({
        placeholder: 'Ketik nama kelurahan...',
        ajax: {
            url: '<?= base_url('get-location') ?>',
            dataType: 'json',
            delay: 1500,
            data: params => ({ search: params.term }),
            processResults: data => ({
                results: data.map(item => ({
                    id: item.id,
                    text: item.subdistrict_name + ", " + item.district_name + ", " + item.city_name + ", " + item.province_name + ", " + item.zip_code
                }))
            }),
            cache: true
        },
        minimumInputLength: 3
    });

    $('#kelurahan').on('change', function() {
        var id = $(this).val();
        $('#layanan').empty();
        ongkir = 0;

        $.ajax({
            url: "<?= site_url('get-cost') ?>",
            type: 'GET',
            data: { destination: id },
            dataType: 'json',
            success: function(data) {
                data.forEach(item => {
                    let text = `${item.description} (${item.service}) : estimasi ${item.etd}`;
                    $('#layanan').append($('<option>', {
                        value: item.cost,
                        text: text
                    }));
                });
                hitungTotal();
            }
        });
    });

    $('#layanan').on('change', function() {
        ongkir = parseInt($(this).val());
        hitungTotal();
    });

    function hitungTotal() {
        let ppn = subtotal * 0.11;
        let biaya_admin = 0;

        if (subtotal <= 20000000) {
            biaya_admin = subtotal * 0.006;
        } else if (subtotal <= 40000000) {
            biaya_admin = subtotal * 0.008;
        } else if (subtotal > 40000000) {
            biaya_admin = subtotal * 0.01;
        }


        let grand_total = subtotal + ppn + biaya_admin + ongkir;

        $('#total_harga').val(subtotal);
        $('#ppn').val(ppn);
        $('#biaya_admin').val(biaya_admin);
        $('#grand_total').val(grand_total);
        $('#ongkir').val(ongkir);

        $('#ppn_display').text("IDR " + formatAngka(ppn));
        $('#admin_display').text("IDR " + formatAngka(biaya_admin));
        $('#ongkir_display').text("IDR " + formatAngka(ongkir));
        $('#grand_total_display').text("IDR " + formatAngka(grand_total));
    }

    function formatAngka(angka) {
        return angka.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

});
</script>
<?= $this->endSection() ?>
