<?php

namespace App\Http\Controllers;

use App\Models\AdministratorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Sto;

class AjaxController extends Controller
{
    private const EMPLOYEE_SORTABLE_COLUMNS = [
        'tb_employee.nik',
        'tb_employee.name',
        'tb_employee.email',
        'tb_employee.number_phone',
        'tb_role.name',
        'tb_employee.status',
    ];

    private const ROLE_SORTABLE_COLUMNS = [
        'name',
    ];

    private const SELECT_OPTIONS_PER_PAGE = 20;

    public function employee_list(Request $request)
    {
        $table  = $this->readDataTableRequest($request, self::EMPLOYEE_SORTABLE_COLUMNS);
        $result = AdministratorModel::employeeList($table['search'], $table['orderColumn'], $table['orderDirection'], $table['start'], $table['length']);

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
        $table  = $this->readDataTableRequest($request, self::ROLE_SORTABLE_COLUMNS);
        $result = AdministratorModel::roleList($table['search'], $table['orderColumn'], $table['orderDirection'], $table['start'], $table['length']);

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
        $table = $this->readDataTableRequest($request, ['name']);

        $result = AdministratorModel::positionList(
            $table['search'],
            $table['orderColumn'],
            $table['orderDirection'],
            $table['start'],
            $table['length']
        );

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function position_save(Request $request)
    {
        $position = $request->validate([
            'id' => 'nullable|integer|exists:tb_position,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tb_position', 'name')->ignore($request->id),
            ],
        ], [
            'name.required' => 'Nama position wajib diisi!',
            'name.unique' => 'Nama position sudah ada!',
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

    
    public function alatKerja_list(Request $request)
    {
        $sortableColumns = [
            'nik_karyawan',
            'kode_alat',
            'nama_alat',
            'tanggal_penyerahan',
            'kondisi',
            'status',
        ];

        $table = $this->readDataTableRequest($request, $sortableColumns);

        $result = AdministratorModel::alatKerjaList(
            $table['search'],
            $table['orderColumn'],
            $table['orderDirection'],
            $table['start'],
            $table['length']
        );

        return $this->dataTableResponse($table['draw'], $result);
    }

    public function alatKerja_save(Request $request)
    {
        $alat = $request->validate([
            'id' => 'nullable|integer|exists:tb_alat_kerja,id',
            'kode_alat' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tb_alat_kerja', 'kode_alat')->ignore($request->id),
            ],
            'nama_alat' => 'required|string|max:100',
            'tanggal_penyerahan' => 'required|date',
            'kondisi' => 'required|in:Baik,Rusak',
        ], [
            'kode_alat.required' => 'Kode alat wajib diisi!',
            'kode_alat.unique' => 'Kode alat sudah digunakan!',
            'nama_alat.required' => 'Nama alat wajib diisi!',
            'tanggal_penyerahan.required' => 'Tanggal penyerahan wajib diisi!',
            'kondisi.required' => 'Kondisi alat wajib dipilih!',
        ]);
        $alat['nik_karyawan'] = Auth::user()->nik;

        AdministratorModel::saveAlatKerja($alat, Auth::id());

        return response()->json([
            'message' => 'Data alat kerja berhasil disimpan.',
        ]);
    }


    public function alatKerja_delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_alat_kerja,id',
        ]);

        AdministratorModel::deleteAlatKerja($request->id);

        return response()->json([
            'message' => 'Data alat kerja berhasil dihapus.',
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

    private function dataTableResponse($draw, array $result)
    {
        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $result['totalRows'],
            'recordsFiltered' => $result['filteredRows'],
            'data'            => $result['rows'],
        ]);
    }

    
    public function sto_list()
    {
        $data = Sto::orderBy('kode_sto')->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function sto_save(\Illuminate\Http\Request $request)
    {
        $id = $request->input('id');

        $validated = $request->validate([
            'kode_sto' => 'required|string|max:20|unique:tb_sto,kode_sto,' . ($id ?? 'NULL') . ',id',
            'nama_sto' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'kecamatan' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        if ($id) {
            $sto = Sto::findOrFail($id);
            $sto->update($validated);
        } else {
            $sto = Sto::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data STO berhasil disimpan.',
            'data' => $sto
        ]);
    }

    public function sto_delete(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tb_sto,id',
        ]);

        Sto::findOrFail($request->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data STO berhasil dihapus.'
        ]);
    }
}
