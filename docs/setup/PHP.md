## PHP Installation Guide

> ❌ Well **XAMPP** also has PHP, but it ships with a pre-defined PHP version (an older one at that), you can check through `http://localhost:8080/dashboard/phpinfo.php`
> Port `8080` is the one XAMPP's using

This guide helps you verify whether PHP is already installed and, if not, we'll go through on how to install it. We'll cover **three approaches**:
- Manual Installation
- Chocolatey
- Herd

### Check if PHP is Already Installed

Before installing, confirm first whether PHP exists on your system. Open your terminal and run:

**PHP Check**
```bash
> php -v

# SAMPLE OUTPUT:
PHP 8.4.14 (cli) (built: Oct 22 2025 08:46:50) (NTS Visual C++ 2022 x64)
Copyright (c) The PHP Group
Zend Engine v4.4.14, Copyright (c) Zend Technologies
```

**Locate PHP**
```bash
> where php

# SAMPLE OUTPUT:
C:\path\to\php.exe
```

---
### Installation

#### OPTION A: Manual Installation
1. Go to the official [PHP downloads page](https://www.php.net/downloads).
2. Select the latest Windows binaries. Same as the image below:
   ![Screenshot of PHP download Windows example](https://github.com/radleigh123/ccsync-api/blob/master/docs/setup/CCSYNC-API-DOC-INSTALL-PHP-1.png)
3. Download the **ZIP file** (choose **VS17 x64 Thread Safe** or **VS17 x86 Thread Safe** for 32-bit system).
4. Extract the ZIP to a directory, e.g., `C:\php-8.4.14`.
5. Add the PHP folder to your system variables PATH:
	- Open *System Properties* -> *Environment Variables*.
	- Edit/Add the `Path` variable, `C:\php-8.4.14`
	> **📒** NOTE: If there are multiple PHP, the desired PHP version must be moved higher than the others, it will determine what PHP the `composer` will use, you can check through:
	> ```bash
	> composer diagnose
	> ```
6. Verify installation: `php -v`

<br>

#### OPTION B: Install through package manager (`Chocolatey`)
1. Go to the official [PHP downloads page](https://www.php.net/downloads).
2. Select the latest Chocolatey binaries, then there should be a given CLI command:
	```bash
	# Download and install Chocolatey.
	powershell -c "irm https://community.chocolatey.org/install.ps1|iex"
	
	# Download and install PHP.
	choco install php --version=8.4 -y
	```
3. Add the PHP folder to your system variables PATH.
4. Verify installation: `php -v`

<br>

#### OPTION C: Install through Herd
1. Visit [Herd](https://herd.laravel.com/windows).
2. Download and install Herd.
3. Run:
	```bash
	herd install php
	```
4. Verify installation: `php -v`

---
### PHP Configurations (`php.ini`)
> 📒 **NOTE:** The standard filenames are `php.ini-development` and `php.ini-production`. Just simply copy either of them to `php.ini`.

#### 1) Locate the PHP configuration file:
```bash
php --ini

# SAMPLE OUTPUT:
Configuration File (php.ini) Path:
Loaded Configuration File:         C:\tools\php84\php.ini
Scan for additional .ini files in: (none)
Additional .ini files parsed:      (none)
```
#### 2) Go to and open the `php.ini`, set extension directory:
```
; On windows:
extension_dir = "ext"
```

#### 3) Enable common extensions:
```
extension=curl
extension=mbstring
extension=openssl
extension=intl
extension=pdo_mysql
extension=fileinfo
extension=gd
extension=sodium
```

#### Verify extensions are loaded:
```bash
php -m

# SAMPLE OUTPUT:
[PHP Modules]
bcmath
calendar
Core
ctype
curl
date
dom
fileinfo
filter
gd
hash
iconv
json
libxml
mbstring
mysqli
mysqlnd
openssl
pcre
PDO
pdo_mysql
Phar
random
readline
Reflection
session
SimpleXML
sockets
sodium
SPL
standard
tokenizer
xml
xmlreader
xmlwriter
zip
zlib

[Zend Modules]

```
