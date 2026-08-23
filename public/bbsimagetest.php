<?php

$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if (isset($_POST['body'])) {
   $image_filename = null;
   if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
     $mime_type = mime_content_type($_FILES['image']['tmp_name']) ?: $_FILES['image']['type'];
     
     if (preg_match('/^image\//', $mime_type) !== 1) {
       header("HTTP/1.1 302 Found");
       header("Location: ./bbsimagetest.php");
       return;
     }

     $pathinfo = pathinfo($_FILES['image']['name']);
     $extension = !empty($pathinfo['extension']) ? $pathinfo['extension'] : 'jpg';

     $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
     $filepath = '/var/www/upload/image/' . $image_filename;
     move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
   }

   $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (body, image_filename) VALUES (:body, :image_filename)");
   $insert_sth->execute([
     ':body' => $_POST['body'],
     ':image_filename' => $image_filename,
   ]);

   header("HTTP/1.1 302 Found");
   header("Location: ./bbsimagetest.php");
   return;
}

$select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth->execute();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @media (max-width: 768px) {
      body { padding: 10px; margin: 0; }
      textarea, input, button { width: 100%; box-sizing: border-box; }
      img { max-width: 100%; height: auto; }
    }
  </style>
</head>
<body>

<form method="POST" action="./bbsimagetest.php" enctype="multipart/form-data">
   <textarea id="messageInput" name="body" required></textarea>
   <div style="margin: 1em 0;">
     <input type="file" accept="image/*" name="image" id="imageInput">
   </div>
   <button type="submit">送信</button>
</form>

<hr>

<?php foreach($select_sth as $entry): ?>
   <?php
     // 1. エスケープ処理
     $body = htmlspecialchars($entry['body']);
     // 2. 本文内の ">>数字" を ページ内リンク <a href="#entry-数字">>>数字</a> に自動置換
     $body_with_anchor = preg_replace('/&gt;&gt;(\d+)/', '<a href="#entry-$1">&gt;&gt;$1</a>', $body);
   ?>
   <!-- ページ内移動用の id="entry-投稿ID" を付与 -->
   <dl id="entry-<?= htmlspecialchars($entry['id']) ?>" style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
     <dt>ID</dt>
     <!-- ID欄には単純な数字のみ表示 -->
     <dd><?= htmlspecialchars($entry['id']) ?></dd>
     <dt>日時</dt>
     <dd><?= htmlspecialchars($entry['created_at']) ?></dd>
     <dt>内容</dt>
     <dd>
       <!-- リンク変換後の本文を表示 -->
       <?= nl2br($body_with_anchor) ?>
       <?php if(!empty($entry['image_filename'])): ?>
       <div>
         <img src="/image/<?= htmlspecialchars($entry['image_filename']) ?>" style="max-height: 10em;">
       </div>
       <?php endif; ?>
     </dd>
   </dl>
<?php endforeach ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
   const imageInput = document.getElementById("imageInput");
   imageInput.addEventListener("change", () => {
     if (imageInput.files.length < 1) return;

     const file = imageInput.files[0];
     // 5MBより大きい場合は Canvas で縮小してセットし直す
     if (file.size > 5 * 1024 * 1024) {
       const img = new Image();
       img.src = URL.createObjectURL(file);
       img.onload = () => {
         const canvas = document.createElement("canvas");
         let width = img.width;
         let height = img.height;
         const maxDim = 1280;

         if (width > maxDim || height > maxDim) {
           if (width > height) {
             height = Math.round((height * maxDim) / width);
             width = maxDim;
           } else {
             width = Math.round((width * maxDim) / height);
             height = maxDim;
           }
         }

         canvas.width = width;
         canvas.height = height;
         canvas.getContext("2d").drawImage(img, 0, 0, width, height);

         canvas.toBlob((blob) => {
           const dt = new DataTransfer();
           dt.items.add(new File([blob], file.name, { type: "image/jpeg" }));
           imageInput.files = dt.files;
         }, "image/jpeg", 0.6);
       };
     }
   });
});
</script>

</body>
</html>
