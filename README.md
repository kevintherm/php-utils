# PHP Utils

Set of tools that I personally keep with me, mainly for deploying onto shared hosting or other purpose.

## Browser Composer Toolkit (Standalone)

A single-file PHP script to manage Composer and project deployments through the browser, ideal for shared hosting without SSH access.

### Features
- **Browser-based Installer**: One-click local `composer.phar` setup with signature verification.
- **Interactive Browser UI**: Create projects, require packages, and install dependencies via a web form.
- **Shared Hosting Friendly**: Requires only PHP process execution (`proc_open` or fallback).
- **Standalone**: All logic is contained in a single file ([composer-toolkit.php](composer-toolkit.php)).

### Prerequisites
- PHP 7.4+
- `allow_url_fopen` enabled (to download Composer)
- `proc_open` or `shell_exec` functions enabled on hosting

### Installation & Setup

1. **Upload File**: Transfer [composer-toolkit.php](composer-toolkit.php) to your shared hosting account.
2. **Access Tool**: Navigate to `http://your-site.com/composer-toolkit.php`.
3. **Run Installer**: Click **Download & Install Composer** on the page.
4. **Use Toolkit**: Start creating projects or managing dependencies immediately.

### Security Warning
- **No integrated security**: This standalone version does not include authentication. Anyone who knows the URL can perform Composer operations on your server.
- **Protect the URL**: Rename the file to something unique (e.g., `my-custom-tool-8234.php`) and ensure it's not indexed by search engines.
- **Delete scripts after use**: Delete the file once your deployment tasks are completed to prevent unauthorized access.
- **Always use HTTPS**: To protect the form data.

### Available Actions
- **Create Project**: Equivalent to `composer create-project`.
- **Install**: Equivalent to `composer install`.
- **Require**: Equivalent to `composer require`.
- **Check Version**: Equivalent to `composer --version`.
