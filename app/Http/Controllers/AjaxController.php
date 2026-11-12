<?php

namespace App\Http\Controllers;

use App\Models\AdministratorModel;
use App\Models\AuthModel;
use App\Models\HomeModel;
use App\Models\OperasionalModel;
use App\Services\AbsenPdfGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AjaxController extends Controller
{
    private const APPROVAL_SORTABLE_COLUMNS = [
        'tb_employee.name',
        'tb_employee.created_at',
        'tb_employee.status',
    ];

    private const EMPLOYEE_SORTABLE_COLUMNS = [
        'tb_employee.name',
        'tb_employee.email',
        'tb_employee.number_phone',
        'tb_role.name',
        'tb_employee.status',
    ];

    private const ROLE_SORTABLE_COLUMNS = [
        'tb_role.name',
        'creator.name',
        'tb_role.updated_at',
    ];

    private const POSITION_SORTABLE_COLUMNS = [
        'tb_position.name',
        'creator.name',
        'tb_position.updated_at',
    ];

    private const ALAT_KERJA_SORTABLE_COLUMNS = [
        'holder.name',
        'tb_alat_kerja.kode_alat',
        'tb_alat_kerja.nama_alat',
        'tb_alat_kerja.tanggal_penyerahan',
        'tb_alat_kerja.kondisi',
        'tb_alat_kerja.status',
    ];

    private const STO_SORTABLE_COLUMNS = [
        'kode_sto',
        'nama_sto',
        'kecamatan',
        'alamat',
        'latitude',
        'status',
    ];

    private const APPROVAL_FILTER_RULES = [
        'pendaftar'       => 'nullable|string|max:100',
        'registered_from' => 'nullable|date',
        'registered_to'   => 'nullable|date|after_or_equal:filters.registered_from',
        'status'          => 'nullable|in:2,3',
    ];

    private const EMPLOYEE_FILTER_RULES = [
        'karyawan'     => 'nullable|string|max:100',
        'email'        => 'nullable|string|max:50',
        'number_phone' => 'nullable|string|max:15',
        'role_id'      => 'nullable|integer|min:1',
        'status'       => 'nullable|in:0,1,2,3',
    ];

    private const MASTER_DATA_FILTER_RULES = [
        'name'         => 'nullable|string|max:100',
        'created_by'   => 'nullable|string|max:100',
        'updated_from' => 'nullable|date',
        'updated_to'   => 'nullable|date|after_or_equal:filters.updated_from',
    ];

    private const ALAT_KERJA_FILTER_RULES = [
        'karyawan'     => 'nullable|string|max:100',
        'kode_alat'    => 'nullable|string|max:50',
        'nama_alat'    => 'nullable|string|max:100',
        'kondisi'      => 'nullable|in:Baik,Rusak',
        'status'       => 'nullable|in:Sudah Dikembalikan,Belum Dikembalikan',
        'tanggal_from' => 'nullable|date',
        'tanggal_to'   => 'nullable|date|after_or_equal:filters.tanggal_from',
    ];

    private const STO_FILTER_RULES = [
        'kode_sto'  => 'nullable|string|max:20',
        'nama_sto'  => 'nullable|string|max:100',
        'kecamatan' => 'nullable|string|max:100',
        'alamat'    => 'nullable|string|max:255',
        'koordinat' => 'nullable|string|max:30',
        'status'    => 'nullable|in:Aktif,Nonaktif',
    ];

    private const MAX_STO_MARKERS = 500;

    private const SELECT_OPTIONS_PER_PAGE = 20;

    public function dashboard_summary()
    {
        return response()->json(HomeModel::dashboardSummary(Auth::user()->isHcm()));
    }

    public function approval_list(Request $request)
    {
        $table   = $this->readDataTableRequest($request, self::APPROVAL_SORTABLE_COLUMNS);
        $filters = $this->readFilters($request, self::APPROVAL_FILTER_RULES);
        $result  = AdministratorModel::approvalList($table, $filters);

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function approval_approve(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_employee,id',
        ]);

        $approvedRows = AdministratorModel::changeRegistrationStatus(
            $request->id,
            [AuthModel::STATUS_PENDING, AuthModel::STATUS_REJECTED],
            AuthModel::STATUS_ACTIVE,
            Auth::id(),
        );

        if ($approvedRows === 0) {
            return response()->json([
                'message' => 'Akun ini tidak sedang menunggu persetujuan.',
            ], 422);
        }

        return response()->json([
            'message' => 'Akun berhasil disetujui dan sekarang bisa login.',
        ]);
    }

    public function approval_reject(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_employee,id',
        ]);

        $rejectedRows = AdministratorModel::changeRegistrationStatus(
            $request->id,
            [AuthModel::STATUS_PENDING],
            AuthModel::STATUS_REJECTED,
            Auth::id(),
        );

        if ($rejectedRows === 0) {
            return response()->json([
                'message' => 'Akun ini tidak sedang menunggu persetujuan.',
            ], 422);
        }

        return response()->json([
            'message' => 'Pendaftaran akun berhasil ditolak.',
        ]);
    }

    public function employee_list(Request $request)
    {
        $table   = $this->readDataTableRequest($request, self::EMPLOYEE_SORTABLE_COLUMNS);
        $filters = $this->readFilters($request, self::EMPLOYEE_FILTER_RULES);
        $result  = AdministratorModel::employeeList($table, $filters);

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function employee_save(Request $request)
    {
        $employee = $request->validate([
            'id'           => 'nullable|integer|exists:tb_employee,id',
            'nik'          => ['required', 'integer', 'min:1', 'max:2147483647', Rule::unique('tb_employee', 'nik')->ignore($request->id)],
            'name'         => 'required|string|max:100',
            'no_ktp'       => 'nullable|string|digits:16',
            'number_phone' => 'nullable|string|max:15|regex:/^[0-9+]+$/',
            'email'        => 'nullable|email|max:50',
            'address'      => 'nullable|string|max:1000',
            'role_id'      => 'required|integer|exists:tb_role,id',
            'status'       => 'required|in:0,1',
            'password'     => 'required_without:id|nullable|string|min:8|max:255',
        ], [
            'nik.required'              => 'NIK wajib diisi!',
            'nik.integer'               => 'NIK harus berupa angka!',
            'nik.unique'                => 'NIK sudah terdaftar!',
            'name.required'             => 'Nama wajib diisi!',
            'no_ktp.digits'             => 'No. KTP harus terdiri dari 16 digit!',
            'number_phone.regex'        => 'No. HP hanya boleh angka dan tanda +.',
            'email.email'               => 'Format email tidak valid!',
            'role_id.required'          => 'Role wajib dipilih!',
            'status.required'           => 'Status wajib dipilih!',
            'password.required_without' => 'Password wajib diisi untuk karyawan baru!',
            'password.min'              => 'Password minimal 8 karakter!',
        ]);

        AdministratorModel::saveEmployee($employee, Auth::id());

        return response()->json([
            'message' => 'Data karyawan berhasil disimpan.',
        ]);
    }

    public function employee_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_employee,id',
        ]);

        if ((int) $request->id === (int) Auth::id()) {
            return response()->json([
                'message' => 'Akun yang sedang Anda pakai tidak bisa dihapus.',
            ], 422);
        }

        AdministratorModel::deleteEmployee($request->id);

        return response()->json([
            'message' => 'Data karyawan berhasil dihapus.',
        ]);
    }

    public function role_list(Request $request)
    {
        $table   = $this->readDataTableRequest($request, self::ROLE_SORTABLE_COLUMNS);
        $filters = $this->readFilters($request, self::MASTER_DATA_FILTER_RULES);
        $result  = AdministratorModel::roleList($table, $filters);

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function role_options(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'page'   => 'nullable|integer|min:1',
        ]);

        $search = trim((string) $request->input('search', ''));
        $page   = (int) $request->input('page', 1);
        $roles  = AdministratorModel::roleOptions($search, $page, self::SELECT_OPTIONS_PER_PAGE);

        return response()->json([
            'results'    => $roles['rows'],
            'pagination' => ['more' => $roles['hasMore']],
        ]);
    }

    public function role_save(Request $request)
    {
        $role = $request->validate([
            'id'   => 'nullable|integer|exists:tb_role,id',
            'name' => ['required', 'string', 'max:100', Rule::unique('tb_role', 'name')->ignore($request->id)],
        ], [
            'name.required' => 'Nama role wajib diisi!',
            'name.unique'   => 'Nama role sudah ada!',
        ]);

        AdministratorModel::saveRole($role, Auth::id());

        return response()->json([
            'message' => 'Data role berhasil disimpan.',
        ]);
    }

    public function role_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_role,id',
        ]);

        if (AdministratorModel::isRoleUsed($request->id)) {
            return response()->json([
                'message' => 'Role masih dipakai karyawan, tidak bisa dihapus.',
            ], 422);
        }

        AdministratorModel::deleteRole($request->id);

        return response()->json([
            'message' => 'Data role berhasil dihapus.',
        ]);
    }

    public function position_list(Request $request)
    {
        $table   = $this->readDataTableRequest($request, self::POSITION_SORTABLE_COLUMNS);
        $filters = $this->readFilters($request, self::MASTER_DATA_FILTER_RULES);
        $result  = AdministratorModel::positionList($table, $filters);

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function position_save(Request $request)
    {
        $position = $request->validate([
            'id'   => 'nullable|integer|exists:tb_position,id',
            'name' => ['required', 'string', 'max:100', Rule::unique('tb_position', 'name')->ignore($request->id)],
        ], [
            'name.required' => 'Nama position wajib diisi!',
            'name.unique'   => 'Nama position sudah ada!',
        ]);

        AdministratorModel::savePosition($position, Auth::id());

        return response()->json([
            'message' => 'Data position berhasil disimpan.',
        ]);
    }

    public function position_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_position,id',
        ]);

        AdministratorModel::deletePosition($request->id);

        return response()->json([
            'message' => 'Data position berhasil dihapus.',
        ]);
    }

        public function absen_list(Request $request)
    {
        $request->validate([
            'draw'         => 'required|integer|min:0',
            'start'        => 'required|integer|min:0',
            'length'       => 'required|integer|min:1|max:100',
            'search.value' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $isHcm = $user->isHcm();

        $query = DB::table('tb_absen')
            ->join('tb_employee', 'tb_absen.nik_karyawan', '=', 'tb_employee.nik')
            ->select(
                'tb_absen.id',
                'tb_absen.nik_karyawan',
                'tb_employee.name as nama_karyawan',
                'tb_absen.jenis',
                'tb_absen.tanggal_mulai',
                'tb_absen.tanggal_selesai',
                'tb_absen.nomor_dokumen',
                'tb_absen.status',
                'tb_absen.keterangan',
                'tb_absen.keterangan_admin',
                'tb_absen.pdf_path',
                'tb_absen.surat_dokter_path as surat_dokter_path_utama',
                'tb_absen.created_at'
            );

        if (!$isHcm) {
            $query->where('tb_absen.nik_karyawan', $user->nik);
        }

        $total = (clone $query)->count();

        $keyword = trim((string) $request->input('search.value', ''));

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('tb_employee.name', 'like', "%{$keyword}%")
                    ->orWhere('tb_absen.nik_karyawan', 'like', "%{$keyword}%")
                    ->orWhere('tb_absen.nomor_dokumen', 'like', "%{$keyword}%")
                    ->orWhere('tb_absen.jenis', 'like', "%{$keyword}%")
                    ->orWhere('tb_absen.status', 'like', "%{$keyword}%");
            });
        }

        $filtered = (clone $query)->count();

        $rows = $query
            ->orderByDesc('tb_absen.id')
            ->offset((int) $request->start)
            ->limit((int) $request->length)
            ->get()
            ->map(function ($row) {
                $row->karyawan = $row->nama_karyawan
                    . ' (' . $row->nik_karyawan . ')';

                // DATA DETAIL CUTI
                $cuti = null;

                if ($row->jenis === 'Cuti') {
                    $cuti = DB::table('tb_absen_cuti')
                        ->where('absen_id', $row->id)
                        ->first();
                }

                $row->jenis_cuti = $cuti->jenis_cuti ?? null;
                $row->lama_cuti = $cuti->lama_cuti ?? null;
                $row->band = $cuti->band ?? null;
                $row->jabatan = $cuti->jabatan ?? null;
                $row->unit_kerja = $cuti->unit_kerja ?? null;
                $row->cuti_tanggal_mulai = $cuti->tanggal_mulai ?? null;
                $row->cuti_tanggal_selesai = $cuti->tanggal_selesai ?? null;
                $row->alamat_selama_cuti = $cuti->alamat_selama_cuti ?? null;
                $row->telepon_cuti = $cuti->telepon_cuti ?? null;
                $row->perjalanan_berangkat_mulai = $cuti->perjalanan_berangkat_mulai ?? null;
                $row->perjalanan_berangkat_selesai = $cuti->perjalanan_berangkat_selesai ?? null;
                $row->perjalanan_pulang_mulai = $cuti->perjalanan_pulang_mulai ?? null;
                $row->perjalanan_pulang_selesai = $cuti->perjalanan_pulang_selesai ?? null;
                $row->alasan_cuti = $cuti->alasan_cuti ?? null;

                // DATA DETAIL SPPD
                $sppd = null;

                if ($row->jenis === 'SPPD') {
                    $sppd = DB::table('tb_absen_sppd')
                        ->where('absen_id', $row->id)
                        ->first();
                }

                $row->tanggal_surat = $sppd->tanggal_surat ?? null;
                $row->perihal_pekerjaan = $sppd->perihal_pekerjaan ?? null;
                $row->lokasi_pekerjaan = $sppd->lokasi_pekerjaan ?? null;
                $row->tanggal_mulai_penugasan = $sppd->tanggal_mulai_penugasan ?? null;
                $row->tanggal_selesai_penugasan = $sppd->tanggal_selesai_penugasan ?? null;

                $row->sppd_karyawan = [];

                if ($row->jenis === 'SPPD') {
                    $row->sppd_karyawan = DB::table('tb_absen_sppd_karyawan')
                        ->where('absen_id', $row->id)
                        ->orderBy('id')
                        ->get([
                            'nik',
                            'nama',
                            'position',
                            'kota_asal',
                            'kota_tujuan',
                        ]);
                }

                // DATA DETAIL SAKIT
                $sakit = null;

                if ($row->jenis === 'Sakit') {
                    $sakit = DB::table('tb_absen_sakit')
                        ->where('absen_id', $row->id)
                        ->first();
                }

                $row->surat_dokter_path = $sakit->surat_dokter_path
                    ?? $row->surat_dokter_path_utama;

                unset($row->surat_dokter_path_utama);

                return $row;
            });

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ]);
    }

    public function absen_nomor_dokumen()
    {
        $datePart = now()->format('Ymd');

        $sequence = (int) DB::table('tb_absen')
            ->where('nomor_dokumen', 'like', "ABS-{$datePart}-%")
            ->count() + 1;

        do {
            $nomorDokumen = 'ABS-' . $datePart . '-'
                . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

            $exists = DB::table('tb_absen')
                ->where('nomor_dokumen', $nomorDokumen)
                ->exists();

            $sequence++;
        } while ($exists);

        return response()->json([
            'nomor_dokumen' => $nomorDokumen,
        ]);
    }

        public function absen_save(Request $request)
    {
        abort_unless(!Auth::user()->isHcm(), 403);

        $data = $request->validate([
            'id' => 'nullable|integer|exists:tb_absen,id',

            'jenis' => 'required|in:SPPD,Sakit,Cuti',

            // FORM CUTI
            'jenis_cuti' => 'required_if:jenis,Cuti|nullable|in:Tahunan,Besar,CAP,Sakit,Bersalin,Haid',
            'band' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:150',
            'unit_kerja' => 'nullable|string|max:150',
            'cuti_tanggal_mulai' => 'required_if:jenis,Cuti|nullable|date',
            'cuti_tanggal_selesai' => 'required_if:jenis,Cuti|nullable|date|after_or_equal:cuti_tanggal_mulai',
            'alamat_selama_cuti' => 'required_if:jenis,Cuti|nullable|string|max:5000',
            'telepon_cuti' => 'required_if:jenis,Cuti|nullable|string|max:20',
            'perjalanan_berangkat_mulai' => 'nullable|date',
            'perjalanan_berangkat_selesai' => 'nullable|date|after_or_equal:perjalanan_berangkat_mulai',
            'perjalanan_pulang_mulai' => 'nullable|date',
            'perjalanan_pulang_selesai' => 'nullable|date|after_or_equal:perjalanan_pulang_mulai',
            'alasan_cuti' => 'required_if:jenis,Cuti|nullable|string|max:5000',

            // FORM SPPD
            'tanggal_surat' => 'required_if:jenis,SPPD|nullable|date',
            'perihal_pekerjaan' => 'required_if:jenis,SPPD|nullable|string|max:255',
            'lokasi_pekerjaan' => 'required_if:jenis,SPPD|nullable|string|max:255',
            'sppd_tanggal_mulai' => 'required_if:jenis,SPPD|nullable|date',
            'sppd_tanggal_selesai' => 'required_if:jenis,SPPD|nullable|date|after_or_equal:sppd_tanggal_mulai',
            'sppd_karyawan' => 'nullable|array|max:3',
            'sppd_karyawan.*.nik' => 'nullable|string|max:30',
            'sppd_karyawan.*.nama' => 'nullable|string|max:150',
            'sppd_karyawan.*.position' => 'nullable|string|max:150',
            'sppd_karyawan.*.kota_asal' => 'nullable|string|max:150',
            'sppd_karyawan.*.kota_tujuan' => 'nullable|string|max:150',

            // FORM SAKIT
            'tanggal_mulai' => 'required_if:jenis,Sakit|nullable|date',
            'tanggal_selesai' => 'required_if:jenis,Sakit|nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required_if:jenis,Sakit|nullable|string|max:5000',
            'surat_dokter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'jenis.required' => 'Jenis absen wajib dipilih!',
            'jenis.in' => 'Jenis absen tidak valid!',

            'jenis_cuti.required_if' => 'Jenis cuti wajib dipilih!',
            'cuti_tanggal_mulai.required_if' => 'Tanggal mulai cuti wajib diisi!',
            'cuti_tanggal_selesai.required_if' => 'Tanggal selesai cuti wajib diisi!',
            'cuti_tanggal_selesai.after_or_equal' => 'Tanggal selesai cuti tidak boleh sebelum tanggal mulai!',
            'alamat_selama_cuti.required_if' => 'Alamat selama cuti wajib diisi!',
            'telepon_cuti.required_if' => 'Nomor telepon selama cuti wajib diisi!',
            'alasan_cuti.required_if' => 'Alasan cuti wajib diisi!',

            'tanggal_surat.required_if' => 'Tanggal surat wajib diisi!',
            'perihal_pekerjaan.required_if' => 'Perihal pekerjaan wajib diisi!',
            'lokasi_pekerjaan.required_if' => 'Lokasi pekerjaan wajib diisi!',
            'sppd_tanggal_mulai.required_if' => 'Tanggal mulai penugasan wajib diisi!',
            'sppd_tanggal_selesai.required_if' => 'Tanggal selesai penugasan wajib diisi!',
            'sppd_tanggal_selesai.after_or_equal' => 'Tanggal selesai penugasan tidak boleh sebelum tanggal mulai!',

            'tanggal_mulai.required_if' => 'Tanggal mulai sakit wajib diisi!',
            'tanggal_selesai.required_if' => 'Tanggal selesai sakit wajib diisi!',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai sakit tidak boleh sebelum tanggal mulai!',
            'keterangan.required_if' => 'Keterangan sakit wajib diisi!',
            'surat_dokter.mimes' => 'Surat dokter harus berformat PDF, JPG, JPEG, atau PNG!',
            'surat_dokter.max' => 'Ukuran surat dokter maksimal 2 MB!',
        ]);

        if ($data['jenis'] === 'Cuti') {
            $tanggalMulaiCuti = new \DateTimeImmutable($data['cuti_tanggal_mulai']);
            $tanggalSelesaiCuti = new \DateTimeImmutable($data['cuti_tanggal_selesai']);
            $data['lama_cuti'] = (int) $tanggalMulaiCuti->diff($tanggalSelesaiCuti)->days + 1;
        }

        $nik = Auth::user()->nik;
        $id = $data['id'] ?? null;

        // Periksa pengajuan jika sedang diedit.
        $existing = null;

        if ($id) {
            $existing = DB::table('tb_absen')
                ->where('id', $id)
                ->where('nik_karyawan', $nik)
                ->first();

            if (!$existing) {
                return response()->json([
                    'message' => 'Pengajuan tidak ditemukan atau bukan milik Anda.',
                ], 403);
            }

            if ($existing->status !== 'Menunggu') {
                return response()->json([
                    'message' => 'Pengajuan yang sudah diproses tidak dapat diedit.',
                ], 422);
            }
        }

        // Periksa kelengkapan setiap baris karyawan SPPD.
        $sppdKaryawan = [];

        if (($data['jenis'] ?? null) === 'SPPD') {
            foreach (($data['sppd_karyawan'] ?? []) as $index => $pegawai) {
                $fields = [
                    'nik',
                    'nama',
                    'position',
                    'kota_asal',
                    'kota_tujuan',
                ];

                $hasValue = false;

                foreach ($fields as $field) {
                    if (trim((string) ($pegawai[$field] ?? '')) !== '') {
                        $hasValue = true;
                        break;
                    }
                }

                if (!$hasValue) {
                    continue;
                }

                foreach ($fields as $field) {
                    if (trim((string) ($pegawai[$field] ?? '')) === '') {
                        return response()->json([
                            'message' => 'Data karyawan SPPD pada baris ' . ($index + 1)
                                . ' belum lengkap. Lengkapi NIK, nama, posisi, kota asal, dan kota tujuan.',
                        ], 422);
                    }
                }

                $sppdKaryawan[] = [
                    'nik' => $pegawai['nik'],
                    'nama' => $pegawai['nama'],
                    'position' => $pegawai['position'],
                    'kota_asal' => $pegawai['kota_asal'],
                    'kota_tujuan' => $pegawai['kota_tujuan'],
                ];
            }

            if (count($sppdKaryawan) === 0) {
                return response()->json([
                    'message' => 'Minimal satu karyawan yang ditugaskan wajib diisi.',
                ], 422);
            }
        }

        $oldSuratPath = null;

        $result = DB::transaction(function () use (
            $request,
            $data,
            $nik,
            $id,
            $existing,
            $sppdKaryawan,
            &$oldSuratPath
        ) {
            $now = now();

            // Ambil file surat dokter sebelumnya.
            if ($existing) {
                $oldSuratPath = $existing->surat_dokter_path;

                if ($existing->jenis === 'Sakit') {
                    $oldDetail = DB::table('tb_absen_sakit')
                        ->where('absen_id', $existing->id)
                        ->first();

                    $oldSuratPath = $oldDetail->surat_dokter_path
                        ?? $oldSuratPath;
                }
            }

            // Tentukan tanggal utama sesuai jenis pengajuan.
            if ($data['jenis'] === 'Cuti') {
                $tanggalMulai = $data['cuti_tanggal_mulai'];
                $tanggalSelesai = $data['cuti_tanggal_selesai'];
                $keterangan = $data['alasan_cuti'];
            } elseif ($data['jenis'] === 'SPPD') {
                $tanggalMulai = $data['sppd_tanggal_mulai'];
                $tanggalSelesai = $data['sppd_tanggal_selesai'];
                $keterangan = $data['perihal_pekerjaan'];
            } else {
                $tanggalMulai = $data['tanggal_mulai'];
                $tanggalSelesai = $data['tanggal_selesai'];
                $keterangan = $data['keterangan'];
            }

            // Siapkan nomor dokumen.
            if ($existing) {
                $nomorDokumen = $existing->nomor_dokumen;
            } else {
                $datePart = $now->format('Ymd');

                $sequence = (int) DB::table('tb_absen')
                    ->where('nomor_dokumen', 'like', "ABS-{$datePart}-%")
                    ->count() + 1;

                do {
                    $nomorDokumen = 'ABS-' . $datePart . '-'
                        . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

                    $exists = DB::table('tb_absen')
                        ->where('nomor_dokumen', $nomorDokumen)
                        ->exists();

                    $sequence++;
                } while ($exists);
            }

            // Simpan file surat dokter jika ada unggahan baru.
            $suratDokterPath = $oldSuratPath;

            if ($request->hasFile('surat_dokter')) {
                $suratDokterPath = $request->file('surat_dokter')
                    ->store('absen/surat-dokter', 'local');
            }

            if ($data['jenis'] !== 'Sakit') {
                $suratDokterPath = null;
            }

            $values = [
                'nik_karyawan' => $nik,
                'jenis' => $data['jenis'],
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'nomor_dokumen' => $nomorDokumen,
                'status' => 'Menunggu',
                'keterangan' => $keterangan,
                'keterangan_admin' => null,
                'pdf_path' => null,
                'surat_dokter_path' => $suratDokterPath,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('tb_absen')
                    ->where('id', $id)
                    ->where('nik_karyawan', $nik)
                    ->where('status', 'Menunggu')
                    ->update($values);

                $absenId = (int) $id;
            } else {
                $values['created_at'] = $now;

                $absenId = (int) DB::table('tb_absen')->insertGetId($values);
            }

            // Hapus detail lama agar tidak ada data jenis sebelumnya.
            DB::table('tb_absen_cuti')->where('absen_id', $absenId)->delete();
            DB::table('tb_absen_sppd')->where('absen_id', $absenId)->delete();
            DB::table('tb_absen_sppd_karyawan')->where('absen_id', $absenId)->delete();
            DB::table('tb_absen_sakit')->where('absen_id', $absenId)->delete();

            // SIMPAN DETAIL CUTI
            if ($data['jenis'] === 'Cuti') {
                DB::table('tb_absen_cuti')->insert([
                    'absen_id' => $absenId,
                    'jenis_cuti' => $data['jenis_cuti'],
                    'lama_cuti' => $data['lama_cuti'],
                    'band' => $data['band'] ?? null,
                    'jabatan' => $data['jabatan'] ?? null,
                    'unit_kerja' => $data['unit_kerja'] ?? null,
                    'tanggal_mulai' => $data['cuti_tanggal_mulai'],
                    'tanggal_selesai' => $data['cuti_tanggal_selesai'],
                    'alamat_selama_cuti' => $data['alamat_selama_cuti'],
                    'telepon_cuti' => $data['telepon_cuti'],
                    'perjalanan_berangkat_mulai' => $data['perjalanan_berangkat_mulai'] ?? null,
                    'perjalanan_berangkat_selesai' => $data['perjalanan_berangkat_selesai'] ?? null,
                    'perjalanan_pulang_mulai' => $data['perjalanan_pulang_mulai'] ?? null,
                    'perjalanan_pulang_selesai' => $data['perjalanan_pulang_selesai'] ?? null,
                    'alasan_cuti' => $data['alasan_cuti'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // SIMPAN DETAIL SPPD
            if ($data['jenis'] === 'SPPD') {
                DB::table('tb_absen_sppd')->insert([
                    'absen_id' => $absenId,
                    'tanggal_surat' => $data['tanggal_surat'],
                    'perihal_pekerjaan' => $data['perihal_pekerjaan'],
                    'lokasi_pekerjaan' => $data['lokasi_pekerjaan'],
                    'tanggal_mulai_penugasan' => $data['sppd_tanggal_mulai'],
                    'tanggal_selesai_penugasan' => $data['sppd_tanggal_selesai'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($sppdKaryawan as $pegawai) {
                    DB::table('tb_absen_sppd_karyawan')->insert([
                        'absen_id' => $absenId,
                        'nik' => $pegawai['nik'],
                        'nama' => $pegawai['nama'],
                        'position' => $pegawai['position'],
                        'kota_asal' => $pegawai['kota_asal'],
                        'kota_tujuan' => $pegawai['kota_tujuan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // SIMPAN DETAIL SAKIT
            if ($data['jenis'] === 'Sakit') {
                DB::table('tb_absen_sakit')->insert([
                    'absen_id' => $absenId,
                    'surat_dokter_path' => $suratDokterPath,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return [
                'id' => $absenId,
                'nomor_dokumen' => $nomorDokumen,
            ];
        });

        // Hapus surat dokter lama jika diganti atau jenis pengajuan berubah.
        if (
            $oldSuratPath &&
            (
                $data['jenis'] !== 'Sakit' ||
                $request->hasFile('surat_dokter')
            )
        ) {
            Storage::disk('local')->delete($oldSuratPath);
        }

        return response()->json([
            'message' => $id
                ? 'Pengajuan berhasil diperbarui.'
                : 'Pengajuan berhasil diajukan.',
            'id' => $result['id'],
            'nomor_dokumen' => $result['nomor_dokumen'],
        ]);
    }

    public function absen_delete(Request $request)
    {
        abort_unless(!Auth::user()->isHcm(), 403);

        $request->validate([
            'id' => 'required|integer|exists:tb_absen,id',
        ]);

        $nik = Auth::user()->nik;

        $absen = DB::table('tb_absen')
            ->where('id', $request->id)
            ->where('nik_karyawan', $nik)
            ->first();

        if (!$absen) {
            return response()->json([
                'message' => 'Pengajuan tidak ditemukan atau bukan milik Anda.',
            ], 403);
        }

        if ($absen->status !== 'Menunggu') {
            return response()->json([
                'message' => 'Pengajuan yang sudah diproses tidak dapat dihapus.',
            ], 422);
        }

        DB::table('tb_absen')
            ->where('id', $request->id)
            ->where('nik_karyawan', $nik)
            ->where('status', 'Menunggu')
            ->delete();

        return response()->json([
            'message' => 'Pengajuan berhasil dihapus.',
        ]);
    }

    public function absen_approve(Request $request)
    {
        abort_unless(Auth::user()->isHcm(), 403);

        $data = $request->validate([
            'id' => 'required|integer|exists:tb_absen,id',
            'keterangan_admin' => 'required|string|max:5000',
        ], [
            'keterangan_admin.required' => 'Keterangan dari HCM wajib diisi!',
        ]);

        $pdfPath = null;

        try {
            $result = DB::transaction(function () use ($data, &$pdfPath) {

                // Ambil pengajuan yang masih menunggu persetujuan.
                $absen = DB::table('tb_absen')
                    ->where('id', $data['id'])
                    ->where('status', 'Menunggu')
                    ->lockForUpdate()
                    ->first();

                if (!$absen) {
                    return false;
                }

                // Ambil data karyawan.
                $karyawan = DB::table('tb_employee')
                    ->where('nik', $absen->nik_karyawan)
                    ->first();

                if (!$karyawan) {
                    throw new \RuntimeException('Data karyawan tidak ditemukan.');
                }

                // Tentukan template PDF berdasarkan jenis pengajuan.
                switch ($absen->jenis) {

                    case 'Cuti':
                        $cuti = DB::table('tb_absen_cuti')
                            ->where('absen_id', $absen->id)
                            ->first();

                        if (!$cuti) {
                            throw new \RuntimeException('Data detail cuti tidak ditemukan.');
                        }

                        $pdfContents = AbsenPdfGenerator::cuti($absen, $karyawan, $cuti);

                        break;

                    case 'SPPD':
                        $sppd = DB::table('tb_absen_sppd')
                            ->where('absen_id', $absen->id)
                            ->first();

                        if (!$sppd) {
                            throw new \RuntimeException('Data detail SPPD tidak ditemukan.');
                        }

                        $sppdKaryawan = DB::table('tb_absen_sppd_karyawan')
                            ->where('absen_id', $absen->id)
                            ->orderBy('id')
                            ->get();

                        $pdfContents = AbsenPdfGenerator::sppd($sppd, $sppdKaryawan);

                        break;

                    case 'Sakit':
                        $pdf = Pdf::loadView(
                            'operasional.pdf.sakit',
                            [
                                'absen' => $absen,
                                'karyawan' => $karyawan,
                            ]
                        );

                        $pdf->setPaper('a4', 'portrait');
                        $pdfContents = $pdf->output();
                        break;

                    default:
                        throw new \RuntimeException('Jenis pengajuan tidak dikenali.');
                }

                // Simpan hasil PDF dari template Word (atau dari view untuk jenis sakit).
                $pdfPath = 'absen/pdf/' . $absen->nomor_dokumen . '.pdf';

                $saved = Storage::disk('local')->put(
                    $pdfPath,
                    $pdfContents
                );

                if (!$saved) {
                    throw new \RuntimeException('PDF gagal disimpan.');
                }

                // Status berubah setelah PDF berhasil disimpan.
                DB::table('tb_absen')
                    ->where('id', $absen->id)
                    ->where('status', 'Menunggu')
                    ->update([
                        'status' => 'Disetujui',
                        'keterangan_admin' => $data['keterangan_admin'],
                        'pdf_path' => $pdfPath,
                        'updated_at' => now(),
                    ]);

                return true;
            });

            if ($result === false) {
                return response()->json([
                    'message' => 'Pengajuan tidak ditemukan atau sudah diproses sebelumnya.',
                ], 422);
            }

            return response()->json([
                'message' => 'Pengajuan berhasil disetujui dan PDF berhasil dibuat.',
            ]);

        } catch (\Throwable $e) {

            // Hapus file jika proses persetujuan mengalami kegagalan.
            if ($pdfPath && Storage::disk('local')->exists($pdfPath)) {
                Storage::disk('local')->delete($pdfPath);
            }

            report($e);

            return response()->json([
                'message' => 'Persetujuan gagal. PDF tidak berhasil dibuat. Periksa konfigurasi template dan logo.',
            ], 500);
        }
    }

    public function absen_download($id)
    {
        // Cari data pengajuan.
        $absen = DB::table('tb_absen')
            ->where('id', $id)
            ->first();

        if (!$absen) {
            abort(404, 'Data pengajuan tidak ditemukan.');
        }

        // Hanya HCM atau karyawan pemilik pengajuan yang boleh mengunduh.
        $isHcm = Auth::user()->isHcm();

        $isOwner = (string) Auth::user()->nik === (string) $absen->nik_karyawan;

        abort_unless($isHcm || $isOwner, 403);

        // PDF hanya tersedia untuk pengajuan yang disetujui.
        abort_unless(
            $absen->status === 'Disetujui'
            && !empty($absen->pdf_path),
            404,
            'PDF belum tersedia.'
        );

        // Pastikan file PDF benar-benar tersedia.
        if (!Storage::disk('local')->exists($absen->pdf_path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        // Unduh PDF.
        return Storage::disk('local')->download(
            $absen->pdf_path,
            $absen->nomor_dokumen . '.pdf'
        );
    }

    public function absen_reject(Request $request)
    {
        abort_unless(Auth::user()->isHcm(), 403);

        $data = $request->validate([
            'id' => 'required|integer|exists:tb_absen,id',
            'keterangan_admin' => 'required|string|max:5000',
        ], [
            'keterangan_admin.required' => 'Keterangan dari HCM wajib diisi!',
        ]);

        $updated = DB::table('tb_absen')
            ->where('id', $data['id'])
            ->where('status', 'Menunggu')
            ->update([
                'status' => 'Ditolak',
                'keterangan_admin' => $data['keterangan_admin'],
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'Pengajuan tidak ditemukan atau sudah diproses sebelumnya.',
            ], 422);
        }

        return response()->json([
            'message' => 'Pengajuan berhasil ditolak.',
        ]);
    }

    public function alat_kerja_list(Request $request)
    {
        $table   = $this->readDataTableRequest($request, self::ALAT_KERJA_SORTABLE_COLUMNS);
        $filters = $this->readFilters($request, self::ALAT_KERJA_FILTER_RULES);
        $result  = OperasionalModel::alatKerjaList($table, $filters);

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function alat_kerja_save(Request $request)
    {
        $alatKerja = $request->validate([
            'id'                 => 'nullable|integer|exists:tb_alat_kerja,id',
            'kode_alat'          => ['required', 'string', 'max:50', Rule::unique('tb_alat_kerja', 'kode_alat')->ignore($request->id)],
            'nama_alat'          => 'required|string|max:100',
            'tanggal_penyerahan' => 'required|date',
            'kondisi'            => 'required|in:Baik,Rusak',
            'status'             => 'required|in:Sudah Dikembalikan,Belum Dikembalikan',
        ], [
            'kode_alat.required'          => 'Kode alat wajib diisi!',
            'kode_alat.unique'            => 'Kode alat sudah digunakan!',
            'nama_alat.required'          => 'Nama alat wajib diisi!',
            'tanggal_penyerahan.required' => 'Tanggal penyerahan wajib diisi!',
            'kondisi.required'            => 'Kondisi alat wajib dipilih!',
            'status.required'             => 'Status pengembalian wajib dipilih!',
        ]);

        $alatKerja['nik_karyawan'] = Auth::user()->nik;

        OperasionalModel::saveAlatKerja($alatKerja, Auth::id());

        return response()->json([
            'message' => 'Data alat kerja berhasil disimpan.',
        ]);
    }

    public function alat_kerja_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_alat_kerja,id',
        ]);

        OperasionalModel::deleteAlatKerja($request->id);

        return response()->json([
            'message' => 'Data alat kerja berhasil dihapus.',
        ]);
    }

    public function sto_list(Request $request)
    {
        $table   = $this->readDataTableRequest($request, self::STO_SORTABLE_COLUMNS);
        $filters = $this->readFilters($request, self::STO_FILTER_RULES);
        $result  = OperasionalModel::stoList($table, $filters);

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function sto_markers(Request $request)
    {
        $filters = $this->readFilters($request, self::STO_FILTER_RULES);

        return response()->json([
            'data' => OperasionalModel::stoMarkers($filters, self::MAX_STO_MARKERS),
        ]);
    }

    public function sto_save(Request $request)
    {
        $sto = $request->validate([
            'id'        => 'nullable|integer|exists:tb_sto,id',
            'kode_sto'  => ['required', 'string', 'max:20', Rule::unique('tb_sto', 'kode_sto')->ignore($request->id)],
            'nama_sto'  => 'required|string|max:100',
            'alamat'    => 'required|string|max:255',
            'kecamatan' => 'nullable|string|max:100',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status'    => 'required|in:Aktif,Nonaktif',
        ], [
            'kode_sto.required'  => 'Kode STO wajib diisi!',
            'kode_sto.unique'    => 'Kode STO sudah digunakan!',
            'nama_sto.required'  => 'Nama STO wajib diisi!',
            'alamat.required'    => 'Alamat wajib diisi!',
            'latitude.required'  => 'Latitude wajib diisi!',
            'latitude.between'   => 'Latitude harus di antara -90 dan 90!',
            'longitude.required' => 'Longitude wajib diisi!',
            'longitude.between'  => 'Longitude harus di antara -180 dan 180!',
            'status.required'    => 'Status wajib dipilih!',
        ]);

        OperasionalModel::saveSto($sto);

        return response()->json([
            'message' => 'Data STO berhasil disimpan.',
        ]);
    }

    public function sto_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_sto,id',
        ]);

        OperasionalModel::deleteSto($request->id);

        return response()->json([
            'message' => 'Data STO berhasil dihapus.',
        ]);
    }

    private function readDataTableRequest(Request $request, array $sortableColumns)
    {
        $request->validate([
            'draw'           => 'required|integer|min:0',
            'start'          => 'required|integer|min:0',
            'length'         => 'required|integer|min:1|max:100',
            'search.value'   => 'nullable|string|max:100',
            'order.0.column' => 'nullable|integer|min:0',
            'order.0.dir'    => 'nullable|in:asc,desc',
        ]);

        $orderIndex = (int) $request->input('order.0.column', 0);

        return [
            'draw'           => (int) $request->draw,
            'start'          => (int) $request->start,
            'length'         => (int) $request->length,
            'search'         => trim((string) $request->input('search.value', '')),
            'orderColumn'    => $sortableColumns[$orderIndex] ?? $sortableColumns[0],
            'orderDirection' => $request->input('order.0.dir', 'asc'),
        ];
    }

    private function readFilters(Request $request, array $filterRules)
    {
        $rules = [];

        foreach ($filterRules as $fieldName => $rule) {
            $rules['filters.' . $fieldName] = $rule;
        }

        $filters = $request->validate($rules)['filters'] ?? [];

        return array_filter($filters, fn ($value) => $value !== null && $value !== '');
    }

    private function dataTableResponse($draw, array $result)
    {
        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $result['totalRows'],
            'recordsFiltered' => $result['filteredRows'],
            'data'            => $result['rows'],
        ]);
    }
}
