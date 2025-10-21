<template>
  <div class="auth-page">
    <!-- Animated Background -->
    <div class="auth-background">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="auth-container">
      <!-- Branding Section -->
      <div class="auth-branding">
        <div class="brand-logo">
          <div class="logo-icon">
            <i class="fas fa-wallet"></i>
          </div>
          <h1 class="brand-name">ExpenseTracker</h1>
        </div>
        <p class="brand-tagline">Take control of your financial future</p>
      </div>

      <!-- Register Form Card -->
      <div class="auth-card">
        <div class="auth-header">
          <h2 class="auth-title">Create Account</h2>
          <p class="auth-subtitle">Start your financial journey today</p>
        </div>

        <RegisterForm />

        <div class="auth-footer">
          <p class="auth-switch">
            Already have an account?
            <router-link to="/login" class="auth-link">Sign in here</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import RegisterForm from '@/components/auth/RegisterForm.vue'
</script>

<style scoped>
  /* Modern Register Page Styles */
  .auth-page {
    min-height: 100vh;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    overflow: hidden;
  }

  /* Animated Background */
  .auth-background {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    z-index: 0;
  }

  .gradient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.7;
    animation: float 6s ease-in-out infinite;
  }

  .orb-1 {
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.1) 70%);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
  }

  .orb-2 {
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(240, 147, 251, 0.4) 0%, rgba(240, 147, 251, 0.1) 70%);
    bottom: -5%;
    right: -5%;
    animation-delay: 2s;
  }

  .orb-3 {
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(79, 172, 254, 0.3) 0%, rgba(79, 172, 254, 0.1) 70%);
    top: 30%;
    right: 10%;
    animation-delay: 4s;
  }

  .floating-shapes {
    position: absolute;
    inset: 0;
    z-index: 1;
  }

  .shape {
    position: absolute;
    opacity: 0.1;
    animation: drift 8s ease-in-out infinite;
  }

  .shape-1 {
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 30%;
    top: 20%;
    left: 10%;
    animation-delay: 1s;
  }

  .shape-2 {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    top: 70%;
    left: 80%;
    animation-delay: 3s;
  }

  .shape-3 {
    width: 80px;
    height: 80px;
    background: white;
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
    top: 60%;
    left: 20%;
    animation-delay: 5s;
  }

  .shape-4 {
    width: 50px;
    height: 50px;
    background: white;
    transform: rotate(45deg);
    top: 30%;
    left: 85%;
    animation-delay: 7s;
  }

  /* Main Container */
  .auth-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 1200px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
  }

  @media (max-width: 968px) {
    .auth-container {
      grid-template-columns: 1fr;
      gap: 2rem;
      max-width: 500px;
    }
  }

  /* Branding Section */
  .auth-branding {
    color: white;
    text-align: center;
  }

  @media (max-width: 968px) {
    .auth-branding {
      order: 2;
    }
  }

  .brand-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .logo-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    animation: pulse 3s ease-in-out infinite;
  }

  .brand-name {
    font-size: 3rem;
    font-weight: 800;
    margin: 0;
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  }

  .brand-tagline {
    font-size: 1.25rem;
    opacity: 0.9;
    font-weight: 300;
    margin: 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  /* Auth Card */
  .auth-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 3rem;
    box-shadow:
      0 20px 60px rgba(0, 0, 0, 0.1),
      0 10px 30px rgba(0, 0, 0, 0.05),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
  }

  @media (max-width: 968px) {
    .auth-card {
      order: 1;
      padding: 2rem;
    }
  }

  .auth-header {
    text-align: center;
    margin-bottom: 2rem;
  }

  .auth-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .auth-subtitle {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
    opacity: 0.8;
  }

  .auth-footer {
    margin-top: 2rem;
    text-align: center;
  }

  .auth-switch {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
  }

  .auth-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
  }

  .auth-link:hover {
    color: #5a67d8;
    text-shadow: 0 0 8px rgba(102, 126, 234, 0.3);
  }

  .auth-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
  }

  .auth-link:hover::after {
    width: 100%;
  }

  /* Animations */
  @keyframes float {
    0%,
    100% {
      transform: translateY(0) rotate(0deg);
    }
    50% {
      transform: translateY(-20px) rotate(180deg);
    }
  }

  @keyframes drift {
    0%,
    100% {
      transform: translateX(0) translateY(0);
    }
    25% {
      transform: translateX(10px) translateY(-10px);
    }
    50% {
      transform: translateX(-5px) translateY(-20px);
    }
    75% {
      transform: translateX(-10px) translateY(-5px);
    }
  }

  @keyframes pulse {
    0%,
    100% {
      transform: scale(1);
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
    }
    50% {
      transform: scale(1.05);
      box-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
    }
  }

  @keyframes slideInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Mobile Responsive */
  @media (max-width: 480px) {
    .auth-page {
      padding: 0.5rem;
    }

    .auth-card {
      padding: 1.5rem;
      border-radius: 16px;
    }

    .brand-name {
      font-size: 2rem;
    }

    .brand-tagline {
      font-size: 1rem;
    }

    .auth-title {
      font-size: 1.5rem;
    }
  }
</style>
