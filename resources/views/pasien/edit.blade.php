<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pasien Hewan</title>
</head>
<body>
    <h1>Edit Pasien Hewan</h1>

    @if (session('error'))
        <p><strong>{{ session('error') }}</strong></p>
    @endif
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/pasien-hewan/{{ $pasien['id'] }}">
        @csrf
        @method('PUT')
        <p>
            <label>Kode Pasien<br>
            <input type="text" name="kode_pasien" value="{{ old('kode_pasien', $pasien['kode_pasien']) }}"></label>
        </p>
        <p>
            <label>Nama Hewan<br>
            <input type="text" name="nama_hewan" value="{{ old('nama_hewan', $pasien['nama_hewan']) }}"></label>
        </p>
        <p>
            <label>Nomor Rekam<br>
            <input type="text" name="nomor_rekam" value="{{ old('nomor_rekam', $pasien['nomor_rekam']) }}"></label>
        </p>
        <p>
            <label>Usia (tahun)<br>
            <input type="number" name="usia_tahun" value="{{ old('usia_tahun', $pasien['usia_tahun']) }}"></label>
        </p>
        <p>
            <label>Jenis Hewan<br>
            <select name="jenis_hewan_id">
                <option value="">-- Pilih --</option>
                @foreach ($jenis as $j)
                    <option value="{{ $j['id'] }}"
                        {{ old('jenis_hewan_id', $pasien['jenis_hewan_id']) == $j['id'] ? 'selected' : '' }}>
                        {{ $j['nama_jenis'] }}
                    </option>
                @endforeach
            </select></label>
        </p>
        <button type="submit">Simpan Perubahan</button>
        <a href="/pasien-hewan">Batal</a>
    </form>
</body>
</html>