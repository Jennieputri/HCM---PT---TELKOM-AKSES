<div
	class="kartu-karyawan"
	data-search="{{ strtolower($member->nama . " " . $member->nama_jabatan . " " . $member->sub_unit) }}"
>
	<div class="kartu-isi">
		<label class="foto-upload">
			<img
				src="{{ asset("assets/images/defaultprofile.png") }}"
				alt="Foto profil {{ $member->nama }}"
				class="foto-preview"
			/>
			<input type="file" class="foto-input" accept="image/*" aria-label="Pratinjau foto {{ $member->nama }}" />
		</label>

		<div class="karyawan-nama">{{ $member->nama }}</div>
		<div class="karyawan-jabatan">{{ $member->nama_jabatan }}</div>
		<p class="karyawan-unit">{{ $member->sub_unit ?: "Sub unit belum diisi" }}</p>
	</div>
</div>
