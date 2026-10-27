document.querySelectorAll('.password-button').forEach(function (toggleButton)
{
    toggleButton.addEventListener('click', function ()
    {
        var passwordInput = toggleButton.parentElement.querySelector('input');
        var eyeIcon = toggleButton.querySelector('i');
        var isPasswordHidden = passwordInput.type === 'password';

        passwordInput.type = isPasswordHidden ? 'text' : 'password';
        eyeIcon.classList.toggle('bi-eye', ! isPasswordHidden);
        eyeIcon.classList.toggle('bi-eye-slash', isPasswordHidden);
        toggleButton.setAttribute('aria-label', isPasswordHidden ? 'Sembunyikan password' : 'Tampilkan password');
    });
});
