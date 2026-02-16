<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href=”https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css” rel=”stylesheet”>
</head>

<body>
    <div class="container mt-5">
        <h3 class="mb-4">Login</h3>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?> </div>
        <?php endif ?>

        <form action="<?= base_url('/login/process') ?>" method="post">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="<?= old('email') ?>">
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>

</html>