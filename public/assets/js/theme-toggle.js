(function setupThemeToggle()
{
	const themeToggleButton = document.getElementById('theme-toggle');

	if (!themeToggleButton)
	{
		return;
	}

	const rootElement = document.documentElement;

	function readActiveTheme()
	{
		return rootElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
	}

	function saveTheme(theme)
	{
		try
		{
			localStorage.setItem('lte-theme', theme);
			return true;
		}
		catch
		{
			return false;
		}
	}

	function updateButtonLabel(theme)
	{
		const nextActionLabel = theme === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap';

		themeToggleButton.setAttribute('aria-label', nextActionLabel);
		themeToggleButton.setAttribute('title', nextActionLabel);
	}

	function applyTheme(theme)
	{
		rootElement.setAttribute('data-bs-theme', theme);
		rootElement.style.colorScheme = theme;
		updateButtonLabel(theme);
	}

	function toggleTheme()
	{
		const nextTheme = readActiveTheme() === 'dark' ? 'light' : 'dark';

		saveTheme(nextTheme);
		applyTheme(nextTheme);
	}

	themeToggleButton.addEventListener('click', toggleTheme);
	updateButtonLabel(readActiveTheme());
})();
