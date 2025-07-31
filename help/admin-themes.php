<?php
// help/admin-themes.php
include __DIR__ . '/../includes/header.php';
?>
<main class="container">
  <h1>Admin</h1>
  <p>Only administrators can change the look of BloomBoutique, view/update products,  and view customer messages & orders.</p>
  <!--switch themes-->
  </p>To switch themes:</p>
  <ol>
    <li>Log in with an admin account (<em>contact your site owner if you need privileges</em>).</li>
    <li>Click <strong>Admin</strong> in the main menu. Then click <strong> Manage Themes</strong>.</li>
    <li>On the Themes page, use the dropdown to select one of:</li>
      <ul>
        <li>Default</li>
        <li>Spring</li>
        <li>Holiday</li>
      </ul>
    <li>Click <strong>Activate Theme</strong>. The site will immediately load the new colors.</li>
    <li>To return, click <strong>Back to Dashboard</strong>.</li>
  </ol>
  <p>Each theme uses a separate CSS file under <code>/themes/</code>. You can also add new themes by uploading a CSS file there and updating the list in <code>admin/themes.php</code>.</p>
  
<!--manage products page-->
  </p>To manage products:</p>
  <ol>
    <li>Log in with an admin account (<em>contact your site owner if you need privileges</em>).</li>
    <li>Click <strong>Admin</strong> in the main menu. Then click <strong> Manage Products</strong>.</li>
    <li>On the product page, you can either edit or delete an existing product, or add a new product:</li>
    <li>To add a new product:</li>
      <ul>
        <li>Click the <strong>Add New Product</strong> button</li>
        <li>Fill out the information regarding the new product. You can upload a photo from your computer.</li>
        <li>Press <strong>Add Product</strong> to save changes.</li>
      </ul>
    <li>To edit a product:</li>
    <ul>
        <li>Click the <strong>edit</strong> button beside any existing product.</li>
        <li>Edit the information of product as desired.</li>
        <li>Press <strong>Save Changes</strong> to save.</li>
      </ul>
    <li>To delete a product:</li>
    <ul>
        <li>Click the <strong>delete</strong> button beside any existing product.</li>
        <li>When the confirmation window pops-up, click <strong>OK</strong></li>
      </ul>
  </ol>
<!--customer messages-->
  </p>To view customer messages:</p>
  <ol>
    <li>Log in with an admin account (<em>contact your site owner if you need privileges</em>).</li>
    <li>Click <strong>Admin</strong> in the main menu. Then click <strong>View Customer Messages</strong>.</li>
    <li>On the messages page, you can view customer messages and delete them</li>
    </ol>

<!--customer orders-->
  </p>To view customer orders:</p>
  <ol>
    <li>Log in with an admin account (<em>contact your site owner if you need privileges</em>).</li>
    <li>Click <strong>Admin</strong> in the main menu. Then click <strong>View Customer Orders</strong>.</li>
    <li>On the orders page, you can view customer order information and delete them</li>
    </ol>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>