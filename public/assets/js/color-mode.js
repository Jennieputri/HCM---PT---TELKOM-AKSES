(function applySavedColorMode()
{
	function readSavedTheme()
	{
		try
		{
			return localStorage.getItem('lte-theme');
		}
		catch
		{
			return null;
		}
	}

	const savedTheme = readSavedTheme();
	const isManualTheme = savedTheme === 'light' || savedTheme === 'dark';
	const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
	const activeTheme = isManualTheme ? savedTheme : systemTheme;

	document.documentElement.setAttribute('data-bs-theme', activeTheme);
	document.documentElement.setAttribute('data-lte-theme-resolved', '');
	document.documentElement.style.colorScheme = activeTheme;
})();
