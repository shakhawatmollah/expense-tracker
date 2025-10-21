# Quick Fix: Clear Browser Storage

## If you see a JSON parse error:

### **Option 1: Quick Reset (Recommended)**

1. Press `F12` to open Developer Tools
2. Click the **Console** tab
3. Type this command and press Enter:
   ```javascript
   localStorage.clear()
   ```
4. Press `F5` to refresh the page
5. You'll need to log in again

### **Option 2: Clear Specific Data**

1. Press `F12` to open Developer Tools
2. Click **Application** tab (Chrome) or **Storage** tab (Firefox)
3. Click **Local Storage** in the left sidebar
4. Click your site URL
5. Find and delete these items:
   - `user`
   - `token`
6. Refresh the page (F5)

### **Option 3: Clear Browser Cache**

1. Press `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
2. Select **"Cached images and files"** and **"Cookies and site data"**
3. Click **Clear data**
4. Refresh the page

---

## This error happens when:
- Browser data gets corrupted
- You cleared storage partially
- A browser extension interfered
- The app was closed during a save operation

## After clearing:
✅ Error will be gone  
✅ App will load normally  
✅ You can log in again  
✅ Your account data is safe (stored on server)

---

**Note:** This only clears your local browser data. Your account, expenses, budgets, and all data on the server are completely safe!
