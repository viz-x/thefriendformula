# **TheFriendFormula: A Simple Social Discovery App**
**Version:** PHP  
**Date:** 1-1-26

## **What This Is**
A basic social app where users connect using Instagram usernames. Users can see profiles nearby and admins can manage everything.

## **How It Works**

### **Tech Used**
- **Backend:** PHP
- **Data Storage:** JSON files (no database needed)

### **Files in the Project**
```
thefriendformula/
├── index.php              # Main file that runs everything
├── config.php             # Settings and passwords
├── functions.php          # Helper functions
├── admin.php              # Admin panel
├── data/
│   └── users.json         # Where user data is stored
└── uploads/               # Where profile pictures go
```

## **Main Features**

### **For Users:**
1. **Sign Up:** Enter your Instagram username and upload a photo
2. **Location:** Gets a fake nearby location for testing (for now only)
3. **Browse:** See other users' profiles
4. **Settings:** Edit your profile anytime

### **For Admins:**
- Turn approval on/off for new users
- Ban or delete users
- See basic stats

### **Important Rules:**
- Must agree to terms before joining
- Can delete your profile anytime
- Only collects what's needed (username, photo, location)

## **Security Basics**
- Filters user inputs
- Checks file uploads are images
- Password protects admin area
- Uses PHP sessions to track logins

## **How to Install**

1. **Requirements:**
   - PHP 7.4 or newer
   - Write permissions for `data/` and `uploads/` folders

2. **Setup:**
   ```bash
   # Create the app folder and set permissions
   mkdir -p data uploads
   chmod 755 data uploads
   ```

3. **Run It:**
   ```bash
   # Using PHP's built-in server:
   php -S localhost:8000
   ```
   Then visit `http://localhost:8000` in your browser

## **Limitations**
- Works best with less than 10,000 users
- Basic security - not for sensitive data
- Simple features only (no messaging yet)

## **Future Updates**
- mobile app version

## **IMPORTANT**
-Regarding Instagram Presence: TheFriendFormula currently utilizes Instagram usernames for identity but does not yet have an official Instagram account or page. Should an official presence become necessary for verification, announcements, or community engagement in the future, it will be created and announced exclusively through official project channels. For security and brand integrity purposes, users and third parties are strictly prohibited from creating any social media pages, accounts, groups, or content using thefriendformula name, branding, or associated materials. All official communications and representations will originate only from the authorized project team.
