<template>
  <div class="login-container">
    <!-- Background Elements -->
    <div class="login-background">
      <div class="background-gradient"></div>
      <div class="background-pattern"></div>
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="login-content">
      <div class="login-card">
        <!-- Logo and Brand -->
        <div class="login-header">
          <div class="logo-container">
            <div class="logo-icon">
              <i class="fas fa-wallet"></i>
            </div>
            <h1 class="brand-name">ExpenseTracker</h1>
          </div>
          <div class="welcome-section">
            <h2 class="welcome-title">Welcome Back!</h2>
            <p class="welcome-subtitle">
              Sign in to continue managing your expenses
            </p>
          </div>
        </div>

        <!-- Login Form -->
        <div class="login-form-container">
          <LoginForm />
        </div>

        <!-- Footer Links -->
        <div class="login-footer">
          <div class="divider">
            <span class="divider-text">New to ExpenseTracker?</span>
          </div>
          <router-link to="/register" class="register-link">
            <i class="fas fa-user-plus"></i>
            Create a new account
          </router-link>
        </div>
      </div>

      <!-- Features Preview -->
      <div class="features-preview">
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="feature-content">
            <h3>Track Expenses</h3>
            <p>Monitor your spending patterns</p>
          </div>
        </div>
        
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-tags"></i>
          </div>
          <div class="feature-content">
            <h3>Categorize</h3>
            <p>Organize expenses by category</p>
          </div>
        </div>
        
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-piggy-bank"></i>
          </div>
          <div class="feature-content">
            <h3>Budget Control</h3>
            <p>Set and monitor budgets</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import LoginForm from '@/components/auth/LoginForm.vue'
</script>

<style scoped>
/* Login Container */
.login-container {
  min-height: 100vh;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

/* Background */
.login-background {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.background-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, 
    #667eea 0%, 
    #764ba2 25%, 
    #f093fb 50%, 
    #f5576c 75%, 
    #4facfe 100%);
  opacity: 0.9;
}

.background-pattern {
  position: absolute;
  inset: 0;
  background-image: 
    radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
  background-size: 400px 400px;
  animation: patternMove 20s ease-in-out infinite;
}

@keyframes patternMove {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(-20px, -20px); }
}

/* Floating Shapes */
.floating-shapes {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 60px;
  height: 60px;
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 80px;
  height: 80px;
  top: 20%;
  right: 20%;
  animation-delay: 2s;
}

.shape-3 {
  width: 40px;
  height: 40px;
  bottom: 30%;
  left: 15%;
  animation-delay: 4s;
}

.shape-4 {
  width: 100px;
  height: 100px;
  bottom: 10%;
  right: 10%;
  animation-delay: 1s;
}

.shape-5 {
  width: 50px;
  height: 50px;
  top: 50%;
  left: 5%;
  animation-delay: 3s;
}

@keyframes float {
  0%, 100% { 
    transform: translateY(0px) rotate(0deg);
    opacity: 0.3;
  }
  50% { 
    transform: translateY(-20px) rotate(180deg);
    opacity: 0.6;
  }
}

/* Main Content */
.login-content {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  display: grid;
  grid-template-columns: 1fr;
  gap: 3rem;
  align-items: center;
}

@media (min-width: 1024px) {
  .login-content {
    grid-template-columns: 1fr 400px;
    gap: 4rem;
  }
}

/* Login Card */
.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 3rem;
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.1),
    0 0 0 1px rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  animation: cardSlideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
  max-width: 480px;
  width: 100%;
  justify-self: center;
}

@media (min-width: 1024px) {
  .login-card {
    justify-self: end;
  }
}

@keyframes cardSlideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Login Header */
.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.logo-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.75rem;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
  animation: logoGlow 2s ease-in-out infinite alternate;
}

@keyframes logoGlow {
  from { box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3); }
  to { box-shadow: 0 8px 32px rgba(102, 126, 234, 0.6); }
}

.brand-name {
  font-size: 1.875rem;
  font-weight: 800;
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.025em;
}

.welcome-section {
  animation: titleFadeIn 1s ease-out 0.3s both;
}

@keyframes titleFadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.welcome-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
  letter-spacing: -0.025em;
}

.welcome-subtitle {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.5;
}

/* Login Form Container */
.login-form-container {
  animation: formFadeIn 1s ease-out 0.5s both;
}

@keyframes formFadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Login Footer */
.login-footer {
  margin-top: 2rem;
  animation: footerFadeIn 1s ease-out 0.7s both;
}

@keyframes footerFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.divider {
  position: relative;
  text-align: center;
  margin: 1.5rem 0;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(to right, 
    transparent, 
    #e5e7eb 20%, 
    #e5e7eb 80%, 
    transparent);
}

.divider-text {
  background: rgba(255, 255, 255, 0.95);
  padding: 0 1rem;
  color: #9ca3af;
  font-size: 0.875rem;
  font-weight: 500;
}

.register-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
  color: #667eea;
  text-decoration: none;
  border-radius: 16px;
  font-weight: 600;
  border: 2px solid rgba(102, 126, 234, 0.2);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  width: 100%;
  justify-content: center;
  font-size: 0.875rem;
}

.register-link:hover {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
  border-color: rgba(102, 126, 234, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
}

.register-link i {
  font-size: 0.875rem;
}

/* Features Preview */
.features-preview {
  display: none;
}

@media (min-width: 1024px) {
  .features-preview {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    justify-self: start;
    animation: featuresSlideIn 1s ease-out 0.9s both;
  }
}

@keyframes featuresSlideIn {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.feature-item:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateX(10px);
  box-shadow: 0 8px 32px rgba(255, 255, 255, 0.1);
}

.feature-icon {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #667eea;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.feature-content h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: white;
  margin-bottom: 0.25rem;
}

.feature-content p {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.4;
}

/* Responsive Design */
@media (max-width: 768px) {
  .login-content {
    padding: 1rem;
    gap: 2rem;
  }
  
  .login-card {
    padding: 2rem;
    border-radius: 20px;
  }
  
  .logo-icon {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
  }
  
  .brand-name {
    font-size: 1.5rem;
  }
  
  .welcome-title {
    font-size: 1.5rem;
  }
  
  .welcome-subtitle {
    font-size: 0.875rem;
  }
}

@media (max-width: 480px) {
  .login-card {
    padding: 1.5rem;
    margin: 1rem;
  }
  
  .logo-container {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .welcome-title {
    font-size: 1.25rem;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .login-card {
    background: white;
    border: 2px solid #000;
  }
  
  .background-gradient {
    opacity: 0.7;
  }
  
  .feature-item {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0, 0, 0, 0.2);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .login-card,
  .welcome-section,
  .login-form-container,
  .login-footer,
  .features-preview {
    animation: none;
  }
  
  .floating-shapes .shape {
    animation: none;
  }
  
  .background-pattern {
    animation: none;
  }
  
  .logo-icon {
    animation: none;
  }
}

/* Focus improvements for accessibility */
.register-link:focus-visible {
  outline: 2px solid #667eea;
  outline-offset: 2px;
}

/* Print styles */
@media print {
  .login-background,
  .floating-shapes,
  .features-preview {
    display: none;
  }
  
  .login-container {
    background: white;
  }
  
  .login-card {
    background: white;
    box-shadow: none;
    border: 1px solid #000;
  }
}
</style>