<?php

namespace App\Http\Controllers;

use App\Models\AdministratorModel;
use App\Models\AuthModel;
use App\Models\HomeModel;
use App\Models\OperasionalModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if ($approvedRows === 0)
        {
            return response()->json(['message' => 'Akun ini tidak sedang menunggu persetujuan.'], 422);
        }

        return response()->json(['message' => 'Akun berhasil disetujui dan sekarang bisa login.']);
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
            return response()->json(['message' => 'Akun ini tidak sedang menunggu persetujuan.'], 422);
        }

        return response()->json(['message' => 'Pendaftaran akun berhasil ditolak.']);
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
            'number_phone.regex'        => 'No. HP hanya boleh angka dan tanda +.',
            'email.email'               => 'Format email tidak valid!',
            'role_id.required'          => 'Role wajib dipilih!',
            'status.required'           => 'Status wajib dipilih!',
            'password.required_without' => 'Password wajib diisi untuk karyawan baru!',
            'password.min'              => 'Password minimal 8 karakter!',
        ]);

        AdministratorModel::saveEmployee($employee, Auth::id());

        return response()->json(['message' => 'Data karyawan berhasil disimpan.']);
    }

    public function employee_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_employee,id',
        ]);

        if ((int) $request->id === (int) Auth::id())
        {
            return response()->json(['message' => 'Akun yang sedang Anda pakai tidak bisa dihapus.'], 422);
        }

        AdministratorModel::deleteEmployee($request->id);

        return response()->json(['message' => 'Data karyawan berhasil dihapus.']);
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

        return response()->json(['message' => 'Data role berhasil disimpan.']);
    }

    public function role_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_role,id',
        ]);

        if (AdministratorModel::isRoleUsed($request->id))
        {
            return response()->json(['message' => 'Role masih dipakai karyawan, tidak bisa dihapus.'], 422);
        }

        AdministratorModel::deleteRole($request->id);

        return response()->json(['message' => 'Data role berhasil dihapus.']);
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

        return response()->json(['message' => 'Data position berhasil disimpan.']);
    }

    public function position_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_position,id',
        ]);

        AdministratorModel::deletePosition($request->id);

        return response()->json(['message' => 'Data position berhasil dihapus.']);
    }

    public function absen_list(Request $request)
    {
        $request->validate([
            'draw'         => 'required|integer|min:0',
            'start'        => 'required|integer|min:0',
            'length'       => 'required|integer|min:1|max:100',
            'search.value' => 'nullable|string|max:100',
        ]);

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
                'tb_absen.created_at'
            );

        $total = DB::table('tb_absen')->count();

        $keyword = trim((string) $request->input('search.value', ''));

        if ($keyword !== '')
        {
            $query->where(function ($q) use ($keyword)
            {
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
            ->map(function ($row)
            {
                $row->karyawan = $row->nama_karyawan
                    .' ('.$row->nik_karyawan.')';

                return $row;
            });

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ]);
    }

    public function absen_save(Request $request)
    {
        $data = $request->validate([
            'id'              => 'nullable|integer|exists:tb_absen,id',
            'jenis'           => 'required|in:SPPD,Sakit,Cuti',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:5000',
        ], [
            'jenis.required'                 => 'Jenis absen wajib dipilih!',
            'jenis.in'                       => 'Jenis harus SPPD, Sakit, atau Cuti!',
            'tanggal_mulai.required'         => 'Tanggal mulai wajib diisi!',
            'tanggal_selesai.required'       => 'Tanggal selesai wajib diisi!',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai!',
            'keterangan.required'            => 'Keterangan wajib diisi!',
        ]);

        $employee = DB::table('tb_employee')
            ->where('id', Auth::id())
            ->first();

        if (! $employee)
        {
            return response()->json([
                'message' => 'Data karyawan yang login tidak ditemukan.',
            ], 422);
        }

        $values = [
            'nik_karyawan'    => $employee->nik,
            'jenis'           => $data['jenis'],
            'tanggal_mulai'   => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'keterangan'      => $data['keterangan'],
            'updated_at'      => now(),
        ];

        if (! empty($data['id']))
        {
            $existing = DB::table('tb_absen')
                ->where('id', $data['id'])
                ->first();

            if (! $existing)
            {
                return response()->json([
                    'message' => 'Data absen tidak ditemukan.',
                ], 404);
            }

            DB::table('tb_absen')
                ->where('id', $data['id'])
                ->update($values);

            return response()->json([
                'message' => 'Data absen berhasil diperbarui.',
            ]);
        }

        $datePart = now()->format('Ymd');

        $sequence = DB::table('tb_absen')
            ->where('nomor_dokumen', 'like', "ABS-{$datePart}-%")
            ->count() + 1;

        do
        {
            $nomorDokumen = 'ABS-'.$datePart.'-'.str_pad(
                (string) $sequence,
                3,
                '0',
                STR_PAD_LEFT
            );

            $exists = DB::table('tb_absen')
                ->where('nomor_dokumen', $nomorDokumen)
                ->exists();

            $sequence++;
        }
        while ($exists);

        $values['nomor_dokumen'] = $nomorDokumen;
        $values['status']        = 'Menunggu';
        $values['created_at']    = now();

        DB::table('tb_absen')->insert($values);

        return response()->json([
            'message' => 'Data absen berhasil disimpan.',
        ]);
    }

    public function absen_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_absen,id',
        ]);

        DB::table('tb_absen')
            ->where('id', $request->id)
            ->delete();

        return response()->json([
            'message' => 'Data absen berhasil dihapus.',
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

        return response()->json(['message' => 'Data alat kerja berhasil disimpan.']);
    }

    public function alat_kerja_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_alat_kerja,id',
        ]);

        OperasionalModel::deleteAlatKerja($request->id);

        return response()->json(['message' => 'Data alat kerja berhasil dihapus.']);
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

        return response()->json(['data' => OperasionalModel::stoMarkers($filters, self::MAX_STO_MARKERS)]);
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

        return response()->json(['message' => 'Data STO berhasil disimpan.']);
    }

    public function sto_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_sto,id',
        ]);

        OperasionalModel::deleteSto($request->id);

        return response()->json(['message' => 'Data STO berhasil dihapus.']);
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
