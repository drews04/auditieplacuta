
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Auditie Placuta</title>

  
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/register.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/register.css'))); ?>">
</head>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auditie Placuta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>">
</head>
<body class="guest-body"> 
    <?php echo $__env->yieldContent('content'); ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/layouts/guest.blade.php ENDPATH**/ ?>