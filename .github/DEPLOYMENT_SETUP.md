# cPanel Deployment Setup

This guide explains how to set up automatic deployment to your cPanel shared hosting via GitHub Actions.

## Required GitHub Secrets

Go to your GitHub repository > Settings > Secrets and variables > Actions, and add the following secrets:

### 1. FTP_SERVER
Your cPanel FTP server address. Usually:
- `ftp.yourdomain.com` or
- `yourdomain.com` or
- The IP address from your cPanel

### 2. FTP_USERNAME
Your cPanel FTP username. Usually:
- `username@yourdomain.com` or
- Your cPanel username

### 3. FTP_PASSWORD
Your cPanel/FTP password.

### 4. FTP_SERVER_DIR
The directory on your server where files should be deployed:
- For main domain: `./public_html/` or `/public_html/`
- For subdomain: `./public_html/subdomain/` or `/subdomain.yourdomain.com/`
- For addon domain: `./public_html/addon-domain/`

## Finding Your FTP Credentials in cPanel

1. Log in to your cPanel
2. Go to **Files** > **FTP Accounts**
3. Look for your existing FTP account or create a new one
4. The FTP server is usually shown in the **Configure FTP Client** section

## How Deployment Works

1. When you push to `main` or `master` branch, the workflow triggers
2. GitHub Actions builds your React application
3. The built files and API are uploaded via FTP to your cPanel
4. Your site is automatically updated

## Manual Deployment

You can also trigger deployment manually:
1. Go to your repository > Actions
2. Select "Deploy to cPanel" workflow
3. Click "Run workflow"

## First-Time Setup on cPanel

After first deployment, you may need to:

1. **Set file permissions** for the API data folder:
   ```
   chmod 755 api/data
   chmod 644 api/data/*.json
   ```

2. **Create the data files** if they don't exist:
   - `api/data/users.json`
   - `api/data/assets.json`
   - `api/data/transactions.json`
   - `api/data/kits.json`
   - `api/data/reservations.json`
   - `api/data/audit_log.json`

3. **Configure email settings** in `api/config.php` if needed

## Troubleshooting

### Deployment fails with authentication error
- Double-check your FTP credentials
- Ensure the FTP account has write access

### Site shows blank page after deployment
- Check that `.htaccess` was deployed correctly
- Verify PHP is working on your hosting

### API returns 500 error
- Check file permissions on `api/data/` folder
- Ensure JSON data files exist and are valid
