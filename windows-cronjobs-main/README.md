# Windows Cronjobs

Windows Cronjobs is a tool designed to bring the functionality of cPanel cron jobs to Windows so that cron jobs can be tested locally.

Official GitHub Link: [https://github.com/putuardiworks/windows-cronjobs](https://github.com/putuardiworks/windows-cronjobs)

> for Indonesians: README ini juga tersedia dalam [Bahasa Indonesia](https://github.com/putuardiworks/windows-cronjobs/blob/main/README_INDONESIAN.md).

## Motivation

When developing a PHP project that requires cron jobs, I usually test them directly in cPanel. This process feels somewhat cumbersome and inefficient for local development, especially if you don't have cPanel hosting. Additionally, it can be difficult to know which scripts have been set as cron jobs and what their configurations are in cPanel.

Windows Cronjobs was developed to address these issues. By providing functionality similar to cPanel cron jobs, this tool allows us to test cron jobs locally on Windows and ensure that cron jobs configurations are well-documented within the code.

## Setup

1. **Download and Extract:**
   - Download the repository as a `.zip` file.
   - Extract the contents.

2. **Copy Configuration File:**
   - Make a copy of `windows-cronjobs.config.example`.
   - Rename the copied file to `windows-cronjobs.config`.

3. **Configuration:**
   - Open `windows-cronjobs.config`.
   - Set `php_path` to the location of your `php.exe`. (required)
   - Set the `timezone`. (optional)

4. **Add Cron Jobs:**
   - Edit `cronjobs_list.php`.
   - Add your cron jobs as shown in the example.

## Usage

- **Start Cron Jobs:**
  - Double-click `windows-cronjobs.bat` to start the cron jobs.

- **Stop Cron Jobs:**
  - Press `Ctrl+C` in the PowerShell window that was opened when starting the cron jobs.

## Features

Windows Cronjobs v0.2.2 currently only supports common cron jobs settings found in cPanel:

- **Once Per Minute:** `* * * * *`
- **Once Per Five Minutes:** `*/5 * * * *`
- **Twice Per Hour:** `0,30 * * * *`
- **Once Per Hour:** `0 * * * *`
- **Twice Per Day:** `0 0,12 * * *`
- **Once Per Day:** `0 0 * * *`
- **Once Per Week:** `0 0 * * 0`
- **On the 1st and 15th of the Month:** `0 0 1,15 * *`
- **Once Per Month:** `0 0 1 * *`
- **Once Per Year:** `0 0 1 1 *`

## TODO

Planned enhancements for future versions:

- Support for specific time values.
- Support for time ranges.
- Support for lists of values.
- Support for intervals.

## License

This project is licensed under the MIT License. See the `LICENSE` file for more details.
