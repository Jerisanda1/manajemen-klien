@echo off
powershell -Command "Start-Process -Verb RunAs powershell '-ExecutionPolicy Bypass -Command cd %~dp0; & .\sys\start.ps1'"
