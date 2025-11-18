# GitHub Setup Guide - Digital Wallet API

## Prerequisites
- Git installed on your system
- GitHub account created
- Code ready to push

---

## Step-by-Step Instructions

### Step 1: Initialize Git Repository (if not already done)

Open terminal/PowerShell in your project directory (`F:\Desktop\wallet-api`) and run:

```bash
git init
```

### Step 2: Check Git Status

```bash
git status
```

This shows which files will be added/committed.

### Step 3: Add All Files to Staging

```bash
git add .
```

**Note**: The `.gitignore` file I created will automatically exclude:
- `.env` (sensitive credentials)
- `vendor/` (dependencies - should be installed via Composer)
- `storage/logs/*.log` (log files)
- Other sensitive/temporary files

### Step 4: Create Initial Commit

```bash
git commit -m "Initial commit: Digital Wallet API with JWT auth, multi-currency support, fraud detection, and rate limiting"
```

### Step 5: Create GitHub Repository

1. Go to [GitHub.com](https://github.com)
2. Click the **"+"** icon in top right → **"New repository"**
3. Repository name: `wallet-api` (or your preferred name)
4. Description: "Digital Wallet API with JWT authentication, multi-currency support, fraud detection, and rate limiting"
5. Choose **Public** or **Private**
6. **DO NOT** initialize with README, .gitignore, or license (we already have files)
7. Click **"Create repository"**

### Step 6: Add Remote Repository

After creating the repository, GitHub will show you commands. Use the HTTPS URL:

```bash
git remote add origin https://github.com/YOUR_USERNAME/wallet-api.git
```

**Replace `YOUR_USERNAME` with your actual GitHub username.**

### Step 7: Rename Branch to Main (if needed)

```bash
git branch -M main
```

### Step 8: Push to GitHub

```bash
git push -u origin main
```

You'll be prompted for your GitHub username and password (or personal access token).

---

## Complete Command Sequence (Copy & Paste)

```bash
# Navigate to project directory
cd F:\Desktop\wallet-api

# Initialize git (if not done)
git init

# Check status
git status

# Add all files
git add .

# Commit
git commit -m "Initial commit: Digital Wallet API with JWT auth, multi-currency support, fraud detection, and rate limiting"

# Add remote (REPLACE YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/wallet-api.git

# Rename branch to main
git branch -M main

# Push to GitHub
git push -u origin main
```

---

## If You Get Authentication Errors

If `git push` asks for credentials and fails:

### Option 1: Use Personal Access Token (Recommended)

1. Go to GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Give it a name (e.g., "wallet-api")
4. Select scopes: **repo** (full control)
5. Click "Generate token"
6. **Copy the token** (you won't see it again!)
7. When pushing, use the token as password

### Option 2: Use GitHub CLI

```bash
# Install GitHub CLI, then:
gh auth login
```

### Option 3: Use SSH (More Secure)

1. Generate SSH key:
```bash
ssh-keygen -t ed25519 -C "your_email@example.com"
```

2. Add SSH key to GitHub:
   - Copy public key: `cat ~/.ssh/id_ed25519.pub`
   - GitHub → Settings → SSH and GPG keys → New SSH key
   - Paste and save

3. Change remote URL:
```bash
git remote set-url origin git@github.com:YOUR_USERNAME/wallet-api.git
```

---

## Future Updates

After making changes to your code:

```bash
# Check what changed
git status

# Add changed files
git add .

# Commit with descriptive message
git commit -m "Description of changes"

# Push to GitHub
git push
```

---

## Important Notes

1. **Never commit `.env` file** - It contains sensitive data (database passwords, JWT secrets)
2. **Create `.env.example`** - Template file with placeholder values (optional but recommended)
3. **README.md** - Update it with setup instructions for others
4. **License** - Consider adding a LICENSE file

---

## Create .env.example (Optional but Recommended)

Create a template file that others can copy:

```bash
# Copy .env to .env.example (remove actual values)
# Then commit .env.example
```

This helps other developers know what environment variables are needed.

---

## Verify Your Push

After pushing, visit:
```
https://github.com/YOUR_USERNAME/wallet-api
```

You should see all your files there!

---

## Troubleshooting

### "Repository not found" error
- Check your GitHub username is correct
- Verify repository exists on GitHub
- Check you have access (if private repo)

### "Authentication failed"
- Use Personal Access Token instead of password
- Or set up SSH keys

### "Branch 'main' does not exist"
- Use: `git push -u origin main` (the `-u` flag sets upstream)

### Want to remove a file from Git but keep locally?
```bash
git rm --cached filename
```

---

Good luck! 🚀

