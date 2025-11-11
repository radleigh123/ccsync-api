## Composer Installation Guide

### Check if **Composer** is Already Installed
```bash
composer -V

# SAMPLE OUTPUT:
Composer version 2.8.12 2025-09-19 13:41:59
PHP version 8.4.14 (C:\tools\php84\php.exe)
Run the "diagnose" command to get more detailed diagnostics output.
```

---

### Installation (Windows)
1. Go to the [Composer download page](https://getcomposer.org/download).
2. Download the [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).
3. Run the installer:
	- It will detect your PHP path (e.g., `C:\php-8.4.14\php.exe`).
	- Add **Composer** to PATH automatically.
4. Verify:
	```bash
	composer -V
	composer diagnose
	```
