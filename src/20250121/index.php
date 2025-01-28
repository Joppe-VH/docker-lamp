<?php

use Upload\MimeType as MT;
use Upload\Upload;
use Upload\FileSizeUnits as FS;

require('php_includes/db.inc.php');
require('php_includes/upload.inc.php');

$errors = [];
$file = (new Upload('inputFile'))
    ->setMaxSize(FS::MEGA_BYTE->toBytes(1))
    ->setAllowedType(MT::IMAGE_JPEG)
    ->setAllowedType(MT::IMAGE_PNG);

if (isset($_POST['formSubmit'])) {

    if (!$file->hasFile()) $errors[] = 'You need to select a file.';
    elseif ($file->hasError()) $errors[] = $file->getErrorMsg();
    elseif (!$file->move('/20250121/uploads')) $errors[] = 'something went wrong while uploading your file.';

    if (!$errors) {
        $id = insertImage(
            name: $file->name(),
            path: $file->getFinalDest()
        );

        if (!$id) $errors[] = "Something unexplainable happened...";
        else {
            // redirect so that page reloads don't re-trigger a successful upload
            // disabled because env file already writes headers which breaks this.
            // header('Location: index.php');
            // exit;
        }
    }
}
$items = getImages();

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DB Images</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <style>
        img.thumb {
            height: 50px;
        }
    </style>
</head>

<body>


    <div class="container">
        <section>
            <h2>Upload Image</h2>
            <hr />

            <?php if (count($errors)) : ?>
                <div class="alert alert-danger" role="alert">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?= $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php" enctype="multipart/form-data">

                <div class="form-group mt-3">
                    <div>
                        <input type="file" class="form-control" id="inputFile" name="inputFile" placeholder="https://...">
                    </div>
                </div>

                <div class="form-group mt-5">
                    <div>
                        <button type="submit" class="btn btn-primary" name="formSubmit" style="width: 100%">Add</button>
                    </div>
                </div>
            </form>


        </section>
        <main>


            <h2>Images</h2>
            <div class="table-responsive small">
                <table class="table table-hover table-striped table-sm">
                    <thead>
                        <tr>
                            <th scope="col">#ID</th>
                            <th scope="col">Image</th>
                            <th scope="col">name</th>
                            <th scope="col">path</th>
                            <th scope="col">created</th>
                            <th scope="col">updated</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($items as $item): ?>

                            <tr>
                                <td><?= $item['id']; ?></td>
                                <td><?= '<img src="' . $item['path'] . '" class="thumb"/>'; ?></td>
                                <td><?= $item['name']; ?></td>
                                <td><?= $item['path']; ?></td>
                                <td><?= $item['created_at']; ?></td>
                                <td><?= $item['updated_at']; ?></td>

                            </tr>

                        <?php endforeach; ?>


                    </tbody>
                </table>


            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>