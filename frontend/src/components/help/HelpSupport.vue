<template>
  <div class="help-support-overlay" @click="$emit('close')">
    <div class="help-support-container" @click.stop>
      <div class="help-header">
        <button @click="$emit('close')" class="back-btn">
          <i class="fas fa-arrow-left"></i>
        </button>
        <div>
          <h1 class="help-title">
            <i class="fas fa-question-circle"></i>
            Help & Support
          </h1>
          <p class="help-subtitle">Find answers to common questions and get support</p>
        </div>
      </div>

    <!-- Search Box -->
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search for help..."
        class="search-input"
      />
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
      <h3 class="section-title">Quick Links</h3>
      <div class="links-grid">
        <button @click="scrollToSection('getting-started')" class="quick-link-card">
          <i class="fas fa-rocket"></i>
          <span>Getting Started</span>
        </button>
        <button @click="scrollToSection('expenses')" class="quick-link-card">
          <i class="fas fa-receipt"></i>
          <span>Expenses</span>
        </button>
        <button @click="scrollToSection('budgets')" class="quick-link-card">
          <i class="fas fa-wallet"></i>
          <span>Budgets</span>
        </button>
        <button @click="scrollToSection('reports')" class="quick-link-card">
          <i class="fas fa-chart-line"></i>
          <span>Reports</span>
        </button>
      </div>
    </div>

    <!-- FAQ Sections -->
    <div class="faq-sections">
      <!-- Getting Started -->
      <div id="getting-started" class="faq-section">
        <h2 class="section-heading">
          <i class="fas fa-rocket"></i>
          Getting Started
        </h2>
        <div class="faq-items">
          <div
            v-for="(item, index) in filteredFAQs('getting-started')"
            :key="index"
            class="faq-item"
            :class="{ active: activeItem === `getting-started-${index}` }"
          >
            <button @click="toggleItem(`getting-started-${index}`)" class="faq-question">
              <span>{{ item.question }}</span>
              <i
                class="fas"
                :class="activeItem === `getting-started-${index}` ? 'fa-chevron-up' : 'fa-chevron-down'"
              ></i>
            </button>
            <div v-if="activeItem === `getting-started-${index}`" class="faq-answer" v-html="item.answer"></div>
          </div>
        </div>
      </div>

      <!-- Managing Expenses -->
      <div id="expenses" class="faq-section">
        <h2 class="section-heading">
          <i class="fas fa-receipt"></i>
          Managing Expenses
        </h2>
        <div class="faq-items">
          <div
            v-for="(item, index) in filteredFAQs('expenses')"
            :key="index"
            class="faq-item"
            :class="{ active: activeItem === `expenses-${index}` }"
          >
            <button @click="toggleItem(`expenses-${index}`)" class="faq-question">
              <span>{{ item.question }}</span>
              <i
                class="fas"
                :class="activeItem === `expenses-${index}` ? 'fa-chevron-up' : 'fa-chevron-down'"
              ></i>
            </button>
            <div v-if="activeItem === `expenses-${index}`" class="faq-answer" v-html="item.answer"></div>
          </div>
        </div>
      </div>

      <!-- Budget Management -->
      <div id="budgets" class="faq-section">
        <h2 class="section-heading">
          <i class="fas fa-wallet"></i>
          Budget Management
        </h2>
        <div class="faq-items">
          <div
            v-for="(item, index) in filteredFAQs('budgets')"
            :key="index"
            class="faq-item"
            :class="{ active: activeItem === `budgets-${index}` }"
          >
            <button @click="toggleItem(`budgets-${index}`)" class="faq-question">
              <span>{{ item.question }}</span>
              <i
                class="fas"
                :class="activeItem === `budgets-${index}` ? 'fa-chevron-up' : 'fa-chevron-down'"
              ></i>
            </button>
            <div v-if="activeItem === `budgets-${index}`" class="faq-answer" v-html="item.answer"></div>
          </div>
        </div>
      </div>

      <!-- Categories -->
      <div id="categories" class="faq-section">
        <h2 class="section-heading">
          <i class="fas fa-tags"></i>
          Categories
        </h2>
        <div class="faq-items">
          <div
            v-for="(item, index) in filteredFAQs('categories')"
            :key="index"
            class="faq-item"
            :class="{ active: activeItem === `categories-${index}` }"
          >
            <button @click="toggleItem(`categories-${index}`)" class="faq-question">
              <span>{{ item.question }}</span>
              <i
                class="fas"
                :class="activeItem === `categories-${index}` ? 'fa-chevron-up' : 'fa-chevron-down'"
              ></i>
            </button>
            <div v-if="activeItem === `categories-${index}`" class="faq-answer" v-html="item.answer"></div>
          </div>
        </div>
      </div>

      <!-- Reports & Analytics -->
      <div id="reports" class="faq-section">
        <h2 class="section-heading">
          <i class="fas fa-chart-line"></i>
          Reports & Analytics
        </h2>
        <div class="faq-items">
          <div
            v-for="(item, index) in filteredFAQs('reports')"
            :key="index"
            class="faq-item"
            :class="{ active: activeItem === `reports-${index}` }"
          >
            <button @click="toggleItem(`reports-${index}`)" class="faq-question">
              <span>{{ item.question }}</span>
              <i
                class="fas"
                :class="activeItem === `reports-${index}` ? 'fa-chevron-up' : 'fa-chevron-down'"
              ></i>
            </button>
            <div v-if="activeItem === `reports-${index}`" class="faq-answer" v-html="item.answer"></div>
          </div>
        </div>
      </div>

      <!-- Account & Security -->
      <div id="account" class="faq-section">
        <h2 class="section-heading">
          <i class="fas fa-user-shield"></i>
          Account & Security
        </h2>
        <div class="faq-items">
          <div
            v-for="(item, index) in filteredFAQs('account')"
            :key="index"
            class="faq-item"
            :class="{ active: activeItem === `account-${index}` }"
          >
            <button @click="toggleItem(`account-${index}`)" class="faq-question">
              <span>{{ item.question }}</span>
              <i
                class="fas"
                :class="activeItem === `account-${index}` ? 'fa-chevron-up' : 'fa-chevron-down'"
              ></i>
            </button>
            <div v-if="activeItem === `account-${index}`" class="faq-answer" v-html="item.answer"></div>
          </div>
        </div>
      </div>

      <!-- Troubleshooting -->
      <div id="troubleshooting" class="faq-section">
        <h2 class="section-heading">
          <i class="fas fa-tools"></i>
          Troubleshooting
        </h2>
        <div class="faq-items">
          <div
            v-for="(item, index) in filteredFAQs('troubleshooting')"
            :key="index"
            class="faq-item"
            :class="{ active: activeItem === `troubleshooting-${index}` }"
          >
            <button @click="toggleItem(`troubleshooting-${index}`)" class="faq-question">
              <span>{{ item.question }}</span>
              <i
                class="fas"
                :class="activeItem === `troubleshooting-${index}` ? 'fa-chevron-up' : 'fa-chevron-down'"
              ></i>
            </button>
            <div v-if="activeItem === `troubleshooting-${index}`" class="faq-answer" v-html="item.answer"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Support -->
    <div class="contact-support">
      <div class="support-card">
        <i class="fas fa-headset"></i>
        <h3>Still Need Help?</h3>
        <p>Our support team is here to assist you</p>
        <div class="support-actions">
          <a href="mailto:support@expensetracker.com" class="support-btn primary">
            <i class="fas fa-envelope"></i>
            Email Support
          </a>
          <button class="support-btn secondary">
            <i class="fas fa-comments"></i>
            Live Chat
          </button>
        </div>
      </div>
    </div>

    <!-- Keyboard Shortcuts -->
    <div class="keyboard-shortcuts">
      <h3 class="section-title">Keyboard Shortcuts</h3>
      <div class="shortcuts-grid">
        <div class="shortcut-item">
          <kbd>Ctrl</kbd> + <kbd>E</kbd>
          <span>Add Expense</span>
        </div>
        <div class="shortcut-item">
          <kbd>Ctrl</kbd> + <kbd>C</kbd>
          <span>Add Category</span>
        </div>
        <div class="shortcut-item">
          <kbd>Ctrl</kbd> + <kbd>B</kbd>
          <span>Set Budget</span>
        </div>
        <div class="shortcut-item">
          <kbd>Ctrl</kbd> + <kbd>A</kbd>
          <span>View Analytics</span>
        </div>
        <div class="shortcut-item">
          <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>E</kbd>
          <span>Export Data</span>
        </div>
        <div class="shortcut-item">
          <kbd>Esc</kbd>
          <span>Close Modal</span>
        </div>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue'

  defineEmits(['close'])

  const searchQuery = ref('')
  const activeItem = ref(null)

  const faqData = {
    'getting-started': [
      {
        question: 'How do I get started with Expense Tracker?',
        answer: `<p>Getting started is easy! Follow these steps:</p>
          <ol>
            <li><strong>Create your account</strong> - Sign up with your email and password</li>
            <li><strong>Set up categories</strong> - Add categories that match your spending habits (Food, Transport, etc.)</li>
            <li><strong>Create budgets</strong> - Set monthly budgets for each category</li>
            <li><strong>Start tracking</strong> - Add your first expense using the quick action button</li>
          </ol>
          <p>💡 <em>Tip: Use the Quick Actions button (bottom right) for fast access to common tasks!</em></p>`
      },
      {
        question: 'What is the dashboard and how do I use it?',
        answer: `<p>The Dashboard is your financial command center. It shows:</p>
          <ul>
            <li>📊 <strong>Total spending</strong> for the current month</li>
            <li>💰 <strong>Budget overview</strong> with usage percentages</li>
            <li>📈 <strong>Spending trends</strong> and charts</li>
            <li>🔥 <strong>Recent expenses</strong> for quick review</li>
            <li>⚠️ <strong>Budget alerts</strong> when you're approaching limits</li>
          </ul>
          <p>Access it anytime by clicking "Dashboard" in the sidebar.</p>`
      },
      {
        question: 'How do I navigate the application?',
        answer: `<p>Navigation is simple:</p>
          <ul>
            <li><strong>Sidebar</strong> - Main navigation menu (Dashboard, Expenses, Categories, Budgets, Analytics)</li>
            <li><strong>Top Header</strong> - Quick access to notifications and user profile</li>
            <li><strong>Quick Actions Button</strong> - Floating button (bottom right) for common tasks</li>
            <li><strong>Keyboard Shortcuts</strong> - Press Ctrl+E to add expense, Ctrl+B for budgets, etc.</li>
          </ul>
          <p>💡 <em>Pro tip: Use keyboard shortcuts for faster navigation!</em></p>`
      }
    ],
    expenses: [
      {
        question: 'How do I add a new expense?',
        answer: `<p>There are multiple ways to add expenses:</p>
          <ol>
            <li><strong>Quick Action Button</strong> - Click the purple button (bottom right) → "Add Expense"</li>
            <li><strong>Keyboard Shortcut</strong> - Press <kbd>Ctrl</kbd> + <kbd>E</kbd></li>
            <li><strong>Expenses Page</strong> - Go to Expenses → Click "Add Expense" button</li>
            <li><strong>Sidebar</strong> - Click "Add Expense" in the Quick Actions section</li>
          </ol>
          <p>Fill in the required fields:</p>
          <ul>
            <li>Amount (required)</li>
            <li>Description (required)</li>
            <li>Category (required)</li>
            <li>Date (defaults to today)</li>
            <li>Notes (optional)</li>
          </ul>`
      },
      {
        question: 'Can I edit or delete an expense?',
        answer: `<p>Yes! Managing expenses is easy:</p>
          <p><strong>To Edit:</strong></p>
          <ol>
            <li>Go to the Expenses page</li>
            <li>Find the expense you want to edit</li>
            <li>Click the <strong>Edit</strong> button (pencil icon)</li>
            <li>Make your changes and click "Save"</li>
          </ol>
          <p><strong>To Delete:</strong></p>
          <ol>
            <li>Go to the Expenses page</li>
            <li>Find the expense you want to delete</li>
            <li>Click the <strong>Delete</strong> button (trash icon)</li>
            <li>Confirm the deletion</li>
          </ol>
          <p>⚠️ <em>Note: Deleted expenses cannot be recovered. Budget calculations will update automatically.</em></p>`
      },
      {
        question: 'How do I search and filter expenses?',
        answer: `<p>The Expenses page offers powerful filtering:</p>
          <ul>
            <li><strong>Search Bar</strong> - Search by description or notes</li>
            <li><strong>Category Filter</strong> - Filter by one or multiple categories</li>
            <li><strong>Date Range</strong> - Select custom date ranges or use presets (This Month, Last Month, etc.)</li>
            <li><strong>Amount Range</strong> - Filter by minimum and maximum amounts</li>
            <li><strong>Sort Options</strong> - Sort by date, amount, or category</li>
          </ul>
          <p>💡 <em>Combine multiple filters for precise results!</em></p>`
      },
      {
        question: 'Can I import expenses from a CSV file?',
        answer: `<p>Yes! Import feature coming soon.</p>
          <p>You can prepare your CSV file with these columns:</p>
          <ul>
            <li>Date (YYYY-MM-DD format)</li>
            <li>Description</li>
            <li>Amount</li>
            <li>Category</li>
            <li>Notes (optional)</li>
          </ul>
          <p>📝 <em>Currently in development. Stay tuned for updates!</em></p>`
      }
    ],
    budgets: [
      {
        question: 'How do I create a budget?',
        answer: `<p>Creating budgets helps you stay on track:</p>
          <ol>
            <li>Click the <strong>Quick Actions button</strong> → "Set Budget" OR press <kbd>Ctrl</kbd> + <kbd>B</kbd></li>
            <li>Select a <strong>category</strong></li>
            <li>Enter the <strong>budget amount</strong></li>
            <li>Choose the <strong>time period</strong> (monthly, weekly, custom)</li>
            <li>Set <strong>alert thresholds</strong> (optional - get notified at 75%, 90%)</li>
            <li>Click "Create Budget"</li>
          </ol>
          <p>💡 <em>Tip: Set realistic budgets based on your past spending patterns!</em></p>`
      },
      {
        question: 'What happens when I exceed my budget?',
        answer: `<p>When you exceed a budget:</p>
          <ul>
            <li>🔴 <strong>Visual alerts</strong> - Budget card turns red</li>
            <li>🔔 <strong>Notifications</strong> - You'll receive an alert in the notification center</li>
            <li>📊 <strong>Dashboard warning</strong> - Highlighted on your dashboard</li>
            <li>📈 <strong>Analytics</strong> - Tracked in your spending patterns</li>
          </ul>
          <p>The app will continue to track expenses normally. Use this as an opportunity to:</p>
          <ul>
            <li>Review your spending in that category</li>
            <li>Adjust your budget if it was too low</li>
            <li>Cut back on unnecessary expenses</li>
          </ul>`
      },
      {
        question: 'Can I set different budgets for different time periods?',
        answer: `<p>Yes! Budget flexibility options:</p>
          <ul>
            <li><strong>Monthly Budgets</strong> - Most common, resets each month</li>
            <li><strong>Weekly Budgets</strong> - Good for short-term goals</li>
            <li><strong>Custom Period</strong> - Set specific start and end dates</li>
            <li><strong>Rolling Budgets</strong> - Continuous tracking (coming soon)</li>
          </ul>
          <p>You can have multiple active budgets for the same category with different time periods.</p>`
      },
      {
        question: 'How do budget alerts work?',
        answer: `<p>Budget alerts keep you informed:</p>
          <p><strong>Alert Levels:</strong></p>
          <ul>
            <li>🟢 <strong>Safe</strong> - Under 75% of budget</li>
            <li>🟡 <strong>Warning</strong> - 75-89% of budget used</li>
            <li>🟠 <strong>Critical</strong> - 90-99% of budget used</li>
            <li>🔴 <strong>Exceeded</strong> - Over 100% of budget</li>
          </ul>
          <p>You'll receive notifications when crossing each threshold. Customize alert settings in your profile.</p>`
      }
    ],
    categories: [
      {
        question: 'How do I create and manage categories?',
        answer: `<p>Categories help organize your expenses:</p>
          <p><strong>To Create a Category:</strong></p>
          <ol>
            <li>Go to <strong>Categories</strong> page OR press <kbd>Ctrl</kbd> + <kbd>C</kbd></li>
            <li>Click "Add Category"</li>
            <li>Enter a name (e.g., "Groceries", "Gas", "Entertainment")</li>
            <li>Choose an icon (optional)</li>
            <li>Select a color (optional)</li>
            <li>Add a description (optional)</li>
            <li>Click "Save"</li>
          </ol>
          <p>💡 <em>Tip: Use descriptive names and colors to quickly identify categories!</em></p>`
      },
      {
        question: 'Can I edit or delete categories?',
        answer: `<p>Yes, categories are fully manageable:</p>
          <p><strong>To Edit:</strong></p>
          <ul>
            <li>Go to Categories page</li>
            <li>Click the edit button on any category</li>
            <li>Make changes and save</li>
          </ul>
          <p><strong>To Delete:</strong></p>
          <ul>
            <li>Click the delete button</li>
            <li>Confirm deletion</li>
          </ul>
          <p>⚠️ <strong>Important:</strong> If a category has expenses or budgets:</p>
          <ul>
            <li>Expenses will be marked as "Uncategorized"</li>
            <li>Associated budgets will be archived</li>
            <li>You'll be prompted to reassign before deletion</li>
          </ul>`
      },
      {
        question: 'What are the default categories?',
        answer: `<p>The app comes with common categories:</p>
          <ul>
            <li>🍔 <strong>Food & Dining</strong> - Restaurants, groceries, takeout</li>
            <li>🚗 <strong>Transportation</strong> - Gas, public transit, parking</li>
            <li>🛍️ <strong>Shopping</strong> - Clothing, electronics, household items</li>
            <li>🎬 <strong>Entertainment</strong> - Movies, concerts, hobbies</li>
            <li>💰 <strong>Bills & Utilities</strong> - Rent, electricity, internet</li>
            <li>🏥 <strong>Healthcare</strong> - Medical, pharmacy, insurance</li>
            <li>✈️ <strong>Travel</strong> - Vacations, hotels, flights</li>
            <li>📚 <strong>Education</strong> - Books, courses, tuition</li>
          </ul>
          <p>You can customize, add, or remove any categories to match your needs.</p>`
      }
    ],
    reports: [
      {
        question: 'What reports are available?',
        answer: `<p>Comprehensive reporting features:</p>
          <ul>
            <li>📊 <strong>Budget Overview</strong> - Current month budget performance</li>
            <li>📈 <strong>Spending Trends</strong> - Historical spending patterns</li>
            <li>🎯 <strong>Category Analysis</strong> - Breakdown by category</li>
            <li>📅 <strong>Monthly Reports</strong> - Month-over-month comparison</li>
            <li>💡 <strong>Insights</strong> - AI-powered spending insights</li>
            <li>🔮 <strong>Forecasts</strong> - Predicted future spending</li>
          </ul>
          <p>Access reports from the <strong>Analytics</strong> page in the main menu.</p>`
      },
      {
        question: 'How do I export my data?',
        answer: `<p>Export your financial data easily:</p>
          <p><strong>Export Options:</strong></p>
          <ul>
            <li><strong>CSV Format</strong> - For Excel or Google Sheets</li>
            <li><strong>PDF Report</strong> - Formatted summary (coming soon)</li>
            <li><strong>JSON Data</strong> - Complete data backup</li>
          </ul>
          <p><strong>To Export:</strong></p>
          <ol>
            <li>Click Quick Actions button → "Export"</li>
            <li>Or press <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>E</kbd></li>
            <li>Select date range and categories</li>
            <li>Choose format</li>
            <li>Click "Download"</li>
          </ol>
          <p>💡 <em>Export data regularly for backup purposes!</em></p>`
      },
      {
        question: 'How far back can I view my data?',
        answer: `<p>All your historical data is available:</p>
          <ul>
            <li>📅 <strong>No time limit</strong> - Access all expenses since account creation</li>
            <li>🔍 <strong>Custom date ranges</strong> - Select any start and end date</li>
            <li>📊 <strong>Trend analysis</strong> - Up to 2 years of historical trends</li>
            <li>💾 <strong>Permanent storage</strong> - Data never expires</li>
          </ul>
          <p>Use the date picker on any report or expenses page to view historical data.</p>`
      }
    ],
    account: [
      {
        question: 'How do I change my password?',
        answer: `<p>To update your password:</p>
          <ol>
            <li>Click your profile picture (top right)</li>
            <li>Select "Settings"</li>
            <li>Go to "Security" tab</li>
            <li>Click "Change Password"</li>
            <li>Enter current password</li>
            <li>Enter new password (minimum 8 characters)</li>
            <li>Confirm new password</li>
            <li>Click "Update Password"</li>
          </ol>
          <p>🔒 <strong>Password Requirements:</strong></p>
          <ul>
            <li>At least 8 characters long</li>
            <li>Mix of uppercase and lowercase letters</li>
            <li>At least one number</li>
            <li>At least one special character recommended</li>
          </ul>`
      },
      {
        question: 'Is my financial data secure?',
        answer: `<p>We take security seriously:</p>
          <ul>
            <li>🔐 <strong>Encryption</strong> - All data encrypted in transit and at rest</li>
            <li>🔑 <strong>Authentication</strong> - Secure token-based authentication</li>
            <li>🛡️ <strong>Privacy</strong> - Your data is never shared with third parties</li>
            <li>💾 <strong>Backups</strong> - Daily automated backups</li>
            <li>🔒 <strong>HTTPS</strong> - Secure connections only</li>
            <li>👁️ <strong>Privacy Mode</strong> - Option to hide amounts on dashboard</li>
          </ul>
          <p>We comply with industry-standard security practices and regulations.</p>`
      },
      {
        question: 'Can I delete my account?',
        answer: `<p>Yes, you have full control over your data:</p>
          <p><strong>To Delete Account:</strong></p>
          <ol>
            <li>Go to Settings → Account</li>
            <li>Scroll to "Danger Zone"</li>
            <li>Click "Delete Account"</li>
            <li>Confirm by entering your password</li>
            <li>Choose data retention option</li>
          </ol>
          <p>⚠️ <strong>Important:</strong></p>
          <ul>
            <li>This action is permanent and cannot be undone</li>
            <li>All expenses, budgets, and reports will be deleted</li>
            <li>Export your data first if you want to keep it</li>
            <li>You can create a new account anytime</li>
          </ul>`
      }
    ],
    troubleshooting: [
      {
        question: 'Why aren\'t my expenses showing up?',
        answer: `<p>Try these troubleshooting steps:</p>
          <ol>
            <li><strong>Check filters</strong> - Clear any active date or category filters</li>
            <li><strong>Refresh the page</strong> - Click the refresh button or press F5</li>
            <li><strong>Check date range</strong> - Make sure the expense date is within selected range</li>
            <li><strong>Clear browser cache</strong> - Sometimes cached data causes issues</li>
            <li><strong>Check internet connection</strong> - Ensure you're connected</li>
          </ol>
          <p>If the problem persists, try logging out and logging back in.</p>`
      },
      {
        question: 'The application is running slowly. What can I do?',
        answer: `<p>Performance optimization tips:</p>
          <ul>
            <li>🧹 <strong>Clear browser cache</strong> - Remove old cached files</li>
            <li>📊 <strong>Limit date ranges</strong> - Don't load years of data at once</li>
            <li>🔄 <strong>Close other tabs</strong> - Free up browser memory</li>
            <li>💻 <strong>Update browser</strong> - Use latest version of Chrome, Firefox, or Safari</li>
            <li>📱 <strong>Check device storage</strong> - Ensure adequate space</li>
            <li>🌐 <strong>Test connection</strong> - Slow internet affects loading</li>
          </ul>
          <p>💡 <em>The app works best with at least 4GB RAM and modern browsers.</em></p>`
      },
      {
        question: 'I forgot my password. How do I reset it?',
        answer: `<p>Password reset is simple:</p>
          <ol>
            <li>Go to the login page</li>
            <li>Click "Forgot Password?"</li>
            <li>Enter your email address</li>
            <li>Check your email for reset link</li>
            <li>Click the link (valid for 1 hour)</li>
            <li>Enter new password</li>
            <li>Confirm new password</li>
            <li>Click "Reset Password"</li>
          </ol>
          <p>📧 <strong>Didn't receive the email?</strong></p>
          <ul>
            <li>Check spam/junk folder</li>
            <li>Verify email address is correct</li>
            <li>Wait 5 minutes and try again</li>
            <li>Contact support if still not received</li>
          </ul>`
      },
      {
        question: 'Charts and graphs aren\'t displaying correctly',
        answer: `<p>Chart display issues can be fixed:</p>
          <ul>
            <li>🔄 <strong>Refresh the page</strong> - Often resolves rendering issues</li>
            <li>📊 <strong>Check data availability</strong> - Charts need data to display</li>
            <li>🌐 <strong>Update browser</strong> - Old browsers may not support modern charts</li>
            <li>🚫 <strong>Disable ad blockers</strong> - They sometimes interfere with charts</li>
            <li>🔌 <strong>Enable JavaScript</strong> - Required for interactive charts</li>
            <li>📱 <strong>Try different device</strong> - Test on desktop if mobile has issues</li>
          </ul>
          <p>Still having issues? Take a screenshot and contact support.</p>`
      }
    ]
  }

  const toggleItem = itemId => {
    activeItem.value = activeItem.value === itemId ? null : itemId
  }

  const scrollToSection = sectionId => {
    const element = document.getElementById(sectionId)
    if (element) {
      element.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  }

  const filteredFAQs = category => {
    if (!searchQuery.value) {
      return faqData[category] || []
    }

    const query = searchQuery.value.toLowerCase()
    return (faqData[category] || []).filter(
      item => item.question.toLowerCase().includes(query) || item.answer.toLowerCase().includes(query)
    )
  }
</script>

<style scoped>
  .help-support-overlay {
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
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  .help-support-container {
    max-width: 1200px;
    width: 100%;
    margin: 2rem auto;
    padding: 2rem;
    background: #f9fafb;
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

  .help-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
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

  .help-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .help-subtitle {
    margin: 0;
    opacity: 0.9;
  }

  .search-box {
    position: relative;
    margin-bottom: 2rem;
  }

  .search-box i {
    position: absolute;
    left: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1.25rem;
  }

  .search-input {
    width: 100%;
    padding: 1rem 1rem 1rem 4rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
  }

  .search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .quick-links {
    margin-bottom: 3rem;
  }

  .section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
  }

  .links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
  }

  .quick-link-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .quick-link-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .quick-link-card i {
    font-size: 2rem;
    color: #667eea;
  }

  .quick-link-card span {
    font-weight: 500;
    color: #374151;
  }

  .faq-sections {
    display: flex;
    flex-direction: column;
    gap: 3rem;
  }

  .faq-section {
    scroll-margin-top: 2rem;
  }

  .section-heading {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .section-heading i {
    color: #667eea;
  }

  .faq-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .faq-item {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .faq-item.active {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
  }

  .faq-question {
    width: 100%;
    padding: 1.25rem 1.5rem;
    background: none;
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    text-align: left;
    transition: all 0.3s ease;
  }

  .faq-question:hover {
    background: #f9fafb;
  }

  .faq-question i {
    color: #667eea;
    font-size: 1rem;
    transition: transform 0.3s ease;
  }

  .faq-answer {
    padding: 0 1.5rem 1.5rem 1.5rem;
    color: #4b5563;
    line-height: 1.7;
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .faq-answer :deep(p) {
    margin-bottom: 1rem;
  }

  .faq-answer :deep(ul),
  .faq-answer :deep(ol) {
    margin: 1rem 0;
    padding-left: 2rem;
  }

  .faq-answer :deep(li) {
    margin-bottom: 0.5rem;
  }

  .faq-answer :deep(strong) {
    color: #1f2937;
    font-weight: 600;
  }

  .faq-answer :deep(em) {
    font-style: italic;
    color: #6b7280;
  }

  .faq-answer :deep(kbd) {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    padding: 0.125rem 0.375rem;
    font-family: monospace;
    font-size: 0.875rem;
  }

  .contact-support {
    margin: 3rem 0;
    padding: 3rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    color: white;
    text-align: center;
  }

  .support-card {
    max-width: 600px;
    margin: 0 auto;
  }

  .support-card i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.9;
  }

  .support-card h3 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
  }

  .support-card p {
    margin-bottom: 2rem;
    opacity: 0.9;
  }

  .support-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
  }

  .support-btn {
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    border: 2px solid transparent;
  }

  .support-btn.primary {
    background: white;
    color: #667eea;
  }

  .support-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  }

  .support-btn.secondary {
    background: transparent;
    color: white;
    border-color: white;
  }

  .support-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
  }

  .keyboard-shortcuts {
    margin-top: 3rem;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
  }

  .shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
  }

  .shortcut-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 8px;
  }

  .shortcut-item kbd {
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    font-family: 'Segoe UI', system-ui, sans-serif;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .shortcut-item span {
    color: #6b7280;
    font-size: 0.9rem;
  }

  @media (max-width: 768px) {
    .help-support-container {
      padding: 1rem;
    }

    .help-header {
      padding: 1.5rem;
    }

    .help-title {
      font-size: 1.5rem;
    }

    .links-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .section-heading {
      font-size: 1.5rem;
    }

    .support-actions {
      flex-direction: column;
    }

    .shortcuts-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
