<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kando - Boost Your Productivity</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <link rel="stylesheet" href="../public/css/land.css" />
    <script src="../public/js/toggle.js"></script>
    <script>
       document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
      item.addEventListener('click', function() {
        const answer = this.querySelector('.faq-answer');
        const toggle = this.querySelector('.faq-toggle');
        
        // Close all other FAQs
        document.querySelectorAll('.faq-answer').forEach(otherAnswer => {
          if (otherAnswer !== answer && otherAnswer.classList.contains('active')) {
            otherAnswer.classList.remove('active');
            otherAnswer.closest('.faq-item').querySelector('.faq-toggle').classList.remove('active');
          }
        });
        
        // Toggle current FAQ
        answer.classList.toggle('active');
        toggle.classList.toggle('active');
      });
    });
  });
    </script>
  </head>
  
  <body>
    <nav class="navbar" id="navbar">
      <div class="logo"><i class="fas fa-tasks"></i> Kando</div>
      <div class="nav-links">
        <a href="#features">Features</a>
        <a href="#faq">FAQ</a>
        <a href="#workflow">How It Works</a>
        <a href="./auth/signup.php">Log In</a>
        <a href="./auth/signup.php">
          <button class="btn-small">Sign Up Free</button></a
        >
        <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
          <i class="fas fa-moon"></i>
        </button>
      </div>
    </nav>

    <section class="hero-section">
      <div class="hero-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
      </div>
      
      <div class="hero-content">
        <h1 class="animate__animated animate__fadeInDown">Kando</h1>
        <p class="headline animate__animated animate__fadeInUp">
          Transform Your Workflow, Amplify Your Productivity
        </p>
        <p
          class="subheadline animate__animated animate__fadeInUp animate__delay-1s"
        >
          Experience the ultimate Kanban solution that combines simplicity with
          powerful features to help teams organize, track, and deliver
          exceptional work.
        </p>

        <div class="hero-buttons">
          <a href="./auth/signup.php">
            <button
              class="btn animate__animated animate__fadeInUp animate__delay-1s"
            >
              Get Started for Free
            </button></a
          >
          <button
            class="btn btn-secondary animate__animated animate__fadeInUp animate__delay-1s"
          >
            Watch Demo
          </button>
        </div>
      </div>
    </section>

    <section class="features-section" id="features">
      <div class="section-title animate__animated animate__fadeInUp">
        <h2>Powerful Features</h2>
        <p>
          Everything you need to streamline your workflow and boost team
          productivity
        </p>
      </div>

      <div class="features-grid">
        <div class="feature-card animate__animated animate__fadeInUp">
          <div class="feature-icon"><i class="fas fa-tasks"></i></div>
          <h3>Intuitive Drag & Drop</h3>
          <p>
            Move tasks seamlessly between columns with our intuitive
            drag-and-drop interface. Instantly visualize your workflow and track
            progress in real-time.
          </p>
          <a href="#">Learn more <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="feature-card animate__animated animate__fadeInUp">
          <div class="feature-icon"><i class="fas fa-users"></i></div>
          <h3>Team Collaboration</h3>
          <p>
            Work together effectively with real-time updates, comments, and task
            assignments. Keep everyone on the same page and boost team
            productivity.
          </p>
          <a href="#">Learn more <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="feature-card animate__animated animate__fadeInUp">
          <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
          <h3>Analytics & Reporting</h3>
          <p>
            Gain valuable insights with comprehensive analytics. Track team
            performance, identify bottlenecks, and make data-driven decisions.
          </p>
          <a href="#">Learn more <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="feature-card animate__animated animate__fadeInUp">
          <div class="feature-icon"><i class="fas fa-cogs"></i></div>
          <h3>Customizable Workflows</h3>
          <p>
            Create custom workflows tailored to your team's unique processes.
            Add columns, set up automation rules, and adapt as your needs
            evolve.
          </p>
          <a href="#">Learn more <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="feature-card animate__animated animate__fadeInUp">
          <div class="feature-icon"><i class="fas fa-bell"></i></div>
          <h3>Smart Notifications</h3>
          <p>
            Stay in the loop with customizable notifications. Get alerts for
            task assignments, due dates, and important updates.
          </p>
          <a href="#">Learn more <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="feature-card animate__animated animate__fadeInUp">
          <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
          <h3>Mobile Access</h3>
          <p>
            Access your boards anytime, anywhere with our responsive mobile app.
            Keep projects moving forward even when you're on the go.
          </p>
          <a href="#">Learn more <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </section>

    <section class="workflow-section" id="workflow">
      <div class="section-title animate__animated animate__fadeInUp">
        <h2>How Kando Works</h2>
        <p>
          A simple yet powerful workflow to transform how your team collaborates
        </p>
      </div>

      <div class="workflow-images">
        <div class="workflow-item animate__animated animate__fadeInUp">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTC5RhW2UQKMfNvPNYoP1C2xvObIFeM_QCU2g&s" alt="Create Boards" />
          <div class="workflow-overlay">
            <h3>1. Create Boards</h3>
            <p>Set up customized boards for projects, departments, or teams.</p>
          </div>
        </div>

        <div
          class="workflow-item animate__animated animate__fadeInUp animate__delay-1s"
        >
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTC5RhW2UQKMfNvPNYoP1C2xvObIFeM_QCU2g&s" alt="Add Tasks" />
          <div class="workflow-overlay">
            <h3>2. Add Tasks</h3>
            <p>
              Create cards with details, attachments, deadlines, and assignees.
            </p>
          </div>
        </div>

        <div
          class="workflow-item animate__animated animate__fadeInUp animate__delay-2s"
        >
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTC5RhW2UQKMfNvPNYoP1C2xvObIFeM_QCU2g&s" alt="Track Progress" />
          <div class="workflow-overlay">
            <h3>3. Track Progress</h3>
            <p>Move cards across columns as work progresses.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="testimonials-section" id="testimonials">
      <div class="section-title animate__animated animate__fadeInUp">
        <h2>What Our Users Say</h2>
        <p>Trusted by thousands of teams worldwide</p>
      </div>

      <div class="testimonials-container">
        <div class="testimonial-card">
          <div class="testimonial-avatar">
            <img
              src="https://t4.ftcdn.net/jpg/03/64/21/11/360_F_364211147_1qgLVxv1Tcq0Ohz3FawUfrtONzz8nq3e.jpg"
              alt="Alex Darwin"
            />
          </div>
          <div class="testimonial-quote">
            The customizable workflows and analytics have been game-changers for
             us!
          </div>
          <div class="testimonial-author">Alex Darwin</div>
          <div class="testimonial-position">CTO, TechSavvy Solutions</div>
        </div>
        <div class="testimonial-card">
          <div class="testimonial-avatar">
            <img
              src="https://img.freepik.com/free-photo/lifestyle-people-emotions-casual-concept-confident-nice-smiling-asian-woman-cross-arms-chest-confident-ready-help-listening-coworkers-taking-part-conversation_1258-59335.jpg?semt=ais_hybrid"
              alt="Alex Darwin"
            />
          </div>
          <div class="testimonial-quote">
            We've cut meeting time by 30% and increased delivery speed by 25%.
          </div>
          <div class="testimonial-author">Amanda Lex</div>
          <div class="testimonial-position">HR, TechSavvy Solutions</div>
        </div>
        <div class="testimonial-card">
          <div class="testimonial-avatar">
            <img
              src="https://t3.ftcdn.net/jpg/02/43/12/34/360_F_243123463_zTooub557xEWABDLk0jJklDyLSGl2jrr.jpg"
              alt="Alex Darwin"
            />
          </div>
          <div class="testimonial-quote">
            Kando has completely transformed how our development team works.
          </div>
          <div class="testimonial-author">Alex Darwin</div>
          <div class="testimonial-position">CTO, TechSavvy Solutions</div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="cta-content animate__animated animate__fadeInUp">
        <h2 class="cta-title">Ready to Transform Your Workflow?</h2>
        <p class="cta-text">
          Join thousands of teams already boosting their productivity with
          Kando.
        </p>
        <button class="cta-button">Start Your Free 14-Day Trial</button>
      </div>
    </section>
  
<section class="faq-section" id="faq">
  <div class="section-title animate__animated animate__fadeInUp">
    <h2>Frequently Asked Questions</h2>
    <p>Get answers to common questions about using Kando</p>
  </div>

  <div class="faq-container">
    <div class="faq-item">
      <div class="faq-question-wrapper">
        <h3 class="faq-question">What is Kando?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-answer">
        Kando is a modern Kanban-based project management tool designed to help teams
        collaborate, manage tasks, and stay productive with intuitive features like drag-and-drop,
        smart notifications, and real-time updates.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question-wrapper">
        <h3 class="faq-question">Is Kando free to use?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-answer">
        Yes! You can start using Kando for free. We also offer premium plans with additional
        features for larger teams and organizations.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question-wrapper">
        <h3 class="faq-question">Can I invite my team members?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-answer">
        Absolutely. Kando is built for collaboration—invite your teammates, assign tasks, leave
        comments, and work together seamlessly.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question-wrapper">
        <h3 class="faq-question">Is there a mobile app?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-answer">
        Yes, Kando is fully responsive and accessible on mobile devices. A dedicated mobile app is
        coming soon to make managing tasks on the go even easier.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question-wrapper">
        <h3 class="faq-question">How secure is my data?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-answer">
        We take security seriously. Your data is encrypted, backed up regularly, and protected by
        industry-standard protocols.
      </div>
    </div>
  </div>
</section>

    
    <footer>
      <div class="footer-content">
        <div class="footer-column">
          <h3>Kando</h3>
          <p>
            Empowering teams to achieve more through effective visual
            collaboration and workflow management.
          </p>
          <div class="social-icons">
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
          </div>
        </div>

        <div class="footer-column">
          <h3>Product</h3>
          <ul class="footer-links">
            <li><a href="#">Features</a></li>
            <li><a href="#">Pricing</a></li>
            <li><a href="#">Integrations</a></li>
            <li><a href="#">What's New</a></li>
            <li><a href="#">Roadmap</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Resources</h3>
          <ul class="footer-links">
            <li><a href="#">Documentation</a></li>
            <li><a href="#">Tutorials</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Community</a></li>
            <li><a href="#">Help Center</a></li>
          </ul>
        </div>
      </div>
    </footer>
    <script src="https://cdn.botpress.cloud/webchat/v2.3/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2025/04/10/13/20250410134013-EU7JN72K.js"></script>
  </body>
</html>
