param
(
    [Parameter(Mandatory = $true)][string]$InputPath,
    [Parameter(Mandatory = $true)][string]$OutputPath
)

$ErrorActionPreference = 'Stop'
$wordExportFormatPdf = 17
$wordDoNotSaveChanges = 0

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

try
{
    $document = $word.Documents.Open($InputPath, $false, $true, $false)

    try
    {
        $document.ExportAsFixedFormat($OutputPath, $wordExportFormatPdf)
    }
    finally
    {
        $document.Close($wordDoNotSaveChanges)
    }
}
finally
{
    $word.Quit()
    [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($word)
}
