<?php

namespace App\Http\Controllers;

use App\Models\AbsenModel;
use App\Models\AdministratorModel;
use App\Models\AuthModel;
use App\Models\HomeModel;
use App\Models\OperasionalModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    private const ABSEN_SORTABLE_COLUMNS = [
        'tb_absen.id',
        'tb_absen.nik_karyawan',
        'tb_employee.name',
        'tb_absen.jenis',
        'tb_absen.tanggal_mulai',
        'tb_absen.tanggal_selesai',
        'tb_absen.nomor_dokumen',
        'tb_absen.status',
    ];

    private const ABSEN_FILTER_RULES = [
        'karyawan'        => 'nullable|string|max:100',
        'nomor_dokumen'   => 'nullable|string|max:50',
        'jenis'           => 'nullable|in:SPPD,Sakit,Cuti',
        'status'          => 'nullable|in:Menunggu,Disetujui,Ditolak',
        'tanggal_mulai'   => 'nullable|date',
        'tanggal_selesai' => 'nullable|date',
    ];

    private const ABSEN_SAVE_RULES = [
        'id'                           => 'nullable|integer|exists:tb_absen,id',
        'jenis'                        => 'required|in:SPPD,Sakit,Cuti',
        'jenis_cuti'                   => 'required_if:jenis,Cuti|nullable|in:Tahunan,Besar,CAP,Sakit,Bersalin,Haid',
        'band'                         => 'nullable|string|max:100',
        'jabatan'                      => 'nullable|string|max:150',
        'unit_kerja'                   => 'nullable|string|max:150',
        'cuti_tanggal_mulai'           => 'required_if:jenis,Cuti|nullable|date',
        'cuti_tanggal_selesai'         => 'required_if:jenis,Cuti|nullable|date|after_or_equal:cuti_tanggal_mulai',
        'alamat_selama_cuti'           => 'required_if:jenis,Cuti|nullable|string|max:5000',
        'telepon_cuti'                 => 'required_if:jenis,Cuti|nullable|string|max:20',
        'perjalanan_berangkat_mulai'   => 'nullable|date',
        'perjalanan_berangkat_selesai' => 'nullable|date|after_or_equal:perjalanan_berangkat_mulai',
        'perjalanan_pulang_mulai'      => 'nullable|date',
        'perjalanan_pulang_selesai'    => 'nullable|date|after_or_equal:perjalanan_pulang_mulai',
        'alasan_cuti'                  => 'required_if:jenis,Cuti|nullable|string|max:5000',
        'tanggal_surat'                => 'required_if:jenis,SPPD|nullable|date',
        'perihal_pekerjaan'            => 'required_if:jenis,SPPD|nullable|string|max:255',
        'lokasi_pekerjaan'             => 'required_if:jenis,SPPD|nullable|string|max:255',
        'sppd_tanggal_mulai'           => 'required_if:jenis,SPPD|nullable|date',
        'sppd_tanggal_selesai'         => 'required_if:jenis,SPPD|nullable|date|after_or_equal:sppd_tanggal_mulai',
        'sppd_karyawan'                => 'nullable|array|max:3',
        'sppd_karyawan.*.nik'          => 'nullable|string|max:30',
        'sppd_karyawan.*.nama'         => 'nullable|string|max:150',
        'sppd_karyawan.*.position'     => 'nullable|string|max:150',
        'sppd_karyawan.*.kota_asal'    => 'nullable|string|max:150',
        'sppd_karyawan.*.kota_tujuan'  => 'nullable|string|max:150',
        'tanggal_mulai'                => 'required_if:jenis,Sakit|nullable|date',
        'tanggal_selesai'              => 'required_if:jenis,Sakit|nullable|date|after_or_equal:tanggal_mulai',
        'keterangan'                   => 'required_if:jenis,Sakit|nullable|string|max:5000',
        'surat_dokter'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ];

    private const ABSEN_SAVE_MESSAGES = [
        'jenis.required'                      => 'Jenis absen wajib dipilih!',
        'jenis.in'                            => 'Jenis absen tidak valid!',
        'jenis_cuti.required_if'              => 'Jenis cuti wajib dipilih!',
        'cuti_tanggal_mulai.required_if'      => 'Tanggal mulai cuti wajib diisi!',
        'cuti_tanggal_selesai.required_if'    => 'Tanggal selesai cuti wajib diisi!',
        'cuti_tanggal_selesai.after_or_equal' => 'Tanggal selesai cuti tidak boleh sebelum tanggal mulai!',
        'alamat_selama_cuti.required_if'      => 'Alamat selama cuti wajib diisi!',
        'telepon_cuti.required_if'            => 'Nomor telepon selama cuti wajib diisi!',
        'alasan_cuti.required_if'             => 'Alasan cuti wajib diisi!',
        'tanggal_surat.required_if'           => 'Tanggal surat wajib diisi!',
        'perihal_pekerjaan.required_if'       => 'Perihal pekerjaan wajib diisi!',
        'lokasi_pekerjaan.required_if'        => 'Lokasi pekerjaan wajib diisi!',
        'sppd_tanggal_mulai.required_if'      => 'Tanggal mulai penugasan wajib diisi!',
        'sppd_tanggal_selesai.required_if'    => 'Tanggal selesai penugasan wajib diisi!',
        'sppd_tanggal_selesai.after_or_equal' => 'Tanggal selesai penugasan tidak boleh sebelum tanggal mulai!',
        'tanggal_mulai.required_if'           => 'Tanggal mulai sakit wajib diisi!',
        'tanggal_selesai.required_if'         => 'Tanggal selesai sakit wajib diisi!',
        'tanggal_selesai.after_or_equal'      => 'Tanggal selesai sakit tidak boleh sebelum tanggal mulai!',
        'keterangan.required_if'              => 'Keterangan sakit wajib diisi!',
        'surat_dokter.mimes'                  => 'Surat dokter harus berformat PDF, JPG, JPEG, atau PNG!',
        'surat_dokter.max'                    => 'Ukuran surat dokter maksimal 2 MB!',
    ];

    private const ABSEN_DECISION_RULES = [
        'id'               => 'required|integer|exists:tb_absen,id',
        'keterangan_admin' => 'required|string|max:5000',
    ];

    private const ABSEN_DECISION_MESSAGES = [
        'keterangan_admin.required' => 'Keterangan dari HCM wajib diisi!',
    ];

    private const SPPD_KARYAWAN_FIELDS = ['nik', 'nama', 'position', 'kota_asal', 'kota_tujuan'];

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

        if ($approvedRows === 0)
        {
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

        if ($rejectedRows === 0)
        {
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

        if ((int) $request->id === (int) Auth::id())
        {
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

        if (AdministratorModel::isRoleUsed($request->id))
        {
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
        $table   = $this->readDataTableRequest($request, self::ABSEN_SORTABLE_COLUMNS);
        $filters = $this->readFilters($request, self::ABSEN_FILTER_RULES);
        $user    = Auth::user();
        $result  = AbsenModel::list($table, $filters, $user->isHcm() ? null : (int) $user->nik);

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function absen_nomor_dokumen()
    {
        return response()->json([
            'nomor_dokumen' => AbsenModel::nextNomorDokumen(),
        ]);
    }

    public function absen_save(Request $request)
    {
        abort_if(Auth::user()->isHcm(), 403);

        $pengajuan = $request->validate(self::ABSEN_SAVE_RULES, self::ABSEN_SAVE_MESSAGES);
        $nik       = (int) Auth::user()->nik;
        $existing  = null;

        if (! empty($pengajuan['id']))
        {
            $existing = AbsenModel::findOwned($pengajuan['id'], $nik);

            if (! $existing)
            {
                return response()->json(['message' => 'Pengajuan tidak ditemukan atau bukan milik Anda.'], 403);
            }

            if ($existing->status !== AbsenModel::STATUS_PENDING)
            {
                return response()->json(['message' => 'Pengajuan yang sudah diproses tidak dapat diedit.'], 422);
            }
        }

        $sppdKaryawan = [];

        if ($pengajuan['jenis'] === 'SPPD')
        {
            $sppdKaryawan = $this->completeSppdKaryawan($pengajuan['sppd_karyawan'] ?? []);
        }

        if ($pengajuan['jenis'] === 'Cuti')
        {
            $pengajuan['lama_cuti'] = $this->countLeaveDays($pengajuan['cuti_tanggal_mulai'], $pengajuan['cuti_tanggal_selesai']);
        }

        $oldSuratPath    = $existing->surat_dokter_path ?? null;
        $newSuratPath    = $request->hasFile('surat_dokter') ? $request->file('surat_dokter')->store('absen/surat-dokter', 'local') : null;
        $suratDokterPath = $pengajuan['jenis'] === 'Sakit' ? ($newSuratPath ?? $oldSuratPath) : null;

        try
        {
            $nomorDokumen = AbsenModel::save($pengajuan, $nik, $existing, $sppdKaryawan, $suratDokterPath);
        }
        catch (\Throwable $exception)
        {
            if ($newSuratPath)
            {
                Storage::disk('local')->delete($newSuratPath);
            }

            throw $exception;
        }

        if ($oldSuratPath && $oldSuratPath !== $suratDokterPath)
        {
            Storage::disk('local')->delete($oldSuratPath);
        }

        return response()->json([
            'message'       => $existing ? 'Pengajuan berhasil diperbarui.' : 'Pengajuan berhasil diajukan.',
            'nomor_dokumen' => $nomorDokumen,
        ]);
    }

    public function absen_delete(Request $request)
    {
        abort_if(Auth::user()->isHcm(), 403);

        $request->validate([
            'id' => 'required|integer|exists:tb_absen,id',
        ]);

        $nik   = (int) Auth::user()->nik;
        $absen = AbsenModel::findOwned($request->id, $nik);

        if (! $absen)
        {
            return response()->json(['message' => 'Pengajuan tidak ditemukan atau bukan milik Anda.'], 403);
        }

        if ($absen->status !== AbsenModel::STATUS_PENDING)
        {
            return response()->json(['message' => 'Pengajuan yang sudah diproses tidak dapat dihapus.'], 422);
        }

        AbsenModel::delete($absen->id, $nik);

        if ($absen->surat_dokter_path)
        {
            Storage::disk('local')->delete($absen->surat_dokter_path);
        }

        return response()->json([
            'message' => 'Pengajuan berhasil dihapus.',
        ]);
    }

    public function absen_approve(Request $request)
    {
        abort_unless(Auth::user()->isHcm(), 403);

        $decision = $request->validate(self::ABSEN_DECISION_RULES, self::ABSEN_DECISION_MESSAGES);

        if (! AbsenModel::approve($decision['id'], $decision['keterangan_admin'], (int) Auth::user()->nik))
        {
            return response()->json(['message' => 'Pengajuan tidak ditemukan atau sudah diproses sebelumnya.'], 422);
        }

        return response()->json([
            'message' => 'Pengajuan berhasil disetujui. PDF sudah dapat diunduh.',
        ]);
    }

    public function absen_reject(Request $request)
    {
        abort_unless(Auth::user()->isHcm(), 403);

        $decision = $request->validate(self::ABSEN_DECISION_RULES, self::ABSEN_DECISION_MESSAGES);

        if (! AbsenModel::reject($decision['id'], $decision['keterangan_admin']))
        {
            return response()->json(['message' => 'Pengajuan tidak ditemukan atau sudah diproses sebelumnya.'], 422);
        }

        return response()->json([
            'message' => 'Pengajuan berhasil ditolak.',
        ]);
    }

    public function absen_download(int $id)
    {
        $user  = Auth::user();
        $absen = AbsenModel::findForDownload($id);

        abort_unless($absen, 404);
        abort_unless($user->isHcm() || (int) $user->nik === (int) $absen->nik_karyawan, 403);
        abort_unless($absen->status === AbsenModel::STATUS_APPROVED, 404);

        try
        {
            $pdfContents = AbsenModel::buildPdf($absen);
        }
        catch (\Throwable $exception)
        {
            report($exception);

            abort(500, 'PDF tidak berhasil dibuat.');
        }

        Log::channel('audit')->info('absen.pdf.download', [
            'username'      => $user->nik,
            'nomor_dokumen' => $absen->nomor_dokumen,
            'ip'            => request()->ip(),
        ]);

        return response($pdfContents, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$absen->nomor_dokumen.'.pdf"',
            'Cache-Control'       => 'no-store, private',
        ]);
    }

    private function completeSppdKaryawan(array $submittedRows)
    {
        $completeRows = [];

        foreach ($submittedRows as $rowIndex => $submittedRow)
        {
            $row         = array_map(fn ($value) => trim((string) $value), array_intersect_key($submittedRow, array_flip(self::SPPD_KARYAWAN_FIELDS)));
            $filledCount = count(array_filter($row, fn ($value) => $value !== ''));

            if ($filledCount === 0)
            {
                continue;
            }

            if ($filledCount < count(self::SPPD_KARYAWAN_FIELDS))
            {
                throw ValidationException::withMessages([
                    'sppd_karyawan' => 'Data karyawan SPPD pada baris '.($rowIndex + 1).' belum lengkap. Lengkapi NIK, nama, posisi, kota asal, dan kota tujuan.',
                ]);
            }

            $completeRows[] = $row;
        }

        if ($completeRows === [])
        {
            throw ValidationException::withMessages([
                'sppd_karyawan' => 'Minimal satu karyawan yang ditugaskan wajib diisi.',
            ]);
        }

        return $completeRows;
    }

    private function countLeaveDays(string $startDate, string $endDate)
    {
        return (new \DateTimeImmutable($startDate))->diff(new \DateTimeImmutable($endDate))->days + 1;
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

        foreach ($filterRules as $fieldName => $rule)
        {
            $rules['filters.'.$fieldName] = $rule;
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
