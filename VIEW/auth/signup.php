<?php
// Start output buffering to prevent any output before headers are sent
ob_start();

// Start session
session_start();

// Check for login error
$login_error = '';
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
    <link rel="stylesheet" href="../../public/css/land.css">
    <link rel="stylesheet" href="../../public/css/signup.css">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="../../public/js/toggle.js"></script>

    <title>Sign Up - Kando</title>
    <style>
      /* Improved styling for the signup form */
      #signup {
        padding-bottom: 15px;
        overflow: visible;
      }

      /* Grid layout for the signup form */
      .signup-grid {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 15px;
        margin-top: 10px;
      }

      /* Photo section styling */
      .photo-section {
        grid-column: 1;
      }

      /* Form fields section */
      .form-fields {
        grid-column: 2;
        display: flex;
        flex-direction: column;
        gap: 8px;
      }

      /* Row styling */
      .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
      }

      /* Full width for password field */
      .form-row:last-child {
        grid-template-columns: 1fr;
      }

      /* Profile photo styling */
      .profile-photo-container {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto;
        cursor: pointer;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        display: block;
        border: 2px solid var(--primary-color);
        transition: all 0.3s ease;
      }

      .profile-photo-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.3);
      }

      .profile-photo-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.3s ease;
      }

      .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        padding: 10px;
      }

      .profile-photo-container:hover .photo-overlay {
        opacity: 1;
      }

      .profile-photo-container:hover .profile-photo-preview {
        transform: scale(1.1);
      }

      /* Form styling improvements */
      .form-group {
        margin-bottom: 8px;
      }

      .form-group label {
        display: block;
        margin-bottom: 4px;
        font-weight: bold;
        color: var(--primary-color);
        font-size: 14px;
      }

      .form-group input {
        padding: 8px;
        height: 36px;
      }

      .form-group input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2);
        outline: none;
      }

      /* Submit button styling */
      .btn {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }

      /* Sign-in grid styling */
      .signin-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
      }

      /* Responsive styles */
      @media (max-width: 768px) {
        .signup-grid {
          grid-template-columns: 1fr;
          gap: 15px;
        }

        .photo-section {
          grid-column: 1;
          grid-row: 1;
        }

        .form-fields {
          grid-column: 1;
          grid-row: 2;
        }

        .form-row {
          grid-template-columns: 1fr;
          gap: 15px;
        }

        .profile-photo-container {
          width: 150px;
          height: 150px;
        }

        .signup-container {
          padding: 15px;
        }
      }
    </style>
  </head>
  <body>
  <nav class="navbar" id="navbar">
      <a href="../landing.php">
        <div class="logo"><i class="fas fa-tasks"></i> Kando</div>
      </a>
      <div class="nav-links">
        <a href="../landing.php#features">Features</a>
        <a href="../landing.php#faq">FAQ</a>
        <a href="../landing.php#workflow">How It Works</a>
        <a href="#" id="signInButtonNav">Log In</a>
        <a href="#" class="signUpButtonNav" onclick="showSignupForm(); return false;">
          <button class="btn-small">Sign Up Free</button></a
        >
        <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
          <i class="fas fa-moon"></i>
        </button>
      </div>
    </nav>
    <div class="signup-container" id="signup" style="display: none; flex-direction: column; max-width: 800px; width: 90%; padding: 15px 20px;">
      <div class="signup-header" style="margin-bottom: 5px;">
        <h2 style="margin-bottom: 5px; font-size: 20px;">Create your account</h2>
      </div>

      <form action="../../controllers/auth_controller.php" method="POST" enctype="multipart/form-data">
        <div class="signup-grid">
          <div class="photo-section">
            <div class="profile-photo-container" onclick="document.getElementById('photo-upload').click();">
              <img id="profile-preview" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="Profile Photo" class="profile-photo-preview" style="width: 100%; height: 100%; object-fit: cover;" />
              <div class="photo-overlay">Click to upload</div>
              <input type="file" id="photo-upload" name="photo-upload" accept="image/*" style="display: none;" />
              <input type="hidden" id="photo" name="photo" value="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" />
            </div>
            <div style="text-align: center; margin-top: 3px; font-size: 11px; color: var(--text-muted);">Click to upload</div>
          </div>

          <div class="form-fields">
            <div class="form-row">
              <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="fullName" required />
              </div>
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required />
              </div>
            </div>

            <!-- Middle row -->
            <div class="form-row">
              <div class="form-group">
                <label for="profession">Profession</label>
                <input type="text" id="profession" name="profession" required />
              </div>
              <div class="form-group">
                <label for="signup-email">Email Address</label>
                <input type="email" id="signup-email" name="email" required />
              </div>
            </div>

            <!-- Bottom row -->
            <div class="form-row">
              <div class="form-group">
                <label for="signup-password">Password</label>
                <input type="password" id="signup-password" name="password" required />
              </div>
            </div>
          </div>
        </div>

        <button type="submit" class="btn" style="margin-top: 10px; width: 100%; padding: 8px; font-size: 15px; font-weight: bold;">CREATE ACCOUNT</button>
      </form>

      <div class="login-link" style="margin-top: 5px; font-size: 13px;">
        Already have an account? <a href="#" id="signInButton">Sign in</a>
      </div>
    </div>

    <div class="signup-container" id="signin" style="display: flex; max-width: 800px; width: 90%; padding: 15px 20px;">
      <div class="signup-header" style="margin-bottom: 5px;">
        <h2 class="typing" style="margin-bottom: 5px; font-size: 20px;">Welcome Back</h2>
      </div>
      <form method="POST" action="../../controllers/auth_controller.php" style="width: 100%;">
        <div class="signin-grid">
          <div class="form-group" style="margin-bottom: 8px;">
            <label for="signin-email" style="display: block; margin-bottom: 4px; font-weight: bold; color: var(--primary-color); font-size: 14px;">Email Address</label>
            <input type="email" id="signin-email" name="email" required style="width: 100%; padding: 8px; height: 36px;" />
          </div>

          <div class="form-group" style="margin-bottom: 8px;">
            <label for="signin-password" style="display: block; margin-bottom: 4px; font-weight: bold; color: var(--primary-color); font-size: 14px;">Password</label>
            <input type="password" id="signin-password" name="password" required style="width: 100%; padding: 8px; height: 36px;" />
          </div>
        </div>
        <button type="submit" class="btn" style="margin-top: 10px; width: 100%; padding: 8px; font-size: 15px; font-weight: bold;">SIGN IN</button>
      </form>
      <div class="alternative-signup" style="margin-top: 10px; margin-bottom: 10px;">
        <p style="margin: 5px 0; font-size: 13px;">  Don't have an account?</p>
     
      </div>

      <div class="login-link" style="margin-top: 5px; font-size: 13px;">
       <a href="#" id="signUpButton">Sign up here</a>
      </div>
   
    </div>

    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const signInForm = document.getElementById("signin");
        const signUpForm = document.getElementById("signup");
        const signInButtons = document.querySelectorAll(
          "#signInButton, #signInButtonNav"
        );
        const signUpButtons = document.querySelectorAll(
          "#signUpButton, .signUpButtonNav"
        );

        // Handle sign in buttons
        signInButtons.forEach((button) => {
          button.addEventListener("click", function (e) {
            e.preventDefault();
            signUpForm.style.display = "none";
            signInForm.style.display = "flex";
          });
        });

        // Handle sign up buttons
        signUpButtons.forEach((button) => {
          button.addEventListener("click", function (e) {
            e.preventDefault();
            signInForm.style.display = "none";
            signUpForm.style.display = "flex";
          });
        });

        // Add a function to show the signup form directly
        window.showSignupForm = function() {
          signInForm.style.display = "none";
          signUpForm.style.display = "flex";

          // Make sure the image is loaded
          const img = document.getElementById('profile-preview');
          if (img) {
            // Force reload the image
            const src = img.src;
            img.src = '';
            setTimeout(() => {
              img.src = src;
            }, 50);
          }
        };

        // Handle profile photo upload
        const photoContainer = document.querySelector('.profile-photo-container');
        const photoUpload = document.getElementById('photo-upload');
        const photoPreview = document.getElementById('profile-preview');
        const photoInput = document.getElementById('photo');

        if (photoContainer && photoUpload) {
          photoContainer.addEventListener('click', function() {
            photoUpload.click();
          });

          photoUpload.addEventListener('change', function() {
            if (this.files && this.files[0]) {
              const reader = new FileReader();

              reader.onload = function(e) {
                // Update the preview image
                photoPreview.src = e.target.result;

                // Store the base64 data in the hidden input
                photoInput.value = e.target.result;

                // Log for debugging
                console.log('Image loaded, length: ' + e.target.result.length);

                // Check if the image is too large (over 1MB)
                if (e.target.result.length > 1000000) {
                  alert('Warning: The image is quite large. Consider using a smaller image for better performance.');
                }
              };

              reader.readAsDataURL(this.files[0]);
            }
          });
        }
      });
    </script>
    <?php if (!empty($login_error)): ?>
    <script>
      // Display the error message
      alert("<?php echo $login_error; ?>");
    </script>
    <?php endif; ?>
  </body>
</html>
<?php
// End output buffering and send output to browser
ob_end_flush();
?>
