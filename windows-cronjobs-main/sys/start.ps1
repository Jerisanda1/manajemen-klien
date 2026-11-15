# ==========================================
# Disable QuickEdit Mode
# > Credit: Krisz (https://stackoverflow.com/a/42792718/23135757)
# ==========================================
$QuickEditCodeSnippet = @" 
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Runtime.InteropServices;

public static class DisableConsoleQuickEdit
{
    const uint ENABLE_QUICK_EDIT = 0x0040;

    // STD_INPUT_HANDLE (DWORD): -10 is the standard input device.
    const int STD_INPUT_HANDLE = -10;

    [DllImport("kernel32.dll", SetLastError = true)]
    static extern IntPtr GetStdHandle(int nStdHandle);

    [DllImport("kernel32.dll")]
    static extern bool GetConsoleMode(IntPtr hConsoleHandle, out uint lpMode);

    [DllImport("kernel32.dll")]
    static extern bool SetConsoleMode(IntPtr hConsoleHandle, uint dwMode);

    public static bool SetQuickEdit(bool SetEnabled)
    {

        IntPtr consoleHandle = GetStdHandle(STD_INPUT_HANDLE);

        // get current console mode
        uint consoleMode;
        if (!GetConsoleMode(consoleHandle, out consoleMode))
        {
            // ERROR: Unable to get console mode.
            return false;
        }

        // Clear the quick edit bit in the mode flags
        if (SetEnabled)
        {
            consoleMode &= ~ENABLE_QUICK_EDIT;
        }
        else
        {
            consoleMode |= ENABLE_QUICK_EDIT;
        }

        // set the new mode
        if (!SetConsoleMode(consoleHandle, consoleMode))
        {
            // ERROR: Unable to set console mode
            return false;
        }

        return true;
    }
}
"@

Add-Type -TypeDefinition $QuickEditCodeSnippet -Language CSharp

function Set-QuickEdit() {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $false, HelpMessage = "This switch will disable Console QuickEdit option")]
        [switch]$DisableQuickEdit = $false
    )


    if ([DisableConsoleQuickEdit]::SetQuickEdit($DisableQuickEdit)) {
        Write-Output "QuickEdit settings has been updated."
    }
    else {
        Write-Output "Something went wrong."
    }
}

Set-QuickEdit -DisableQuickEdit *> $null
# ==========================================


# ==========================================
# Functions
# ==========================================
function Test-IsPhp {
    param (
        [string]$phpPath
    )

    # Check file exists
    if (-Not (Test-Path -Path $phpPath -PathType Leaf)) {
        return "PHP_PATH_NOT_EXISTS"
    }

    # Check .exe
    if ((Get-Item $phpPath).Extension -ne ".exe") {
        return "PHP_PATH_INVALID"
    }

    # Check PHP
    $output = & $phpPath -v 2>&1
    if ($output -match "PHP [0-9]+\.[0-9]+\.[0-9]+") {
        return "PHP_PATH_VALID";
    }
    else {
        return "PHP_PATH_INVALID"
    }
}
# ==========================================


# ==========================================
# Stop Program
# ==========================================
$exitHandler = {
    Write-Host ""
    Write-Host ""
    Write-Host ""
    Write-Host "=============================="
    Write-Host " windows-cronjobs stopping... "
    Write-Host "=============================="

    # Read configuration file
    $configFile = Join-Path (Get-Location) "windows-cronjobs.config"
    $config = @{}
    Get-Content $configFile | ForEach-Object {
        # Ignore empty lines or lines starting with '#' (comments)
        if ($_ -match '^\s*([^#]+?)\s*=\s*(.*)\s*$') {
            $key = $Matches[1].Trim()
            $value = $Matches[2].Trim()
            $config[$key] = $value
        }
    }

    # Define task name
    if ($null -ne $config["task_name"] -and $config["task_name"] -ne "") {
        $taskName = $config["task_name"]
    }
    else {
        $taskName = "windows-cronjobs"
    }

    # Delete task if exists
    if (Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue) {
        Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
        $systemConfig = Join-Path (Get-Location) "sys\system.config"
        Remove-Item $systemConfig
    }

    Write-Host ""
    Write-Host "windows-cronjob stopped." -ForegroundColor Yellow
    Write-Host ""
    
    Write-Host "Wait 5 seconds or press any key to exit..."
    for ($seconds = 5; $seconds -ge 0; $seconds--) {
        if ([Console]::KeyAvailable) {
            $null = [Console]::ReadKey($true)
            exit
        }
        
        Write-Host "Exiting in $seconds seconds..."
        Start-Sleep -Seconds 1
    
        if ($seconds -eq 0) {
            Write-Host "Exiting..."
        }
    }
    
}

Register-EngineEvent -SourceIdentifier PowerShell.Exiting -Action $exitHandler  *> $null
# ==========================================


# ==========================================
# Start Program
# ==========================================
Write-Host "-------------------------------------------------------"
Write-Host "| WINDOWS CRONJOBS v0.2.2                             |"
Write-Host "|                                                     |"
Write-Host "| - (c) Putu Ardi Dharmayasa                          |"
Write-Host "| - https://github.com/putuardiworks/windows-cronjobs |"
Write-Host "-------------------------------------------------------"
Write-Host ""
Write-Host "=============================="
Write-Host " windows-cronjobs starting... "
Write-Host "=============================="

# Read configuration file
$configFile = Join-Path (Get-Location) "windows-cronjobs.config"
$config = @{}
Get-Content $configFile | ForEach-Object {
    # Ignore empty lines or lines starting with '#' (comments)
    if ($_ -match '^\s*([^#]+?)\s*=\s*(.*)\s*$') {
        $key = $Matches[1].Trim()
        $value = $Matches[2].Trim()
        $config[$key] = $value
    }
}

# Get PHP path
if ($null -ne $config["php_path"] -and $config["php_path"] -ne "") {
    $phpPath = $config["php_path"]
}
else {
    Write-Host ""
    Write-Host "Failed to start windows-cronjobs." -ForegroundColor Red
    Write-Host "PHP_PATH_NOT_FOUND: Please set php_path in windows-cronjobs.config." -ForegroundColor Red
    Write-Host ""

    Write-Host "Press any key to exit..."
    while ($true) {
        if ([Console]::KeyAvailable) {
            $null = [Console]::ReadKey($true)
            exit
        }
    }
}

# Check PHP
if ((Test-IsPhp $phpPath) -eq "PHP_PATH_NOT_EXISTS") {
    Write-Host ""
    Write-Host "Failed to start windows-cronjobs." -ForegroundColor Red
    Write-Host "PHP_PATH_NOT_EXISTS: Please set php_path to the correct path of the PHP executable." -ForegroundColor Red
    Write-Host ""

    Write-Host "Press any key to exit..."
    while ($true) {
        if ([Console]::KeyAvailable) {
            $null = [Console]::ReadKey($true)
            exit
        }
    }
}
elseif ((Test-IsPhp $phpPath) -eq "PHP_PATH_INVALID") {
    Write-Host ""
    Write-Host "Failed to start windows-cronjobs." -ForegroundColor Red
    Write-Host "PHP_PATH_INVALID: Please set php_path to the correct path of the PHP executable." -ForegroundColor Red
    Write-Host ""

    Write-Host "Press any key to exit..."
    while ($true) {
        if ([Console]::KeyAvailable) {
            $null = [Console]::ReadKey($true)
            exit
        }
    }
}

# Define task name
if ($null -ne $config["task_name"] -and $config["task_name"] -ne "") {
    $taskName = $config["task_name"]
}
else {
    $taskName = "windows-cronjobs"
}

# Delete task if exists
if (Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue) {
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

# Define the start time for the trigger (next minute with 0 seconds)
$startTime = (Get-Date).AddMinutes(1).AddSeconds( - $(Get-Date).Second)
$startTimeFormatted = $startTime.ToString("HH:mm")

# Create task
$windowsCronjobs = Join-Path (Get-Location) "sys\cronjobs.php"
$action = New-ScheduledTaskAction -Execute $phpPath -Argument "-f $windowsCronjobs"
$trigger = New-ScheduledTaskTrigger -Once -At $startTime -RepetitionInterval (New-TimeSpan -Minutes 1)
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries
Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -User "SYSTEM" -RunLevel Highest *>$null

Write-Host ""
Write-Host "$taskName started successfully." -ForegroundColor Green
Write-Host "Your cronjobs will start running at $startTimeFormatted." -ForegroundColor Green
Write-Host ""

Write-Host "Press Ctrl+C to stop windows-cronjobs..."
while ($true) {
    Start-Sleep -Seconds 1    
}
