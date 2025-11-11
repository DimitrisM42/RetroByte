
<footer class="footer">
  <div class="container">

    <div class="us">
      <img src="assets/images/RetroByteLogo.png" alt="RetroByte">
      <span>RetroByte</span>
      <p>Bringing back the golden age of video games. One pixel at a time.</p>
    </div>

    <div class="links">
      <h3>Links</h3>
      <a href="index.php"><i class="hn hn-home-solid"></i> Home</a>
      <a href="shop.php"><i class="hn hn-save-solid"></i> Shop</a>
      <a href="about.php"><i class="hn hn-user-solid"></i> About</a>
      <a href="contact.php"><i class="hn hn-envelope-solid"></i> Contact</a>
    </div>

    <div class="socials">
      <h3>Socials</h3>
      <a href="Instagram.com"><i class="hn hn-instagram"></i>Instagram</a>
      <a href="Linkedin.com"><i class="hn hn-linkedin"></i>LinkedIn</a>
      <a href="Twitter.com"><i class="hn hn-twitter"></i>Twitter</a>
    </div>

    <div class="theme-toggle">
      <h3>Theme</h3>
      <button onclick="toggleTheme()" class="theme-toggle">
        Toggle Theme
      </button>
    </div>

  </div>




  <div class="footer-end">
     <p>&copy; <?php echo date("Y"); ?> RetroByte. All rights reserved</p>
  </div>
  


  
</footer>



<script>
  
  function toggleTheme() {
    const r = document.documentElement;
    r.dataset.theme = r.dataset.theme === 'dark' ? '' : 'dark';
    
    if (r.dataset.theme) localStorage.setItem('theme','dark'); 
    else localStorage.removeItem('theme');
  }
  
  (function () {
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.dataset.theme = 'dark';
    }
  })();
</script>









