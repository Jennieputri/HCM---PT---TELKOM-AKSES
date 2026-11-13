<?php

use App\Database\SchemaSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_DETAIL_TABLES = ['tb_absen_cuti', 'tb_absen_sakit', 'tb_absen_sppd_karyawan', 'tb_absen_sppd'];

    private const NEW_COLUMNS = [
        'jenis_cuti',
        'lama_cuti',
        'band',
        'jabatan',
        'unit_kerja',
        'alamat_selama_cuti',
        'telepon_cuti',
        'perjalanan_berangkat_mulai',
        'perjalanan_berangkat_selesai',
        'perjalanan_pulang_mulai',
        'perjalanan_pulang_selesai',
        'tanggal_surat',
        'lokasi_pekerjaan',
        'sppd_karyawan',
    ];

    public function up(): void
    {
        SchemaSync::table('tb_absen', [
            'id'                           => fn (Blueprint $table) => $table->bigIncrements('id'),
            'nik_karyawan'                 => fn (Blueprint $table) => $table->integer('nik_karyawan')->index(),
            'jenis'                        => fn (Blueprint $table) => $table->enum('jenis', ['SPPD', 'Sakit', 'Cuti']),
            'tanggal_mulai'                => fn (Blueprint $table) => $table->date('tanggal_mulai'),
            'tanggal_selesai'              => fn (Blueprint $table) => $table->date('tanggal_selesai'),
            'nomor_dokumen'                => fn (Blueprint $table) => $table->string('nomor_dokumen', 50)->unique(),
            'status'                       => fn (Blueprint $table) => $table->enum('status', ['Menunggu', 'Disetujui', 'Ditolak'])->default('Menunggu')->index(),
            'created_at'                   => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at'                   => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
            'keterangan'                   => fn (Blueprint $table) => $table->text('keterangan')->nullable(),
            'keterangan_admin'             => fn (Blueprint $table) => $table->text('keterangan_admin')->nullable(),
            'surat_dokter_path'            => fn (Blueprint $table) => $table->string('surat_dokter_path')->nullable(),
            'jenis_cuti'                   => fn (Blueprint $table) => $table->enum('jenis_cuti', ['Tahunan', 'Besar', 'CAP', 'Sakit', 'Bersalin', 'Haid'])->nullable(),
            'lama_cuti'                    => fn (Blueprint $table) => $table->unsignedSmallInteger('lama_cuti')->nullable(),
            'band'                         => fn (Blueprint $table) => $table->string('band', 100)->nullable(),
            'jabatan'                      => fn (Blueprint $table) => $table->string('jabatan', 150)->nullable(),
            'unit_kerja'                   => fn (Blueprint $table) => $table->string('unit_kerja', 150)->nullable(),
            'alamat_selama_cuti'           => fn (Blueprint $table) => $table->text('alamat_selama_cuti')->nullable(),
            'telepon_cuti'                 => fn (Blueprint $table) => $table->string('telepon_cuti', 20)->nullable(),
            'perjalanan_berangkat_mulai'   => fn (Blueprint $table) => $table->date('perjalanan_berangkat_mulai')->nullable(),
            'perjalanan_berangkat_selesai' => fn (Blueprint $table) => $table->date('perjalanan_berangkat_selesai')->nullable(),
            'perjalanan_pulang_mulai'      => fn (Blueprint $table) => $table->date('perjalanan_pulang_mulai')->nullable(),
            'perjalanan_pulang_selesai'    => fn (Blueprint $table) => $table->date('perjalanan_pulang_selesai')->nullable(),
            'tanggal_surat'                => fn (Blueprint $table) => $table->date('tanggal_surat')->nullable(),
            'lokasi_pekerjaan'             => fn (Blueprint $table) => $table->string('lokasi_pekerjaan')->nullable(),
            'sppd_karyawan'                => fn (Blueprint $table) => $table->json('sppd_karyawan')->nullable(),
        ]);

        DB::transaction(function ()
        {
            $this->copyCutiDetails();
            $this->copySppdDetails();
        });

        foreach (self::OLD_DETAIL_TABLES as $oldTable)
        {
            Schema::dropIfExists($oldTable);
        }
    }

    public function down(): void
    {
        Schema::table('tb_absen', function (Blueprint $table)
        {
            $table->dropColumn(self::NEW_COLUMNS);
        });
    }

    private function copyCutiDetails()
    {
        if (! Schema::hasTable('tb_absen_cuti'))
        {
            return;
        }

        foreach (DB::table('tb_absen_cuti')->get() as $cuti)
        {
            DB::table('tb_absen')->where('id', $cuti->absen_id)->update([
                'jenis_cuti'                   => $cuti->jenis_cuti,
                'lama_cuti'                    => $cuti->lama_cuti,
                'band'                         => $cuti->band,
                'jabatan'                      => $cuti->jabatan,
                'unit_kerja'                   => $cuti->unit_kerja,
                'alamat_selama_cuti'           => $cuti->alamat_selama_cuti,
                'telepon_cuti'                 => $cuti->telepon_cuti,
                'perjalanan_berangkat_mulai'   => $cuti->perjalanan_berangkat_mulai,
                'perjalanan_berangkat_selesai' => $cuti->perjalanan_berangkat_selesai,
                'perjalanan_pulang_mulai'      => $cuti->perjalanan_pulang_mulai,
                'perjalanan_pulang_selesai'    => $cuti->perjalanan_pulang_selesai,
                'keterangan'                   => $cuti->alasan_cuti,
            ]);
        }
    }

    private function copySppdDetails()
    {
        if (! Schema::hasTable('tb_absen_sppd'))
        {
            return;
        }

        foreach (DB::table('tb_absen_sppd')->get() as $sppd)
        {
            $assignedEmployees = DB::table('tb_absen_sppd_karyawan')
                ->select('nik', 'nama', 'position', 'kota_asal', 'kota_tujuan')
                ->where('absen_id', $sppd->absen_id)
                ->orderBy('id')
                ->get();

            DB::table('tb_absen')->where('id', $sppd->absen_id)->update([
                'tanggal_surat'    => $sppd->tanggal_surat,
                'lokasi_pekerjaan' => $sppd->lokasi_pekerjaan,
                'keterangan'       => $sppd->perihal_pekerjaan,
                'sppd_karyawan'    => $assignedEmployees->toJson(),
            ]);
        }
    }
};
