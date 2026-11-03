<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pasien Klinik Hewan</title>
</head>
<body>
    <h1>Pasien Klinik Hewan</h1>

    @if (session('success'))
        <p><strong>{{ session('success') }}</strong></p>
    @endif
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

    <h2>Tambah Pasien</h2>
    <form method="POST" action="/pasien-hewan">
        @csrf
        <p>
            <label>Kode Pasien<br>
            <input type="text" name="kode_pasien" value="{{ old('kode_pasien') }}"></label>
        </p>
        <p>
            <label>Nama Hewan<br>
            <input type="text" name="nama_hewan" value="{{ old('nama_hewan') }}"></label>
        </p>
        <p>
            <label>Nomor Rekam<br>
            <input type="text" name="nomor_rekam" value="{{ old('nomor_rekam') }}"></label>
        </p>
        <p>
            <label>Usia (tahun)<br>
            <input type="number" name="usia_tahun" value="{{ old('usia_tahun') }}"></label>
        </p>
        <p>
            <label>Jenis Hewan<br>
            <select name="jenis_hewan_id">
                <option value="">-- Pilih --</option>
                @foreach ($jenis as $j)
                    <option value="{{ $j['id'] }}" {{ old('jenis_hewan_id') == $j['id'] ? 'selected' : '' }}>
                        {{ $j['nama_jenis'] }}
                    </option>
                @endforeach
            </select></label>
        </p>
        <button type="submit">Simpan</button>
    </form>

    <h2>Cari dan Filter</h2>
    <form method="GET" action="/pasien-hewan">
        <label>Kata kunci
            <input type="text" name="q" value="{{ $q }}" placeholder="kode, nama, atau no. rekam">
        </label>
        <label>Jenis
            <select name="jenis_hewan_id">
                <option value="">Semua</option>
                @foreach ($jenis as $j)
                    <option value="{{ $j['id'] }}" {{ $filter == $j['id'] ? 'selected' : '' }}>
                        {{ $j['nama_jenis'] }}
                    </option>
                @endforeach
            </select>
        </label>
        <button type="submit">Cari</button>
        <a href="/pasien-hewan">Reset</a>
    </form>

    <h2>Daftar Pasien</h2>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Hewan</th>
                <th>No. Rekam</th>
                <th>Usia</th>
                <th>Status</th>
                <th>Jenis</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pasien as $i => $p)
                @php $status = statusUsiaHewan((int) $p['usia_tahun']); @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p['kode_pasien'] }}</td>
                    <td>{{ $p['nama_hewan'] }}</td>
                    <td>{{ $p['nomor_rekam'] }}</td>
                    <td>{{ $p['usia_tahun'] }}</td>
                    <td>
                        @if ($status === 'Dewasa')
                            <strong>Dewasa</strong>
                        @else
                            Muda
                        @endif
                    </td>
                    <td>{{ $p['nama_jenis'] }}</td>
                    <td>
                        <a href="/pasien-hewan/{{ $p['id'] }}/edit">Edit</a>
                        <form method="POST" action="/pasien-hewan/{{ $p['id'] }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">Data tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>