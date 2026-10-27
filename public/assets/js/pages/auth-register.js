(function ()
{
    var registerForm = document.getElementById('register-form');
    var nikInput = document.getElementById('nik');
    var passwordInput = document.getElementById('password');

    function showInputError(input, message)
    {
        input.value = '';
        input.placeholder = message;
        input.classList.add('input-error');
        input.closest('.input-group-custom').classList.add('input-error');
    }

    function clearInputError(input, defaultPlaceholder)
    {
        if (! input.classList.contains('input-error'))
        {
            return;
        }

        input.placeholder = defaultPlaceholder;
        input.classList.remove('input-error');
        input.closest('.input-group-custom').classList.remove('input-error');
    }

    registerForm.addEventListener('submit', function (event)
    {
        var hasError = false;

        if (! /^[0-9]+$/.test(nikInput.value))
        {
            showInputError(nikInput, 'NIK harus berupa angka!');
            hasError = true;
        }

        if (passwordInput.value.length < 8)
        {
            showInputError(passwordInput, 'Password minimal 8 karakter!');
            hasError = true;
        }

        if (hasError)
        {
            event.preventDefault();
        }
    });

    nikInput.addEventListener('input', function ()
    {
        clearInputError(nikInput, 'Masukkan NIK');
    });

    passwordInput.addEventListener('input', function ()
    {
        clearInputError(passwordInput, 'Masukkan Password');
    });
})();
