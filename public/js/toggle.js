function toggleTheme() {
  const currentTheme = localStorage.getItem('theme') || 'light';
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  
  // Save to localStorage
  localStorage.setItem('theme', newTheme);
  
  // Also set a cookie for PHP to read
  document.cookie = `theme=${newTheme}; path=/; max-age=${60*60*24*365}`;
  
  // Apply theme to document
  document.documentElement.setAttribute('data-theme', newTheme);
  document.body.setAttribute('data-theme', newTheme);
  
  // Update icon
  const themeToggleBtn = document.getElementById('themeToggle');
  if (themeToggleBtn) {
    const icon = themeToggleBtn.querySelector('i');
    if (icon) {
      if (newTheme === 'dark') {
        icon.className = 'fas fa-sun';
      } else {
        icon.className = 'fas fa-moon';
      }
    }
  }
  
  // If the page has circles, update their colors
  if (typeof updateCircleColors === 'function') {
    updateCircleColors();
  }
}

// Apply saved theme when the DOM is loaded
function applyTheme() {
  const savedTheme = localStorage.getItem('theme') || 'light';
  
  // Apply theme to document
  document.documentElement.setAttribute('data-theme', savedTheme);
  document.body.setAttribute('data-theme', savedTheme);
  
  // Update icon if it exists
  const themeToggleBtn = document.getElementById('themeToggle');
  if (themeToggleBtn) {
    const icon = themeToggleBtn.querySelector('i');
    if (icon) {
      icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
  }
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', applyTheme);
