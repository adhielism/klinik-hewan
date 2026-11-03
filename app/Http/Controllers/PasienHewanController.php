<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class PasienHewanController extends Controller
{
    private function pdo(): PDO
    {
        return DB::connection()->getPdo();
    }

    private function validasiInput(Request $request): array
    {
        // Sanitasi
        $request->merge([
            'kode_pasien' => strip_tags(trim((string) $request->input('kode_pasien'))),
            'nama_hewan'  => strip_tags(trim((string) $request->input('nama_hewan'))),
            'nomor_rekam' => strip_tags(trim((string) $request->input('nomor_rekam'))),
        ]);

        // Validasi server-side
        return $request->validate([
            'kode_pasien'    => ['required', 'regex:/^[A-Za-z0-9]{10}$/'],
            'nama_hewan'     => ['required', 'min:2', 'max:100'],
            'nomor_rekam'    => ['required', 'min:5', 'max:30'],
            'usia_tahun'     => ['required', 'integer', 'between:0,30'],
            'jenis_hewan_id' => ['required', 'integer'],
        ], [
            'kode_pasien.required'    => 'Kode pasien wajib diisi.',
            'kode_pasien.regex'       => 'Kode pasien harus tepat 10 karakter alfanumerik.',
            'nama_hewan.required'     => 'Nama hewan wajib diisi.',
            'nama_hewan.min'          => 'Nama hewan minimal 2 karakter.',
            'nama_hewan.max'          => 'Nama hewan maksimal 100 karakter.',
            'nomor_rekam.required'    => 'Nomor rekam wajib diisi.',
            'nomor_rekam.min'         => 'Nomor rekam minimal 5 karakter.',
            'nomor_rekam.max'         => 'Nomor rekam maksimal 30 karakter.',
            'usia_tahun.required'     => 'Usia wajib diisi.',
            'usia_tahun.integer'      => 'Usia harus berupa bilangan bulat.',
            'usia_tahun.between'      => 'Usia harus antara 0 sampai 30 tahun.',
            'jenis_hewan_id.required' => 'Jenis hewan wajib dipilih.',
            'jenis_hewan_id.integer'  => 'Jenis hewan tidak valid.',
        ]);
    }

    public function index(Request $request)
    {
        $jenis = [];
        $pasien = [];

        $q = strip_tags(trim((string) $request->query('q', '')));
        $filterRaw = (string) $request->query('jenis_hewan_id', '');
        $filter = ctype_digit($filterRaw) ? (int) $filterRaw : 0;

        try {
            $pdo = $this->pdo();

            $stmt = $pdo->prepare('SELECT id, nama_jenis FROM jenis_hewan ORDER BY nama_jenis');
            $stmt->execute();
            $jenis = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = 'SELECT p.id, p.kode_pasien, p.nama_hewan, p.nomor_rekam, p.usia_tahun, j.nama_jenis
                    FROM pasien_hewan p
                    JOIN jenis_hewan j ON j.id = p.jenis_hewan_id
                    WHERE 1 = 1';
            $params = [];

            if ($q !== '') {
                $sql .= ' AND (p.kode_pasien LIKE :q1 OR p.nama_hewan LIKE :q2 OR p.nomor_rekam LIKE :q3)';
                $like = '%' . $q . '%';
                $params['q1'] = $like;
                $params['q2'] = $like;
                $params['q3'] = $like;
            }

            if ($filter > 0) {
                $sql .= ' AND p.jenis_hewan_id = :jenis';
                $params['jenis'] = $filter;
            }

            $sql .= ' ORDER BY p.id';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pasien = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            report($e);
            session()->now('error', 'Data tidak dapat dimuat saat ini. Silakan coba lagi.');
        }

        return view('pasien.index', compact('jenis', 'pasien', 'q', 'filter'));
    }

    public function store(Request $request)
    {
        $data = $this->validasiInput($request);

        try {
            $pdo = $this->pdo();
            $errors = [];

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM jenis_hewan WHERE id = :id');
            $stmt->execute(['id' => $data['jenis_hewan_id']]);
            if ((int) $stmt->fetchColumn() === 0) {
                $errors['jenis_hewan_id'] = 'Jenis hewan tidak tersedia.';
            }

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM pasien_hewan WHERE kode_pasien = :kode');
            $stmt->execute(['kode' => $data['kode_pasien']]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['kode_pasien'] = 'Kode pasien sudah digunakan.';
            }

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM pasien_hewan WHERE nomor_rekam = :rekam');
            $stmt->execute(['rekam' => $data['nomor_rekam']]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['nomor_rekam'] = 'Nomor rekam sudah digunakan.';
            }

            if ($errors) {
                return back()->withErrors($errors)->withInput();
            }

            $stmt = $pdo->prepare(
                'INSERT INTO pasien_hewan (kode_pasien, nama_hewan, nomor_rekam, usia_tahun, jenis_hewan_id)
                 VALUES (:kode, :nama, :rekam, :usia, :jenis)'
            );
            $stmt->execute([
                'kode'  => $data['kode_pasien'],
                'nama'  => $data['nama_hewan'],
                'rekam' => $data['nomor_rekam'],
                'usia'  => $data['usia_tahun'],
                'jenis' => $data['jenis_hewan_id'],
            ]);

            return redirect('/pasien-hewan')->with('success', 'Data pasien berhasil ditambahkan.');
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Data gagal disimpan. Silakan coba lagi.');
        }
    }

    public function edit(int $id)
    {
        try {
            $pdo = $this->pdo();

            $stmt = $pdo->prepare(
                'SELECT id, kode_pasien, nama_hewan, nomor_rekam, usia_tahun, jenis_hewan_id
                 FROM pasien_hewan WHERE id = :id'
            );
            $stmt->execute(['id' => $id]);
            $pasien = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pasien) {
                return redirect('/pasien-hewan')->with('error', 'Data pasien tidak ditemukan.');
            }

            $stmt = $pdo->prepare('SELECT id, nama_jenis FROM jenis_hewan ORDER BY nama_jenis');
            $stmt->execute();
            $jenis = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return view('pasien.edit', compact('pasien', 'jenis'));
        } catch (Throwable $e) {
            report($e);
            return redirect('/pasien-hewan')->with('error', 'Data tidak dapat dimuat saat ini. Silakan coba lagi.');
        }
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validasiInput($request);

        try {
            $pdo = $this->pdo();

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM pasien_hewan WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if ((int) $stmt->fetchColumn() === 0) {
                return redirect('/pasien-hewan')->with('error', 'Data pasien tidak ditemukan.');
            }

            $errors = [];

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM jenis_hewan WHERE id = :id');
            $stmt->execute(['id' => $data['jenis_hewan_id']]);
            if ((int) $stmt->fetchColumn() === 0) {
                $errors['jenis_hewan_id'] = 'Jenis hewan tidak tersedia.';
            }

            // Unik, tetapi abaikan baris yang sedang diedit
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM pasien_hewan WHERE kode_pasien = :kode AND id <> :id');
            $stmt->execute(['kode' => $data['kode_pasien'], 'id' => $id]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['kode_pasien'] = 'Kode pasien sudah digunakan pasien lain.';
            }

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM pasien_hewan WHERE nomor_rekam = :rekam AND id <> :id');
            $stmt->execute(['rekam' => $data['nomor_rekam'], 'id' => $id]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['nomor_rekam'] = 'Nomor rekam sudah digunakan pasien lain.';
            }

            if ($errors) {
                return back()->withErrors($errors)->withInput();
            }

            $stmt = $pdo->prepare(
                'UPDATE pasien_hewan
                 SET kode_pasien = :kode, nama_hewan = :nama, nomor_rekam = :rekam,
                     usia_tahun = :usia, jenis_hewan_id = :jenis
                 WHERE id = :id'
            );
            $stmt->execute([
                'kode'  => $data['kode_pasien'],
                'nama'  => $data['nama_hewan'],
                'rekam' => $data['nomor_rekam'],
                'usia'  => $data['usia_tahun'],
                'jenis' => $data['jenis_hewan_id'],
                'id'    => $id,
            ]);

            return redirect('/pasien-hewan')->with('success', 'Data pasien berhasil diperbarui.');
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Data gagal diperbarui. Silakan coba lagi.');
        }
    }

    public function destroy(int $id)
    {
        try {
            $stmt = $this->pdo()->prepare('DELETE FROM pasien_hewan WHERE id = :id');
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() === 0) {
                return redirect('/pasien-hewan')->with('error', 'Data pasien tidak ditemukan.');
            }

            return redirect('/pasien-hewan')->with('success', 'Data pasien berhasil dihapus.');
        } catch (Throwable $e) {
            report($e);
            return redirect('/pasien-hewan')->with('error', 'Data gagal dihapus. Silakan coba lagi.');
        }
    }
}