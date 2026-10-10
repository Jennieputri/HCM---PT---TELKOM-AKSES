@if ($errors->any() || session("success"))
	<div
		id="flash-alert"
		hidden
		data-type="{{ $errors->any() ? "error" : "success" }}"
		data-title="{{ $errors->any() ? "Gagal" : "Berhasil" }}"
		data-messages="{{ json_encode($errors->any() ? $errors->all() : [session("success")]) }}"
	></div>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
	<script src="{{ asset("assets/js/flash-alert.js") }}"></script>
@endif
