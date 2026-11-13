var MAX_PHOTO_PREVIEW_BYTES = 5 * 1024 * 1024;

function filterMemberCards(keyword)
{
    var normalizedKeyword = keyword.toLowerCase().trim();

    document.querySelectorAll('.kartu-karyawan').forEach(function (memberCard)
    {
        var searchableText = (memberCard.dataset.search || '').toLowerCase();

        memberCard.style.display = searchableText.includes(normalizedKeyword) ? '' : 'none';
    });
}

function previewMemberPhoto(photoInput)
{
    var selectedFile = photoInput.files[0];

    if (!selectedFile)
    {
        return;
    }

    if (!selectedFile.type.startsWith('image/') || selectedFile.size > MAX_PHOTO_PREVIEW_BYTES)
    {
        showMessage('Pilih file gambar dengan ukuran maksimal 5 MB.', 'danger');
        photoInput.value = '';

        return;
    }

    var photoPreview = photoInput.closest('.kartu-karyawan').querySelector('.foto-preview');
    var fileReader = new FileReader();

    fileReader.onload = function (event)
    {
        photoPreview.src = event.target.result;
    };

    fileReader.readAsDataURL(selectedFile);
}

$(function ()
{
    $('#cari-struktur').on('input', function ()
    {
        filterMemberCards(this.value);
    });

    $('.foto-input').on('change', function ()
    {
        previewMemberPhoto(this);
    });
});
