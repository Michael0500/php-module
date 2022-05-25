@REM SuperAdmin
@echo off

for /F "delims=;" %%d in ('dir /B /AD %1') do (
	move %1\%%d\*.pdf %1\%%d.pdf
)
