<template>
  <div class="settings-overlay" @click="$emit('close')">
    <div class="settings-container" @click.stop>
      <!-- Settings Header -->
      <div class="settings-header">
        <button @click="$emit('close')" class="back-btn">
          <i class="fas fa-arrow-left"></i>
        </button>
        <div>
          <h1 class="settings-title">
            <i class="fas fa-cog"></i>
            Settings
          </h1>
          <p class="settings-subtitle">Manage your account and preferences</p>
        </div>
      </div>

      <div class="settings-content">
        <!-- Sidebar Navigation -->
        <div class="settings-sidebar">
          <nav class="settings-nav">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="nav-item"
              :class="{ active: activeTab === tab.id }"
            >
              <i :class="tab.icon"></i>
              <span>{{ tab.label }}</span>
            </button>
          </nav>
        </div>

        <!-- Settings Panel -->
        <div class="settings-panel">
          <!-- Profile Settings -->
          <div v-if="activeTab === 'profile'" class="settings-section">
            <h2 class="section-title">Profile Information</h2>

            <div class="form-group">
              <label>Full Name</label>
              <input
                v-model="settingsStore.profile.name"
                type="text"
                placeholder="Enter your name"
                class="form-control"
              />
            </div>

            <div class="form-group">
              <label>Email Address</label>
              <input
                v-model="settingsStore.profile.email"
                type="email"
                placeholder="your.email@example.com"
                class="form-control"
              />
            </div>

            <div class="form-group">
              <label>Phone Number</label>
              <input
                v-model="settingsStore.profile.phone"
                type="tel"
                placeholder="+1 (555) 123-4567"
                class="form-control"
              />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Currency</label>
                <select v-model="settingsStore.profile.currency" class="form-control">
                  <option value="USD">USD - US Dollar</option>
                  <option value="EUR">EUR - Euro</option>
                  <option value="GBP">GBP - British Pound</option>
                  <option value="JPY">JPY - Japanese Yen</option>
                  <option value="INR">INR - Indian Rupee</option>
                  <option value="BDT">BDT - Bangladeshi Taka</option>
                </select>
              </div>

              <div class="form-group">
                <label>Date Format</label>
                <select v-model="settingsStore.profile.dateFormat" class="form-control">
                  <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                  <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                  <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                </select>
              </div>
            </div>

            <div class="form-actions">
              <button @click="saveProfile" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Changes
              </button>
            </div>
          </div>

          <!-- Notifications -->
          <div v-if="activeTab === 'notifications'" class="settings-section">
            <h2 class="section-title">Notification Preferences</h2>

            <div class="notification-category">
              <h3 class="category-title">Email Notifications</h3>
              <div class="toggle-list">
                <div class="toggle-item">
                  <div class="toggle-info">
                    <span class="toggle-label">Budget Alerts</span>
                    <span class="toggle-description">Get notified when approaching budget limits</span>
                  </div>
                  <label class="toggle-switch">
                    <input v-model="settingsStore.notifications.email.budgetAlerts" type="checkbox" />
                    <span class="slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-info">
                    <span class="toggle-label">Weekly Reports</span>
                    <span class="toggle-description">Receive weekly spending summaries</span>
                  </div>
                  <label class="toggle-switch">
                    <input v-model="settingsStore.notifications.email.weeklyReports" type="checkbox" />
                    <span class="slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-info">
                    <span class="toggle-label">Monthly Reports</span>
                    <span class="toggle-description">Get detailed monthly financial reports</span>
                  </div>
                  <label class="toggle-switch">
                    <input v-model="settingsStore.notifications.email.monthlyReports" type="checkbox" />
                    <span class="slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-info">
                    <span class="toggle-label">Goal Achievements</span>
                    <span class="toggle-description">Celebrate when you reach savings goals</span>
                  </div>
                  <label class="toggle-switch">
                    <input v-model="settingsStore.notifications.email.goalAchievements" type="checkbox" />
                    <span class="slider"></span>
                  </label>
                </div>
              </div>
            </div>

            <div class="notification-category">
              <h3 class="category-title">Push Notifications</h3>
              <div class="toggle-list">
                <div class="toggle-item">
                  <div class="toggle-info">
                    <span class="toggle-label">Budget Alerts</span>
                    <span class="toggle-description">Real-time budget notifications</span>
                  </div>
                  <label class="toggle-switch">
                    <input v-model="settingsStore.notifications.push.budgetAlerts" type="checkbox" />
                    <span class="slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-info">
                    <span class="toggle-label">Expense Reminders</span>
                    <span class="toggle-description">Reminders to log daily expenses</span>
                  </div>
                  <label class="toggle-switch">
                    <input v-model="settingsStore.notifications.push.expenseReminders" type="checkbox" />
                    <span class="slider"></span>
                  </label>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button @click="saveNotifications" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Preferences
              </button>
            </div>
          </div>

          <!-- Privacy & Security -->
          <div v-if="activeTab === 'security'" class="settings-section">
            <h2 class="section-title">Privacy & Security</h2>

            <div class="toggle-list">
              <div class="toggle-item">
                <div class="toggle-info">
                  <span class="toggle-label">Show Amounts on Dashboard</span>
                  <span class="toggle-description">Display monetary values on main screen</span>
                </div>
                <label class="toggle-switch">
                  <input v-model="settingsStore.privacy.showAmountsOnDashboard" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>

              <div class="toggle-item">
                <div class="toggle-info">
                  <span class="toggle-label">Two-Factor Authentication</span>
                  <span class="toggle-description">Add extra security to your account</span>
                </div>
                <label class="toggle-switch">
                  <input v-model="settingsStore.privacy.twoFactorAuth" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>

              <div class="toggle-item">
                <div class="toggle-info">
                  <span class="toggle-label">Data Sharing</span>
                  <span class="toggle-description">Share anonymous usage data to improve the app</span>
                </div>
                <label class="toggle-switch">
                  <input v-model="settingsStore.privacy.dataSharing" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>
            </div>

            <div class="form-group">
              <label>Session Timeout (minutes)</label>
              <input
                v-model.number="settingsStore.privacy.sessionTimeout"
                type="number"
                min="5"
                max="120"
                class="form-control"
              />
              <span class="form-hint">Auto logout after inactivity</span>
            </div>

            <div class="security-actions">
              <h3 class="category-title">Account Security</h3>
              <button @click="showChangePassword = true" class="btn btn-secondary">
                <i class="fas fa-key"></i>
                Change Password
              </button>
            </div>

            <div class="form-actions">
              <button @click="savePrivacy" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Settings
              </button>
            </div>
          </div>

          <!-- Display & Appearance -->
          <div v-if="activeTab === 'display'" class="settings-section">
            <h2 class="section-title">Display & Appearance</h2>

            <div class="form-group">
              <label>Theme</label>
              <div class="theme-selector">
                <button
                  @click="settingsStore.display.theme = 'light'"
                  class="theme-option"
                  :class="{ active: settingsStore.display.theme === 'light' }"
                >
                  <i class="fas fa-sun"></i>
                  <span>Light</span>
                </button>
                <button
                  @click="settingsStore.display.theme = 'dark'"
                  class="theme-option"
                  :class="{ active: settingsStore.display.theme === 'dark' }"
                >
                  <i class="fas fa-moon"></i>
                  <span>Dark</span>
                </button>
                <button
                  @click="settingsStore.display.theme = 'auto'"
                  class="theme-option"
                  :class="{ active: settingsStore.display.theme === 'auto' }"
                >
                  <i class="fas fa-adjust"></i>
                  <span>Auto</span>
                </button>
              </div>
            </div>

            <div class="toggle-list">
              <div class="toggle-item">
                <div class="toggle-info">
                  <span class="toggle-label">Compact Mode</span>
                  <span class="toggle-description">Use smaller spacing and fonts</span>
                </div>
                <label class="toggle-switch">
                  <input v-model="settingsStore.display.compactMode" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>

              <div class="toggle-item">
                <div class="toggle-info">
                  <span class="toggle-label">Show Category Icons</span>
                  <span class="toggle-description">Display icons next to category names</span>
                </div>
                <label class="toggle-switch">
                  <input v-model="settingsStore.display.showCategoryIcons" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>

              <div class="toggle-item">
                <div class="toggle-info">
                  <span class="toggle-label">Show Charts</span>
                  <span class="toggle-description">Display visual charts and graphs</span>
                </div>
                <label class="toggle-switch">
                  <input v-model="settingsStore.display.showCharts" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>
            </div>

            <div class="form-group">
              <label>Default View</label>
              <select v-model="settingsStore.display.defaultView" class="form-control">
                <option value="dashboard">Dashboard</option>
                <option value="expenses">Expenses</option>
                <option value="budgets">Budgets</option>
                <option value="analytics">Analytics</option>
              </select>
            </div>

            <div class="form-actions">
              <button @click="saveDisplay" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Apply Changes
              </button>
            </div>
          </div>

          <!-- Budget Alerts -->
          <div v-if="activeTab === 'alerts'" class="settings-section">
            <h2 class="section-title">Budget Alert Settings</h2>

            <div class="form-group">
              <label>Warning Threshold (%)</label>
              <div class="slider-input">
                <input
                  v-model.number="settingsStore.budgetAlerts.warningThreshold"
                  type="range"
                  min="0"
                  max="100"
                  class="range-slider"
                />
                <span class="slider-value">{{ settingsStore.budgetAlerts.warningThreshold }}%</span>
              </div>
              <span class="form-hint">Alert when budget reaches this percentage</span>
            </div>

            <div class="form-group">
              <label>Critical Threshold (%)</label>
              <div class="slider-input">
                <input
                  v-model.number="settingsStore.budgetAlerts.criticalThreshold"
                  type="range"
                  min="0"
                  max="100"
                  class="range-slider"
                />
                <span class="slider-value">{{ settingsStore.budgetAlerts.criticalThreshold }}%</span>
              </div>
              <span class="form-hint">Critical alert when reaching this level</span>
            </div>

            <div class="toggle-list">
              <div class="toggle-item">
                <div class="toggle-info">
                  <span class="toggle-label">Sound Alerts</span>
                  <span class="toggle-description">Play sound for budget notifications</span>
                </div>
                <label class="toggle-switch">
                  <input v-model="settingsStore.budgetAlerts.enableSoundAlerts" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>
            </div>

            <div class="form-group">
              <label>Alert Frequency</label>
              <select v-model="settingsStore.budgetAlerts.alertFrequency" class="form-control">
                <option value="once">Once per threshold</option>
                <option value="daily">Daily reminders</option>
                <option value="always">Every time exceeded</option>
              </select>
            </div>

            <div class="form-actions">
              <button @click="saveBudgetAlerts" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Settings
              </button>
            </div>
          </div>

          <!-- Danger Zone -->
          <div v-if="activeTab === 'account'" class="settings-section">
            <h2 class="section-title">Account Management</h2>

            <div class="info-card">
              <div class="info-icon">
                <i class="fas fa-download"></i>
              </div>
              <div class="info-content">
                <h4>Export Your Data</h4>
                <p>Download all your expenses, budgets, and settings</p>
                <button @click="exportAllData" class="btn btn-secondary">
                  <i class="fas fa-file-export"></i>
                  Export Data
                </button>
              </div>
            </div>

            <div class="danger-zone">
              <h3 class="danger-title">
                <i class="fas fa-exclamation-triangle"></i>
                Danger Zone
              </h3>

              <div class="danger-card">
                <div class="danger-content">
                  <h4>Delete Account</h4>
                  <p>Permanently delete your account and all associated data. This action cannot be undone.</p>
                </div>
                <button @click="showDeleteAccount = true" class="btn btn-danger">
                  <i class="fas fa-trash-alt"></i>
                  Delete Account
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Password Modal -->
    <div v-if="showChangePassword" class="modal-overlay" @click="showChangePassword = false">
      <div class="modal-content" @click.stop>
        <h3 class="modal-title">Change Password</h3>
        <div class="form-group">
          <label>Current Password</label>
          <input
            v-model="passwordForm.current"
            type="password"
            placeholder="Enter current password"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label>New Password</label>
          <input v-model="passwordForm.new" type="password" placeholder="Enter new password" class="form-control" />
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input
            v-model="passwordForm.confirm"
            type="password"
            placeholder="Confirm new password"
            class="form-control"
          />
        </div>
        <div class="modal-actions">
          <button @click="showChangePassword = false" class="btn btn-secondary">Cancel</button>
          <button @click="changePassword" class="btn btn-primary">Change Password</button>
        </div>
      </div>
    </div>

    <!-- Delete Account Confirmation -->
    <div v-if="showDeleteAccount" class="modal-overlay" @click="showDeleteAccount = false">
      <div class="modal-content danger-modal" @click.stop>
        <div class="modal-icon danger">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="modal-title">Delete Account?</h3>
        <p class="modal-message">
          This will permanently delete your account and all data including expenses, budgets, and categories. This
          action cannot be undone.
        </p>
        <div class="form-group">
          <label>Enter your password to confirm</label>
          <input v-model="deletePassword" type="password" placeholder="Password" class="form-control" />
        </div>
        <div class="modal-actions">
          <button @click="showDeleteAccount = false" class="btn btn-secondary">Cancel</button>
          <button @click="confirmDeleteAccount" class="btn btn-danger">Delete My Account</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue'
  import { useSettingsStore } from '@/stores/settings'
  import { useRouter } from 'vue-router'

  defineEmits(['close'])

  const router = useRouter()
  const settingsStore = useSettingsStore()

  const activeTab = ref('profile')
  const showChangePassword = ref(false)
  const showDeleteAccount = ref(false)
  const deletePassword = ref('')

  const passwordForm = ref({
    current: '',
    new: '',
    confirm: ''
  })

  const tabs = [
    { id: 'profile', label: 'Profile', icon: 'fas fa-user' },
    { id: 'notifications', label: 'Notifications', icon: 'fas fa-bell' },
    { id: 'security', label: 'Security', icon: 'fas fa-shield-alt' },
    { id: 'display', label: 'Display', icon: 'fas fa-palette' },
    { id: 'alerts', label: 'Budget Alerts', icon: 'fas fa-exclamation-circle' },
    { id: 'account', label: 'Account', icon: 'fas fa-user-cog' }
  ]

  const saveProfile = async () => {
    const result = await settingsStore.updateProfile(settingsStore.profile)
    showNotification(result.success ? 'success' : 'error', result.message)
  }

  const saveNotifications = async () => {
    const result = await settingsStore.updateNotifications(settingsStore.notifications)
    showNotification(result.success ? 'success' : 'error', result.message)
  }

  const savePrivacy = async () => {
    const result = await settingsStore.updatePrivacy(settingsStore.privacy)
    showNotification(result.success ? 'success' : 'error', result.message)
  }

  const saveDisplay = async () => {
    const result = await settingsStore.updateDisplay(settingsStore.display)
    showNotification(result.success ? 'success' : 'error', result.message)
  }

  const saveBudgetAlerts = async () => {
    const result = await settingsStore.updateBudgetAlerts(settingsStore.budgetAlerts)
    showNotification(result.success ? 'success' : 'error', result.message)
  }

  const changePassword = async () => {
    if (passwordForm.value.new !== passwordForm.value.confirm) {
      showNotification('error', 'Passwords do not match')
      return
    }

    const result = await settingsStore.changePassword(passwordForm.value.current, passwordForm.value.new)

    if (result.success) {
      showChangePassword.value = false
      passwordForm.value = { current: '', new: '', confirm: '' }
    }

    showNotification(result.success ? 'success' : 'error', result.message)
  }

  const exportAllData = async () => {
    const result = await settingsStore.exportData('json')

    if (result.success) {
      const blob = new Blob([JSON.stringify(result.data, null, 2)], { type: 'application/json' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `expense-tracker-data-${new Date().toISOString().split('T')[0]}.json`
      a.click()
      URL.revokeObjectURL(url)
      showNotification('success', 'Data exported successfully')
    } else {
      showNotification('error', result.message)
    }
  }

  const confirmDeleteAccount = async () => {
    if (!deletePassword.value) {
      showNotification('error', 'Please enter your password')
      return
    }

    const result = await settingsStore.deleteAccount(deletePassword.value)

    if (result.success) {
      showDeleteAccount.value = false
      router.push('/login')
      showNotification('success', 'Account deleted successfully')
    } else {
      showNotification('error', result.message)
    }
  }

  const showNotification = (type, message) => {
    // This would integrate with your notification system
    console.log(`${type}: ${message}`)
  }

  // Load settings on mount
  settingsStore.loadSettings()
</script>

<style scoped>
  .settings-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    z-index: 99999;
    overflow-y: auto;
    padding: 1rem;
  }

  .settings-container {
    max-width: 1400px;
    width: 100%;
    margin: 2rem auto;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .settings-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px 16px 0 0;
    color: white;
  }

  .back-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }

  .back-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
  }

  .settings-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .settings-subtitle {
    margin: 0;
    opacity: 0.9;
  }

  .settings-content {
    display: grid;
    grid-template-columns: 250px 1fr;
    min-height: 600px;
  }

  .settings-sidebar {
    background: #f9fafb;
    border-right: 1px solid #e5e7eb;
    padding: 1.5rem 0;
  }

  .settings-nav {
    display: flex;
    flex-direction: column;
  }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1.5rem;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 0.9375rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s ease;
    text-align: left;
    border-left: 3px solid transparent;
  }

  .nav-item:hover {
    background: #f3f4f6;
    color: #374151;
  }

  .nav-item.active {
    background: #ede9fe;
    color: #667eea;
    border-left-color: #667eea;
  }

  .nav-item i {
    width: 20px;
    text-align: center;
  }

  .settings-panel {
    padding: 2rem;
    overflow-y: auto;
    max-height: calc(100vh - 200px);
  }

  .settings-section {
    max-width: 700px;
  }

  .section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
  }

  .form-group {
    margin-bottom: 1.5rem;
  }

  .form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
  }

  .form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9375rem;
    transition: all 0.3s ease;
  }

  .form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .form-hint {
    display: block;
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
  }

  .form-actions {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
  }

  .btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
  }

  .btn-primary {
    background: #667eea;
    color: white;
  }

  .btn-primary:hover {
    background: #5568d3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  .btn-secondary {
    background: #f3f4f6;
    color: #374151;
  }

  .btn-secondary:hover {
    background: #e5e7eb;
  }

  .btn-danger {
    background: #ef4444;
    color: white;
  }

  .btn-danger:hover {
    background: #dc2626;
  }

  .toggle-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .toggle-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
  }

  .toggle-info {
    flex: 1;
  }

  .toggle-label {
    display: block;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
  }

  .toggle-description {
    display: block;
    font-size: 0.875rem;
    color: #6b7280;
  }

  .toggle-switch {
    position: relative;
    width: 50px;
    height: 26px;
    display: inline-block;
  }

  .toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.3s;
    border-radius: 26px;
  }

  .slider:before {
    position: absolute;
    content: '';
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
  }

  input:checked + .slider {
    background-color: #667eea;
  }

  input:checked + .slider:before {
    transform: translateX(24px);
  }

  .notification-category {
    margin-bottom: 2rem;
  }

  .category-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
  }

  .theme-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
  }

  .theme-option {
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
  }

  .theme-option:hover {
    border-color: #667eea;
    background: #f9fafb;
  }

  .theme-option.active {
    border-color: #667eea;
    background: #ede9fe;
    color: #667eea;
  }

  .theme-option i {
    font-size: 1.5rem;
  }

  .slider-input {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .range-slider {
    flex: 1;
    height: 6px;
    border-radius: 3px;
    background: #e5e7eb;
    outline: none;
  }

  .range-slider::-webkit-slider-thumb {
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #667eea;
    cursor: pointer;
  }

  .slider-value {
    min-width: 50px;
    text-align: right;
    font-weight: 600;
    color: #667eea;
  }

  .security-actions {
    margin: 2rem 0;
  }

  .info-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: #eff6ff;
    border: 2px solid #dbeafe;
    border-radius: 12px;
    margin-bottom: 2rem;
  }

  .info-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #3b82f6;
    color: white;
    border-radius: 12px;
    font-size: 1.5rem;
  }

  .info-content h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .info-content p {
    color: #6b7280;
    margin-bottom: 1rem;
  }

  .danger-zone {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid #fee2e2;
  }

  .danger-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #dc2626;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .danger-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #fef2f2;
    border: 2px solid #fecaca;
    border-radius: 12px;
  }

  .danger-content h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .danger-content p {
    color: #6b7280;
    max-width: 500px;
  }

  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999999;
  }

  .modal-content {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  }

  .modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
  }

  .modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
  }

  .modal-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 2rem;
  }

  .modal-icon.danger {
    background: #fef2f2;
    color: #dc2626;
  }

  .modal-message {
    text-align: center;
    color: #6b7280;
    margin-bottom: 1.5rem;
  }

  @media (max-width: 768px) {
    .settings-content {
      grid-template-columns: 1fr;
    }

    .settings-sidebar {
      border-right: none;
      border-bottom: 1px solid #e5e7eb;
    }

    .settings-nav {
      flex-direction: row;
      overflow-x: auto;
    }

    .nav-item {
      border-left: none;
      border-bottom: 3px solid transparent;
      white-space: nowrap;
    }

    .nav-item.active {
      border-left: none;
      border-bottom-color: #667eea;
    }

    .form-row {
      grid-template-columns: 1fr;
    }

    .theme-selector {
      grid-template-columns: 1fr;
    }
  }
</style>
