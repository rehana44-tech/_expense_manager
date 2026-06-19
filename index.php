<?php
require_once 'config.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Expense Manager - Track Your Finances Intelligently</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/landing.css">
</head>
<body class="landing">
    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="landing-nav-content">
            <div class="landing-logo">
                💰 Smart Expense Manager
            </div>
            <div class="landing-nav-links">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#about">About Us</a>
                <a href="#pricing">Pricing</a>
                <a href="#contact">Contact Us</a>
            </div>
            <div class="landing-nav-auth">
                <a href="login.php" class="btn-link">Sign In</a>
                <a href="register.php" class="btn btn-white btn-small">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Take Control of Your Finances</h1>
            <p>Track expenses, analyze spending patterns, and achieve your financial goals with AI-powered insights</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-large btn-white">Get Started Free</a>
                <a href="#features" class="btn btn-large btn-outline">Learn More</a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">$2M+</div>
                    <div class="stat-label">Money Tracked</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.8/5</div>
                    <div class="stat-label">User Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-container">
            <div class="section-title">
                <h2>Everything You Need to Manage Money</h2>
                <p>Powerful features designed to simplify your financial life</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Visual Analytics</h3>
                    <p>Beautiful charts and graphs that help you understand your spending patterns at a glance</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <h3>AI Assistant</h3>
                    <p>Smart chatbot that answers questions about your finances and provides personalized insights</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Multi-Device Access</h3>
                    <p>Access your expenses from any device, anywhere. Your data syncs automatically</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Budget Tracking</h3>
                    <p>Set budgets for different categories and get alerts when you're close to limits</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure & Private</h3>
                    <p>Bank-level encryption keeps your financial data safe and secure</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Smart Reports</h3>
                    <p>Generate detailed reports to understand your financial health and make better decisions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="about-section" id="about">
        <div class="about-container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About Smart Expense Manager</h2>
                    <p>We're on a mission to help people take control of their finances through intelligent expense tracking and data-driven insights.</p>
                    <p>Founded in 2024, our platform has helped thousands of users save money, reduce unnecessary spending, and achieve their financial goals. We believe that managing money shouldn't be complicated or time-consuming.</p>
                    <p>Our AI-powered tools make it easy to track expenses, understand spending patterns, and make smarter financial decisions every day.</p>
                    <div class="about-values">
                        <div class="value-item">
                            <div class="value-icon">🎯</div>
                            <h4>Simple & Intuitive</h4>
                            <p>Easy to use for everyone</p>
                        </div>
                        <div class="value-item">
                            <div class="value-icon">🔒</div>
                            <h4>Privacy First</h4>
                            <p>Your data stays yours</p>
                        </div>
                        <div class="value-item">
                            <div class="value-icon">💡</div>
                            <h4>Smart Insights</h4>
                            <p>AI-powered recommendations</p>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <div class="about-placeholder">
                        <span style="font-size: 120px;">💰</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section" id="pricing">
        <div class="pricing-container">
            <div class="section-title">
                <h2>Simple, Transparent Pricing</h2>
                <p>Choose the plan that works best for you</p>
            </div>
            
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Free</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">0</span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li>✅ Track unlimited expenses</li>
                        <li>✅ Basic analytics & charts</li>
                        <li>✅ AI chatbot assistant</li>
                        <li>✅ Export to CSV</li>
                        <li>❌ Advanced reports</li>
                        <li>❌ Budget alerts</li>
                    </ul>
                    <a href="register.php" class="btn btn-outline-dark">Get Started</a>
                </div>
                
                <div class="pricing-card featured">
                    <div class="popular-badge">Most Popular</div>
                    <div class="pricing-header">
                        <h3>Pro</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">9</span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li>✅ Everything in Free</li>
                        <li>✅ Advanced analytics</li>
                        <li>✅ Budget alerts & limits</li>
                        <li>✅ Custom categories</li>
                        <li>✅ Monthly reports</li>
                        <li>✅ Priority support</li>
                    </ul>
                    <a href="register.php" class="btn btn-primary">Start Free Trial</a>
                </div>
                
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Business</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">29</span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li>✅ Everything in Pro</li>
                        <li>✅ Team collaboration</li>
                        <li>✅ Receipt scanning</li>
                        <li>✅ API access</li>
                        <li>✅ Custom integrations</li>
                        <li>✅ Dedicated support</li>
                    </ul>
                    <a href="register.php" class="btn btn-outline-dark">Contact Sales</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="contact-container">
            <div class="section-title">
                <h2>Get In Touch</h2>
                <p>Have questions? We'd love to hear from you</p>
            </div>
            
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <h4>Email</h4>
                        <p>support@expensemanager.com</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📱</div>
                        <h4>Phone</h4>
                        <p>+1 (555) 123-4567</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <h4>Address</h4>
                        <p>123 Finance Street<br>San Francisco, CA 94102</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">⏰</div>
                        <h4>Hours</h4>
                        <p>Mon-Fri: 9am - 6pm PST<br>Weekend: Closed</p>
                    </div>
                </div>
                
                <div class="contact-form">
                    <form id="contactForm">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Transform Your Finances?</h2>
            <p>Join thousands of users who are taking control of their money with smart expense tracking</p>
            <a href="register.php" class="btn btn-large btn-white">Start Tracking Today</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>💰 Smart Expense Manager</h3>
                <p>Making financial management simple, intelligent, and accessible for everyone.</p>
                <div class="social-links">
                    <a href="#" title="Facebook">📘</a>
                    <a href="#" title="Twitter">🐦</a>
                    <a href="#" title="Instagram">📷</a>
                    <a href="#" title="LinkedIn">💼</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Product</h4>
                <ul class="footer-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="login.php">Sign In</a></li>
                    <li><a href="register.php">Sign Up</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Company</h4>
                <ul class="footer-links">
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Support</h4>
                <ul class="footer-links">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Security</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Smart Expense Manager. All rights reserved.</p>
            <p>Built with ❤️ for better financial management</p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Contact form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.landing-nav');
            if (window.scrollY > 50) {
                nav.style.background = 'rgba(255, 255, 255, 0.95)';
                nav.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            } else {
                nav.style.background = 'rgba(255, 255, 255, 0.1)';
                nav.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>