<?php
// /BloomBoutique/admin/messages.php

require __DIR__ . '/../includes/admin_auth.php';
require __DIR__ . '/../db_connect.php';
include __DIR__ . '/../includes/header.php';

// delete request using POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    //remove the message
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")
        ->execute([$delId]);
    echo "<p style='color:green;'>Message #{$delId} deleted.</p>";
}

//fetch messages
$stmt     = $pdo->query(
    "SELECT id, name, email, message, created_at
     FROM contact_messages
     ORDER BY id DESC"
);
$messages = $stmt->fetchAll();
?>
<!--page setup-->
<main class="container">
  <h2>Manage Customer Messages</h2>

  <?php if (count($messages) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Message</th>
          <th>Received At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
        <tr>
          <td><?= $m['id'] ?></td>
          <td><?= htmlspecialchars($m['name']) ?></td>
          <td>
            <a href="mailto:<?= htmlspecialchars($m['email']) ?>">
              <?= htmlspecialchars($m['email']) ?>
            </a>
          </td>
          <td style="white-space:pre-wrap;">
            <?= nl2br(htmlspecialchars($m['message'])) ?>
          </td>
          <td>
            <?= date('M j, Y g:ia', strtotime($m['created_at'])) ?>
          </td>
          <td>
            <form method="post" style="display:inline; margin:0;">
              <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
              <button type="submit" class="btn danger small"
                      onclick="return confirm('Delete this message?');">
                Delete
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No customer messages found.</p>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>