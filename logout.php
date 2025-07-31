<?php
// logout.php
session_start();
session_unset();
session_destroy();

// show confirmation instead of immediate redirect
include 'includes/header.php';
?>
<main style="display:flex;justify-content:center;align-items:center;min-height:80vh;">
  <div style="
    width:100%;max-width:400px;
    background:#f9f9f9;
    padding:2rem;
    border-radius:8px;
    box-shadow:0 2px 12px rgba(0,0,0,0.1);
    text-align:center;
  ">
    <h2 style="margin-bottom:1rem;">You’ve Been Logged Out</h2>
    <p style="margin-bottom:2rem;">Thank you for visiting BloomBoutique.</p>
    <a href="home.html" style="
      background:#2196F3;
      color:white;
      padding:0.75rem 1.5rem;
      border:none;
      border-radius:4px;
      text-decoration:none;
      font-size:1rem;
    ">
      Back to Home
    </a>
  </div>
</main>
<?php include 'includes/footer.php'; ?>